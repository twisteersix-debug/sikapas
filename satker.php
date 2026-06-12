<?php
// ============================================================
//  SIKAPAS.RIAU — Kelola Satker (Self-contained)
//  File: satker.php
// ============================================================
require_once 'includes/config.php';
requireLogin();
if (!canEdit()) { header('Location: index.php'); exit; }

$user = currentUser();
$db   = getDB();

$error   = '';
$success = '';
$act     = $_POST['act'] ?? '';

// ── Auto-tambah kolom jika belum ada ────────────────────────
$cols = $db->query("SHOW COLUMNS FROM satker")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('kode',   $cols)) $db->exec("ALTER TABLE satker ADD COLUMN kode VARCHAR(30) NULL");
if (!in_array('alamat', $cols)) $db->exec("ALTER TABLE satker ADD COLUMN alamat VARCHAR(255) NULL");

// ── Handle POST ─────────────────────────────────────────────
if ($act === 'tambah') {
    $nama   = trim($_POST['nama']   ?? '');
    $kode   = trim($_POST['kode']   ?? '') ?: null;
    $alamat = trim($_POST['alamat'] ?? '') ?: null;
    if (!$nama) {
        $error = 'Nama satker wajib diisi.';
    } else {
        $cek = $db->prepare("SELECT id FROM satker WHERE nama=?");
        $cek->execute([$nama]);
        if ($cek->fetch()) {
            $error = "Satker '$nama' sudah ada.";
        } else {
            $db->prepare("INSERT INTO satker (nama,kode,alamat) VALUES (?,?,?)")
               ->execute([$nama, $kode, $alamat]);
            $success = "Satker '$nama' berhasil ditambahkan.";
        }
    }
}

if ($act === 'edit') {
    $id     = $_POST['id']     ?? '';
    $nama   = trim($_POST['nama']   ?? '');
    $kode   = trim($_POST['kode']   ?? '') ?: null;
    $alamat = trim($_POST['alamat'] ?? '') ?: null;
    if (!$nama) {
        $error = 'Nama satker wajib diisi.';
    } else {
        $db->prepare("UPDATE satker SET nama=?,kode=?,alamat=? WHERE id=?")
           ->execute([$nama, $kode, $alamat, $id]);
        $success = "Satker '$nama' berhasil diupdate.";
    }
}

if ($act === 'hapus') {
    $id  = $_POST['id'] ?? '';
    $cek = $db->prepare("SELECT COUNT(*) FROM pegawai WHERE satker_id=?");
    $cek->execute([$id]);
    $jml = (int)$cek->fetchColumn();
    if ($jml > 0) {
        $error = "Tidak bisa menghapus — satker ini masih memiliki $jml pegawai.";
    } else {
        $db->prepare("DELETE FROM satker WHERE id=?")->execute([$id]);
        $success = 'Satker berhasil dihapus.';
    }
}

// ── Ambil data ───────────────────────────────────────────────
$satkers = $db->query("
    SELECT s.id, s.nama,
           IFNULL(s.kode,'')   AS kode,
           IFNULL(s.alamat,'') AS alamat,
           COUNT(p.id)             AS jml_pegawai,
           SUM(p.status='Aktif')  AS jml_aktif
    FROM satker s
    LEFT JOIN pegawai p ON p.satker_id = s.id
    GROUP BY s.id, s.nama, s.kode, s.alamat
    ORDER BY s.nama
")->fetchAll();

$totalPegawai = array_sum(array_column($satkers, 'jml_pegawai'));
$totalAktif   = (int)array_sum(array_column($satkers, 'jml_aktif'));

// Satker user (non-admin)
$userSatkerNama = '';
if (!isAdmin()) {
    $sq = $db->prepare("SELECT s.nama FROM users u LEFT JOIN satker s ON s.id=u.satker_id WHERE u.id=?");
    $sq->execute([$_SESSION['user_id']]);
    $sr = $sq->fetch();
    $userSatkerNama = $sr['nama'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Satker — SIKAPAS.RIAU</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0f1e45;--navy2:#162354;--navy3:#1c2d6b;
  --blue:#1e6fbf;--blue2:#2980d4;--blue-pale:#e8f2fb;
  --green:#16a34a;--green-pale:#dcfce7;
  --red:#dc2626;--red-pale:#fee2e2;
  --orange:#ea7c17;--orange-pale:#fff3e0;
  --purple:#7c3aed;--purple-pale:#ede9fe;
  --gray-50:#f8fafc;--gray-100:#f1f5f9;--gray-200:#e2e8f0;
  --gray-400:#94a3b8;--gray-600:#475569;--gray-800:#1e293b;
  --sidebar-w:240px;--header-h:64px;--radius:12px;
  --shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.05);
  --shadow-md:0 4px 12px rgba(0,0,0,.10),0 8px 32px rgba(0,0,0,.08);
}
html,body{height:100%;font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-50);color:var(--gray-800)}
.shell{display:flex;height:100vh;overflow:hidden}

/* ── Sidebar ── */
.sidebar{width:var(--sidebar-w);flex-shrink:0;background:linear-gradient(180deg,var(--navy) 0%,var(--navy2) 65%,#0b1733 100%);display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;transition:width .25s,transform .25s;z-index:20}
.sidebar::-webkit-scrollbar{width:3px}
.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}
.sidebar.collapsed{width:0;overflow:hidden}
.sb-head{padding:18px 14px 14px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px;flex-shrink:0}
.sb-logo{width:42px;height:42px;flex-shrink:0;border-radius:8px;overflow:hidden;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center}
.sb-logo img{width:42px;height:42px;object-fit:contain}
.sb-brand{font-size:14px;font-weight:800;color:#fff;letter-spacing:.3px;line-height:1.2}
.sb-sub{font-size:9px;color:rgba(255,255,255,.4);line-height:1.5;margin-top:2px}
.sb-section{padding:14px 14px 5px;font-size:9px;font-weight:700;letter-spacing:1.4px;color:rgba(255,255,255,.28);text-transform:uppercase}
.nav-item{display:flex;align-items:center;gap:9px;padding:9px 12px;margin:1px 8px;border-radius:8px;cursor:pointer;color:rgba(255,255,255,.58);font-size:12.5px;font-weight:500;text-decoration:none;transition:all .16s}
.nav-item:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.88)}
.nav-item.active{background:var(--blue);color:#fff;font-weight:600;box-shadow:0 3px 10px rgba(30,111,191,.4)}
.nav-item svg{width:16px;height:16px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.nav-badge{margin-left:auto;background:#ef4444;color:#fff;border-radius:20px;font-size:9px;font-weight:700;padding:1px 6px}
.nav-arrow{margin-left:auto;width:13px;height:13px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2.5;transition:transform .2s}
.nav-arrow.open{transform:rotate(180deg)}
.nav-sub{overflow:hidden;max-height:0;transition:max-height .28s ease}
.nav-sub.open{max-height:300px}
.nav-sub-item{display:flex;align-items:center;gap:7px;padding:7px 12px 7px 36px;margin:0 8px;border-radius:7px;color:rgba(255,255,255,.42);font-size:12px;text-decoration:none;transition:all .15s}
.nav-sub-item:hover{color:rgba(255,255,255,.78);background:rgba(255,255,255,.05)}
.nav-sub-item.active{color:#60a5fa;font-weight:600}
.nav-sub-item::before{content:'';width:4px;height:4px;border-radius:50%;background:currentColor;flex-shrink:0;opacity:.7}
.sb-footer{margin-top:auto;padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:8px;flex-shrink:0}
.sb-foot-logo{width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0;background:rgba(255,255,255,.08)}
.sb-foot-logo img{width:32px;height:32px;object-fit:contain}
.sb-foot-text{font-size:8.5px;color:rgba(255,255,255,.32);line-height:1.6}
.sb-foot-text strong{display:block;font-size:9px;color:rgba(255,255,255,.5);font-weight:700}
.mob-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:19;display:none}
.mob-overlay.show{display:block}

/* ── Main ── */
.main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}
.topbar{height:var(--header-h);flex-shrink:0;background:#fff;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;padding:0 22px;gap:12px;z-index:10}
.topbar-left{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.menu-toggle{background:none;border:none;cursor:pointer;padding:7px;border-radius:8px;color:var(--gray-600);transition:background .15s;display:flex;align-items:center}
.menu-toggle:hover{background:var(--gray-100)}
.menu-toggle svg{width:19px;height:19px;stroke:currentColor;fill:none;stroke-width:2}
.topbar-title{font-size:17px;font-weight:700;color:var(--navy)}
.topbar-right{display:flex;align-items:center;gap:6px}
.icon-btn{position:relative;width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;background:none;border:none;cursor:pointer;color:var(--gray-600);transition:background .15s}
.icon-btn:hover{background:var(--gray-100)}
.icon-btn svg{width:19px;height:19px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.notif-dot{position:absolute;top:7px;right:7px;width:8px;height:8px;background:#ef4444;border-radius:50%;border:2px solid #fff}
.user-pill{display:flex;align-items:center;gap:8px;padding:5px 10px 5px 5px;border-radius:30px;background:var(--gray-50);border:1px solid var(--gray-200);cursor:pointer;transition:background .15s;position:relative;user-select:none}
.user-pill:hover{background:var(--gray-100)}
.user-ava{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--navy3));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;flex-shrink:0}
.u-name{font-size:12.5px;font-weight:700;color:var(--gray-800)}
.u-role{font-size:10.5px;color:var(--gray-400)}
.u-caret{width:13px;height:13px;stroke:var(--gray-400);fill:none;stroke-width:2.5;transition:transform .2s;flex-shrink:0}
.user-pill.open .u-caret{transform:rotate(180deg)}
.u-dropdown{position:absolute;top:calc(100%+8px);right:0;background:#fff;border-radius:12px;min-width:200px;box-shadow:var(--shadow-md);border:1px solid var(--gray-200);overflow:hidden;display:none;z-index:100}
.user-pill.open .u-dropdown{display:block;animation:dropIn .16s ease}
@keyframes dropIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.dd-info{padding:13px 15px 10px;border-bottom:1px solid var(--gray-100)}
.dd-name{font-size:13px;font-weight:700;color:var(--gray-800)}
.dd-role{font-size:11px;color:var(--gray-400)}
.dd-item{display:flex;align-items:center;gap:9px;padding:10px 15px;font-size:12.5px;color:var(--gray-800);text-decoration:none;transition:background .14s;border:none;background:none;width:100%;font-family:inherit;cursor:pointer}
.dd-item:hover{background:var(--gray-50)}
.dd-item svg{width:14px;height:14px;stroke:var(--gray-400);fill:none;stroke-width:2}
.dd-divider{height:1px;background:var(--gray-100)}
.dd-item.red{color:var(--red)}
.dd-item.red svg{stroke:var(--red)}
.dd-item.red:hover{background:#fff5f5}

/* ── Content ── */
.content{flex:1;overflow-y:auto;padding:24px}
.content::-webkit-scrollbar{width:5px}
.content::-webkit-scrollbar-thumb{background:var(--gray-200);border-radius:5px}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.ph-title{font-size:20px;font-weight:800;color:var(--navy);display:flex;align-items:center;gap:8px;margin-bottom:3px}
.ph-title svg{width:20px;height:20px;stroke:var(--blue);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.ph-sub{font-size:13px;color:var(--gray-400)}

/* ── Buttons ── */
.btn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .16s;display:inline-flex;align-items:center;gap:6px;text-decoration:none;white-space:nowrap}
.btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;flex-shrink:0}
.btn-primary{background:var(--blue);color:#fff}
.btn-primary:hover{background:#155fa0}
.btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
.btn-outline:hover{background:var(--blue-pale)}
.btn-danger{background:var(--red-pale);color:var(--red);border:1px solid #fca5a5}
.btn-danger:hover{background:#fecaca}
.btn-sm{padding:5px 12px;font-size:12px}

/* ── Stats ── */
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
.stat-card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:16px 18px;display:flex;align-items:center;gap:12px}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon svg{width:22px;height:22px;stroke:#fff;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.si-blue{background:linear-gradient(135deg,#1e6fbf,#2980d4)}
.si-green{background:linear-gradient(135deg,#16a34a,#22c55e)}
.si-orange{background:linear-gradient(135deg,#ea7c17,#f59e0b)}
.stat-val{font-size:24px;font-weight:800;color:var(--gray-800);line-height:1}
.stat-lbl{font-size:11px;color:var(--gray-400);margin-top:2px;font-weight:500}

/* ── Alerts ── */
.alert{padding:11px 16px;border-radius:8px;font-size:13px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:9px}
.alert svg{width:15px;height:15px;flex-shrink:0;fill:none;stroke:currentColor;stroke-width:2}
.alert-success{background:var(--green-pale);color:var(--green);border:1px solid #86efac}
.alert-error{background:var(--red-pale);color:var(--red);border:1px solid #fca5a5}

/* ── Card / Table ── */
.card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden}
.card-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--gray-100)}
.card-title{font-size:13.5px;font-weight:700;color:var(--gray-800)}
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{background:var(--gray-50);padding:9px 16px;font-size:11px;font-weight:700;color:var(--gray-400);text-align:left;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
td{padding:11px 16px;font-size:13px;border-bottom:1px solid var(--gray-100);color:var(--gray-800);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--gray-50)}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-blue{background:var(--blue-pale);color:var(--blue)}
.badge-green{background:var(--green-pale);color:var(--green)}
.badge-gray{background:var(--gray-100);color:var(--gray-600)}
.empty-row{text-align:center;padding:2.5rem;color:var(--gray-400);font-size:13px}

/* ── Modal ── */
.modal-overlay{position:fixed;inset:0;background:rgba(10,20,50,.5);display:none;z-index:200;overflow-y:auto;backdrop-filter:blur(2px)}
.modal-overlay.open{display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem}
.modal{background:#fff;border-radius:16px;width:100%;max-width:520px;box-shadow:0 24px 64px rgba(0,0,0,.2);margin:auto;overflow:hidden}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--gray-100)}
.modal-title{font-size:16px;font-weight:700;color:var(--navy)}
.modal-close{background:none;border:none;font-size:22px;color:var(--gray-400);cursor:pointer;line-height:1;padding:2px 6px;border-radius:6px}
.modal-close:hover{background:var(--gray-100)}
.modal-body{padding:22px}
.modal-foot{display:flex;justify-content:flex-end;gap:8px;padding:14px 22px;border-top:1px solid var(--gray-100);background:var(--gray-50)}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
.form-group:last-child{margin-bottom:0}
.form-label{font-size:12.5px;font-weight:600;color:var(--gray-600)}
.form-control{padding:9px 13px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--gray-800);outline:none;transition:border-color .18s;background:#fff;width:100%}
.form-control:focus{border-color:var(--blue2);box-shadow:0 0 0 3px rgba(30,111,191,.08)}

/* ── Toast ── */
.toast{position:fixed;bottom:24px;right:24px;background:#1a2e5a;color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:500;box-shadow:var(--shadow-md);z-index:999;opacity:0;transform:translateY(8px);transition:all .28s;pointer-events:none}
.toast.show{opacity:1;transform:translateY(0)}
.toast.success{background:#166534}
.toast.error{background:#991b1b}

@media(max-width:900px){
  .sidebar{position:fixed;top:0;left:0;height:100%;transform:translateX(-100%);width:var(--sidebar-w) !important}
  .sidebar.mob-open{transform:translateX(0)}
  .stat-row{grid-template-columns:1fr 1fr}
  .content{padding:16px}
}
@media(max-width:560px){.stat-row{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="shell">

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
  <div class="sb-head">
    <div class="sb-logo"><img src="logo.png" alt="SIKAPAS" onerror="this.parentElement.innerHTML='🏛️'"></div>
    <div>
      <div class="sb-brand">SIKAPAS.RIAU</div>
      <div class="sb-sub">Sistem Informasi Kepegawaian<br>dan Administrasi Pemasyarakatan</div>
    </div>
  </div>

  <div class="sb-section">Menu Utama</div>
  <a href="dashboard.php" class="nav-item"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a>

  <div class="nav-item" onclick="toggleSub('sub-pegawai',this)">
    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Pegawai<svg class="nav-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub" id="sub-pegawai">
    <a href="pegawai_satker.php" class="nav-sub-item">Data per Satker</a>
    <a href="index.php" class="nav-sub-item">Semua Pegawai</a>
    <a href="pengingat.php" class="nav-sub-item">Pengingat Pensiun</a>
  </div>

  <div class="nav-item" onclick="toggleSub('sub-arsip',this)">
    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    Arsip Kepegawaian<svg class="nav-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub" id="sub-arsip">
    <a href="dokumen.php" class="nav-sub-item">Dokumen Pegawai</a>
    <a href="arsip.php" class="nav-sub-item">Arsip File</a>
  </div>

  <a href="satker.php" class="nav-item active">
    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Satker
  </a>

  <div class="nav-item" onclick="toggleSub('sub-lap',this)">
    <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    Laporan<svg class="nav-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub" id="sub-lap">
    <a href="index.php" class="nav-sub-item">Kenaikan Gaji Berkala</a>
    <a href="index.php" class="nav-sub-item">SLKS / SKP</a>
    <a href="index.php" class="nav-sub-item">Tunjangan</a>
  </div>

  <?php if (isAdmin()): ?>
  <div class="sb-section">Pengaturan</div>
  <a href="users.php" class="nav-item"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>Kelola User</a>
  <a href="aktivitas.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Log Aktivitas</a>
  <?php endif; ?>

  <div class="sb-section">Informasi</div>
  <a href="#" class="nav-item"><svg viewBox="0 0 24 24"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>Pengumuman<span class="nav-badge">2</span></a>
  <a href="#" class="nav-item"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Kalender Kegiatan</a>
  <a href="profile.php" class="nav-item"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>Profil Saya</a>

  <div class="sb-footer">
    <div class="sb-foot-logo"><img src="logo.png" alt="" onerror="this.style.display='none'"></div>
    <div class="sb-foot-text"><strong>KEMENTERIAN IMIGRASI<br>DAN PEMASYARAKATAN</strong>© 2026 SIKAPAS.RIAU</div>
  </div>
</aside>

<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>

<!-- ══ MAIN ══ -->
<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()">
        <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="topbar-title">Kelola Satker</span>
    </div>
    <div class="topbar-right">
      <button class="icon-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="notif-dot"></span></button>
      <div class="user-pill" id="userPill" onclick="toggleUser(event)">
        <div class="user-ava"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>
        <div><div class="u-name"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></div><div class="u-role"><?= isAdmin()?'Administrator':ucfirst($user['role']??'') ?></div></div>
        <svg class="u-caret" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        <div class="u-dropdown">
          <div class="dd-info">
            <div class="dd-name"><?= htmlspecialchars($user['nama']??'') ?></div>
            <div class="dd-role"><?= isAdmin()?'Administrator':ucfirst($user['role']??'') ?></div>
            <?php if ($userSatkerNama): ?><div style="font-size:11px;color:var(--blue);font-weight:600;margin-top:2px">🏢 <?= htmlspecialchars($userSatkerNama) ?></div><?php endif; ?>
          </div>
          <a href="profile.php" class="dd-item"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>Profil Saya</a>
          <div class="dd-divider"></div>
          <a href="logout.php" class="dd-item red"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Keluar</a>
        </div>
      </div>
    </div>
  </header>

  <div class="content">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <div class="ph-title">
          <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Kelola Satker
        </div>
        <div class="ph-sub">Manajemen Satuan Kerja</div>
      </div>
      <button class="btn btn-primary" onclick="openModal('modal-tambah')">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Satker
      </button>
    </div>

    <!-- Alerts -->
    <?php if ($error): ?>
    <div class="alert alert-error">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stat-row">
      <div class="stat-card">
        <div class="stat-icon si-blue"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
        <div><div class="stat-val"><?= count($satkers) ?></div><div class="stat-lbl">Total Satker</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-green"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div><div class="stat-val"><?= $totalPegawai ?></div><div class="stat-lbl">Total Pegawai</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-orange"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div><div class="stat-val"><?= $totalAktif ?></div><div class="stat-lbl">Pegawai Aktif</div></div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="card-head">
        <span class="card-title">Daftar Satuan Kerja (<?= count($satkers) ?>)</span>
        <a href="pegawai_satker.php" class="btn btn-sm btn-outline">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Lihat per Satker
        </a>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>No</th><th>Nama Satker</th><th>Kode</th><th>Alamat</th><th>Jml Pegawai</th><th>Aktif</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php if ($satkers): ?>
            <?php foreach ($satkers as $i => $s): ?>
            <tr>
              <td style="color:var(--gray-400);font-size:12px"><?= $i+1 ?></td>
              <td style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($s['nama']) ?></td>
              <td><?= $s['kode'] ? '<span class="badge badge-gray">'.htmlspecialchars($s['kode']).'</span>' : '<span style="color:var(--gray-400)">—</span>' ?></td>
              <td style="font-size:12px;color:var(--gray-600)"><?= $s['alamat'] ? htmlspecialchars($s['alamat']) : '<span style="color:var(--gray-400)">—</span>' ?></td>
              <td><span class="badge badge-blue"><?= $s['jml_pegawai'] ?> pegawai</span></td>
              <td><span class="badge badge-green"><?= (int)$s['jml_aktif'] ?> aktif</span></td>
              <td style="white-space:nowrap;display:flex;gap:6px">
                <button class="btn btn-sm btn-outline"
                        onclick='openEdit(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)'>Edit</button>
                <?php if ((int)$s['jml_pegawai'] === 0): ?>
                <form method="POST" style="display:inline"
                      onsubmit="return confirm('Hapus satker ini?')">
                  <input type="hidden" name="act" value="hapus">
                  <input type="hidden" name="id"  value="<?= $s['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
                <?php else: ?>
                <button class="btn btn-sm btn-danger" disabled
                        style="opacity:.4;cursor:not-allowed"
                        title="Masih ada <?= $s['jml_pegawai'] ?> pegawai">Hapus</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr><td colspan="7" class="empty-row">Belum ada data satker.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<!-- Modal Tambah -->
<div class="modal-overlay" id="modal-tambah" onclick="if(event.target===this)closeModal('modal-tambah')">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title">➕ Tambah Satker Baru</span>
      <button class="modal-close" onclick="closeModal('modal-tambah')">✕</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="act" value="tambah">
        <div class="form-group">
          <label class="form-label">Nama Satker <span style="color:var(--red)">*</span></label>
          <input type="text" name="nama" class="form-control" placeholder="Contoh: Lapas Kelas IIA Pekanbaru" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kode Satker</label>
          <input type="text" name="kode" class="form-control" placeholder="Opsional">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat</label>
          <input type="text" name="alamat" class="form-control" placeholder="Alamat singkat">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-tambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title">✏️ Edit Satker</span>
      <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="act" value="edit">
        <input type="hidden" name="id"  id="e-id">
        <div class="form-group">
          <label class="form-label">Nama Satker <span style="color:var(--red)">*</span></label>
          <input type="text" name="nama" id="e-nama" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kode Satker</label>
          <input type="text" name="kode" id="e-kode" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat</label>
          <input type="text" name="alamat" id="e-alamat" class="form-control">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
function toggleSidebar(){
  const sb=document.getElementById('sidebar'),ov=document.getElementById('mobOverlay');
  if(window.innerWidth<=900){const o=sb.classList.toggle('mob-open');ov.classList.toggle('show',o);}
  else sb.classList.toggle('collapsed');
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('mob-open');
  document.getElementById('mobOverlay').classList.remove('show');
}
function toggleSub(id,el){
  const sub=document.getElementById(id),arrow=el.querySelector('.nav-arrow'),isOpen=sub.classList.contains('open');
  document.querySelectorAll('.nav-sub').forEach(s=>s.classList.remove('open'));
  document.querySelectorAll('.nav-arrow').forEach(a=>a.classList.remove('open'));
  if(!isOpen){sub.classList.add('open');if(arrow)arrow.classList.add('open');}
}
function toggleUser(e){e.stopPropagation();document.getElementById('userPill').classList.toggle('open');}
document.addEventListener('click',()=>document.getElementById('userPill')?.classList.remove('open'));
function openModal(id){document.getElementById(id)?.classList.add('open');}
function closeModal(id){document.getElementById(id)?.classList.remove('open');}
function openEdit(s){
  document.getElementById('e-id').value    =s.id;
  document.getElementById('e-nama').value  =s.nama||'';
  document.getElementById('e-kode').value  =s.kode||'';
  document.getElementById('e-alamat').value=s.alamat||'';
  openModal('modal-edit');
}
</script>
</body>
</html>
