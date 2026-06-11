<?php
// ============================================================
//  SIKAPAS.RIAU — Shared Layout (Sidebar + Topbar)
//  File: includes/layout.php
//
//  CARA PAKAI di setiap halaman PHP:
//
//  <?php
//  require_once 'includes/config.php';
//  requireLogin();
//  $user = currentUser();
//  $pageTitle  = 'Nama Halaman';   // judul di topbar
//  $activeMenu = 'pegawai';        // key menu aktif (lihat daftar di bawah)
//  require_once 'includes/layout.php';
//  // ... konten halaman ...
//  layoutFooter();
//  ?>
//
//  KEY MENU AKTIF:
//  dashboard, pegawai, pegawai_satker, pengingat, dokumen, arsip,
//  satker, kgb, slks, tunjangan, users, aktivitas, profil
// ============================================================

if (!isset($pageTitle))  $pageTitle  = 'SIKAPAS.RIAU';
if (!isset($activeMenu)) $activeMenu = '';

// Ambil satker user
$_layoutDb = getDB();
$_userSatker = '';
if (!isAdmin()) {
    $_s = $_layoutDb->prepare("SELECT s.nama FROM users u JOIN satker s ON s.id=u.satker_id WHERE u.id=?");
    $_s->execute([$_SESSION['user_id']]);
    $_r = $_s->fetch();
    $_userSatker = $_r['nama'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — SIKAPAS.RIAU</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════
   RESET & ROOT
═══════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0f1e45;--navy2:#162354;--navy3:#1c2d6b;
  --blue:#1e6fbf;--blue2:#2980d4;--blue-pale:#e8f2fb;
  --gold:#c9933a;
  --green:#16a34a;--green-pale:#dcfce7;
  --red:#dc2626;--red-pale:#fee2e2;
  --orange:#ea7c17;--orange-pale:#fff3e0;
  --purple:#7c3aed;--purple-pale:#ede9fe;
  --white:#fff;
  --gray-50:#f8fafc;--gray-100:#f1f5f9;--gray-200:#e2e8f0;
  --gray-400:#94a3b8;--gray-600:#475569;--gray-800:#1e293b;
  --sidebar-w:240px;--header-h:64px;
  --radius:12px;--radius-lg:18px;
  --shadow:0 1px 3px rgba(0,0,0,.08),0 4px 16px rgba(0,0,0,.06);
  --shadow-md:0 4px 12px rgba(0,0,0,.10),0 8px 32px rgba(0,0,0,.08);
}
html,body{height:100%;font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-50);color:var(--gray-800)}

/* ═══ LAYOUT SHELL ═══ */
.shell{display:flex;height:100vh;overflow:hidden}

/* ═══ SIDEBAR ═══ */
.sidebar{
  width:var(--sidebar-w);flex-shrink:0;
  background:linear-gradient(180deg,var(--navy) 0%,var(--navy2) 60%,#0b1733 100%);
  display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;
  position:relative;z-index:10;transition:width .3s;
}
.sidebar::-webkit-scrollbar{width:4px}
.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px}
.sb-head{padding:20px 16px 16px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px;flex-shrink:0}
.sb-logo{width:44px;height:44px;flex-shrink:0;border-radius:8px;overflow:hidden;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center}
.sb-logo img{width:44px;height:44px;object-fit:contain}
.sb-brand{font-size:15px;font-weight:800;color:#fff;letter-spacing:.5px;line-height:1.2}
.sb-sub{font-size:9.5px;color:rgba(255,255,255,.5);line-height:1.4;margin-top:2px}
.sb-section{padding:16px 14px 6px;font-size:10px;font-weight:700;letter-spacing:1.2px;color:rgba(255,255,255,.3);text-transform:uppercase}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:8px;cursor:pointer;color:rgba(255,255,255,.6);font-size:13px;font-weight:500;text-decoration:none;transition:all .18s;position:relative}
.nav-item:hover{background:rgba(255,255,255,.07);color:#fff}
.nav-item.active{background:var(--blue);color:#fff;font-weight:600;box-shadow:0 4px 12px rgba(30,111,191,.4)}
.nav-item svg{width:17px;height:17px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.nav-badge{margin-left:auto;background:#ef4444;color:#fff;border-radius:20px;font-size:10px;font-weight:700;padding:1px 7px}
.nav-arrow{margin-left:auto;width:14px;height:14px;transition:transform .2s;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
.nav-sub{overflow:hidden;max-height:0;transition:max-height .3s ease}
.nav-sub.open{max-height:300px}
.nav-sub-item{display:flex;align-items:center;gap:8px;padding:8px 14px 8px 40px;margin:0 8px;border-radius:8px;cursor:pointer;color:rgba(255,255,255,.45);font-size:12.5px;text-decoration:none;transition:all .18s}
.nav-sub-item:hover{color:rgba(255,255,255,.85);background:rgba(255,255,255,.05)}
.nav-sub-item.active{color:#60a5fa;font-weight:600}
.sb-footer{margin-top:auto;padding:16px 14px;border-top:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px;flex-shrink:0}
.sb-footer-logo{width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;background:rgba(255,255,255,.1)}
.sb-footer-logo img{width:36px;height:36px;object-fit:contain}
.sb-footer-text{font-size:9px;color:rgba(255,255,255,.35);line-height:1.6}
.sb-footer-text strong{display:block;font-size:10px;color:rgba(255,255,255,.55);font-weight:700}

/* ═══ MAIN ═══ */
.main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}

/* ═══ TOPBAR ═══ */
.topbar{height:var(--header-h);flex-shrink:0;background:#fff;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;padding:0 28px;gap:16px;position:sticky;top:0;z-index:5;box-shadow:0 1px 0 var(--gray-200)}
.topbar-left{display:flex;align-items:center;gap:12px;flex:1}
.menu-toggle{background:none;border:none;cursor:pointer;padding:6px;border-radius:8px;color:var(--gray-600);transition:background .15s}
.menu-toggle:hover{background:var(--gray-100)}
.menu-toggle svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;display:block}
.topbar-title{font-size:18px;font-weight:700;color:var(--navy)}
.topbar-right{display:flex;align-items:center;gap:8px}
.icon-btn{position:relative;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:none;border:none;cursor:pointer;color:var(--gray-600);transition:background .15s}
.icon-btn:hover{background:var(--gray-100)}
.icon-btn svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.notif-badge{position:absolute;top:6px;right:6px;width:16px;height:16px;background:#ef4444;border-radius:50%;font-size:9px;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff}
.user-pill{display:flex;align-items:center;gap:10px;padding:6px 12px 6px 6px;border-radius:32px;background:var(--gray-50);border:1px solid var(--gray-200);cursor:pointer;transition:background .15s;position:relative}
.user-pill:hover{background:var(--gray-100)}
.user-ava{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--navy3));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#fff;flex-shrink:0;overflow:hidden}
.user-info{line-height:1.3}
.user-name{font-size:13px;font-weight:700;color:var(--gray-800)}
.user-role{font-size:11px;color:var(--gray-400)}
.user-caret{width:14px;height:14px;stroke:var(--gray-400);fill:none;stroke-width:2.5;flex-shrink:0}
.user-dropdown{position:absolute;top:calc(100%+8px);right:0;background:#fff;border-radius:12px;min-width:200px;box-shadow:var(--shadow-md);border:1px solid var(--gray-200);overflow:hidden;display:none;z-index:50}
.user-pill.open .user-dropdown{display:block}
.dd-item{display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13px;color:var(--gray-800);text-decoration:none;transition:background .15s;cursor:pointer;border:none;background:none;width:100%;font-family:inherit}
.dd-item:hover{background:var(--gray-50)}
.dd-item svg{width:15px;height:15px;stroke:var(--gray-400);fill:none;stroke-width:2}
.dd-divider{height:1px;background:var(--gray-100);margin:4px 0}
.dd-item.red{color:#dc2626}
.dd-item.red svg{stroke:#dc2626}
.dd-item.red:hover{background:#fff5f5}

/* ═══ CONTENT AREA ═══ */
.content{flex:1;overflow-y:auto;padding:28px 32px 48px}
.content::-webkit-scrollbar{width:6px}
.content::-webkit-scrollbar-thumb{background:var(--gray-200);border-radius:6px}

/* ═══ PAGE HEADER ═══ */
.page-header{margin-bottom:24px}
.page-header h1{font-size:22px;font-weight:800;color:var(--navy);margin-bottom:4px;display:flex;align-items:center;gap:10px}
.page-header h1 svg{width:22px;height:22px;stroke:var(--blue);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.page-header p{font-size:13px;color:var(--gray-400)}
.breadcrumb{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--gray-400);margin-bottom:8px}
.breadcrumb a{color:var(--blue);text-decoration:none;font-weight:500}
.breadcrumb a:hover{text-decoration:underline}
.breadcrumb svg{width:12px;height:12px;stroke:var(--gray-400);fill:none;stroke-width:2}

/* ═══ CARDS ═══ */
.card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden}
.card-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--gray-100)}
.card-title{font-size:14px;font-weight:700;color:var(--gray-800)}
.card-body{padding:20px}

/* ═══ BUTTONS ═══ */
.btn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;display:inline-flex;align-items:center;gap:6px}
.btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;flex-shrink:0}
.btn-primary{background:var(--blue);color:#fff}
.btn-primary:hover{background:var(--navy)}
.btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
.btn-outline:hover{background:var(--blue-pale)}
.btn-danger{background:var(--red-pale);color:var(--red);border:1px solid #fca5a5}
.btn-danger:hover{background:#fecaca}
.btn-sm{padding:5px 12px;font-size:12px}
.btn-link{background:none;border:none;color:var(--blue);font-size:12px;font-weight:600;cursor:pointer;padding:5px 10px;border-radius:6px;font-family:inherit;transition:background .15s}
.btn-link:hover{background:var(--blue-pale)}

/* ═══ TABLE ═══ */
.table-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{background:var(--gray-50);padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-400);text-align:left;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
.tbl td{padding:12px 16px;font-size:13px;border-bottom:1px solid var(--gray-100);color:var(--gray-800);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:var(--gray-50)}

/* ═══ BADGES ═══ */
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-green{background:var(--green-pale);color:var(--green)}
.badge-red{background:var(--red-pale);color:var(--red)}
.badge-orange{background:var(--orange-pale);color:var(--orange)}
.badge-blue{background:var(--blue-pale);color:var(--blue)}
.badge-purple{background:var(--purple-pale);color:var(--purple)}
.badge-gray{background:var(--gray-100);color:var(--gray-600)}

/* ═══ FORM ═══ */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
.form-label{font-size:13px;font-weight:600;color:var(--gray-600)}
.form-section{font-size:11px;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:.5px;grid-column:1/-1;padding:8px 0 4px;border-bottom:1px solid var(--gray-200);margin-top:6px}
input[type=text],input[type=date],input[type=number],input[type=password],input[type=email],select,textarea{padding:10px 14px;border:1.5px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--gray-800);outline:none;transition:border-color .2s;background:#fff;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--blue)}
select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}

/* ═══ ALERT ═══ */
.alert{padding:12px 16px;border-radius:8px;font-size:13px;font-weight:500;display:flex;align-items:flex-start;gap:10px;margin-bottom:16px}
.alert svg{width:16px;height:16px;flex-shrink:0;margin-top:1px}
.alert-success{background:var(--green-pale);color:var(--green);border:1px solid #86efac}
.alert-success svg{stroke:var(--green);fill:none;stroke-width:2}
.alert-error{background:var(--red-pale);color:var(--red);border:1px solid #fca5a5}
.alert-error svg{stroke:var(--red);fill:none;stroke-width:2}
.alert-info{background:var(--blue-pale);color:var(--blue);border:1px solid #93c5fd}
.alert-info svg{stroke:var(--blue);fill:none;stroke-width:2}
.alert-warning{background:var(--orange-pale);color:var(--orange);border:1px solid #fcd34d}
.alert-warning svg{stroke:var(--orange);fill:none;stroke-width:2}

/* ═══ MODAL ═══ */
.modal-overlay{position:fixed;inset:0;background:rgba(10,20,50,.55);display:none;z-index:200;overflow-y:auto}
.modal-overlay.open{display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem}
.modal{background:#fff;border-radius:16px;padding:2rem;width:100%;max-width:640px;box-shadow:0 20px 60px rgba(0,0,0,.25);margin:2rem auto}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}
.modal-title{font-size:17px;font-weight:700;color:var(--navy)}
.modal-close{background:none;border:none;font-size:22px;color:var(--gray-400);cursor:pointer;line-height:1;padding:0}
.modal-close:hover{color:var(--gray-600)}

/* ═══ TOAST ═══ */
.toast{position:fixed;bottom:24px;right:24px;background:var(--navy);color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:var(--shadow-md);z-index:999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none;max-width:340px}
.toast.show{opacity:1;transform:translateY(0)}
.toast.success{background:var(--green)}
.toast.error{background:var(--red)}
.toast.warning{background:var(--orange)}

/* ═══ LOADING ═══ */
.loading{text-align:center;padding:3rem;color:var(--gray-400);font-size:13px}
.loading svg{width:32px;height:32px;stroke:var(--gray-200);fill:none;stroke-width:1.5;display:block;margin:0 auto 12px;animation:spin 1.5s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ═══ RESPONSIVE ═══ */
@media(max-width:900px){
  .sidebar{position:fixed;left:0;top:0;height:100%;z-index:100;transform:translateX(-100%);transition:transform .3s}
  .sidebar.mob-open{transform:translateX(0)}
  .mob-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:99}
  .mob-overlay.show{display:block}
  .content{padding:16px}
}
@media(max-width:560px){.form-grid{grid-template-columns:1fr}.form-grid-3{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="shell">

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="sidebar" id="sidebar">
  <div class="sb-head">
    <div class="sb-logo">
      <img src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1) ?>logo.png"
           alt="SIKAPAS" onerror="this.parentElement.innerHTML='🏛️'">
    </div>
    <div>
      <div class="sb-brand">SIKAPAS.RIAU</div>
      <div class="sb-sub">Sistem Informasi Kepegawaian<br>dan Administrasi Pemasyarakatan</div>
    </div>
  </div>

  <div class="sb-section">Menu Utama</div>

  <a href="dashboard.php" class="nav-item <?= $activeMenu==='dashboard'?'active':'' ?>">
    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Dashboard
  </a>

  <!-- Pegawai sub-menu -->
  <div class="nav-item <?= in_array($activeMenu,['pegawai','pegawai_satker','pengingat'])?'active':'' ?>"
       onclick="toggleSub('sub-pegawai',this)" id="nav-pegawai">
    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Pegawai
    <svg class="nav-arrow <?= in_array($activeMenu,['pegawai','pegawai_satker','pengingat'])?'rotated':'' ?>" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub <?= in_array($activeMenu,['pegawai','pegawai_satker','pengingat'])?'open':'' ?>" id="sub-pegawai">
    <a href="pegawai_satker.php" class="nav-sub-item <?= $activeMenu==='pegawai_satker'?'active':'' ?>">Data per Satker</a>
    <a href="index.php" class="nav-sub-item <?= $activeMenu==='pegawai'?'active':'' ?>">Semua Pegawai</a>
    <a href="pengingat.php" class="nav-sub-item <?= $activeMenu==='pengingat'?'active':'' ?>">Pengingat Pensiun</a>
  </div>

  <!-- Arsip sub-menu -->
  <div class="nav-item <?= in_array($activeMenu,['dokumen','arsip'])?'active':'' ?>"
       onclick="toggleSub('sub-arsip',this)">
    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    Arsip Kepegawaian
    <svg class="nav-arrow <?= in_array($activeMenu,['dokumen','arsip'])?'rotated':'' ?>" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub <?= in_array($activeMenu,['dokumen','arsip'])?'open':'' ?>" id="sub-arsip">
    <a href="dokumen.php" class="nav-sub-item <?= $activeMenu==='dokumen'?'active':'' ?>">Dokumen Pegawai</a>
    <a href="arsip.php"   class="nav-sub-item <?= $activeMenu==='arsip'?'active':'' ?>">Arsip File</a>
  </div>

  <a href="satker.php" class="nav-item <?= $activeMenu==='satker'?'active':'' ?>">
    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Satker
  </a>

  <!-- Laporan sub-menu -->
  <div class="nav-item <?= in_array($activeMenu,['kgb','slks','tunjangan'])?'active':'' ?>"
       onclick="toggleSub('sub-lap',this)">
    <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    Laporan
    <svg class="nav-arrow <?= in_array($activeMenu,['kgb','slks','tunjangan'])?'rotated':'' ?>" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub <?= in_array($activeMenu,['kgb','slks','tunjangan'])?'open':'' ?>" id="sub-lap">
    <a href="index.php#kgb"      class="nav-sub-item <?= $activeMenu==='kgb'?'active':'' ?>">Kenaikan Gaji Berkala</a>
    <a href="index.php#slks"     class="nav-sub-item <?= $activeMenu==='slks'?'active':'' ?>">SLKS / SKP</a>
    <a href="index.php#tunjangan"class="nav-sub-item <?= $activeMenu==='tunjangan'?'active':'' ?>">Tunjangan</a>
  </div>

  <?php if (isAdmin()): ?>
  <div class="sb-section">Pengaturan</div>
  <a href="users.php" class="nav-item <?= $activeMenu==='users'?'active':'' ?>">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    Kelola User
  </a>
  <a href="aktivitas.php" class="nav-item <?= $activeMenu==='aktivitas'?'active':'' ?>">
    <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
    Log Aktivitas
  </a>
  <?php endif; ?>

  <div class="sb-section">Informasi</div>
  <a href="#" class="nav-item">
    <svg viewBox="0 0 24 24"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    Pengumuman
    <span class="nav-badge">2</span>
  </a>
  <a href="#" class="nav-item">
    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    Kalender Kegiatan
  </a>
  <a href="profile.php" class="nav-item <?= $activeMenu==='profil'?'active':'' ?>">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    Profil Saya
  </a>

  <div class="sb-footer">
    <div class="sb-footer-logo">
      <img src="logo.png" alt="" onerror="this.style.display='none'">
    </div>
    <div class="sb-footer-text">
      <strong>KEMENTERIAN IMIGRASI<br>DAN PEMASYARAKATAN</strong>
      REPUBLIK INDONESIA<br>© 2026 SIKAPAS.RIAU
    </div>
  </div>
</aside>

<!-- Overlay mobile -->
<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>

<!-- ═══════════════ MAIN ═══════════════ -->
<div class="main">

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()">
        <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="topbar-title"><?= htmlspecialchars($pageTitle) ?></span>
    </div>
    <div class="topbar-right">
      <button class="icon-btn" title="Notifikasi">
        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="notif-badge">3</span>
      </button>
      <div class="user-pill" id="userPill" onclick="toggleUser(event)">
        <div class="user-ava"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>
        <div class="user-info">
          <div class="user-name"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></div>
          <div class="user-role"><?= isAdmin() ? 'Administrator' : ucfirst($user['role'] ?? '') ?></div>
        </div>
        <svg class="user-caret" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        <div class="user-dropdown">
          <div style="padding:14px 16px 10px;border-bottom:1px solid var(--gray-100)">
            <div style="font-size:13px;font-weight:700;color:var(--gray-800)"><?= htmlspecialchars($user['nama'] ?? '') ?></div>
            <div style="font-size:11px;color:var(--gray-400)"><?= isAdmin() ? 'Administrator' : ucfirst($user['role'] ?? '') ?></div>
            <?php if (!empty($_userSatker ?? '')): ?>
            <div style="font-size:11px;color:var(--blue);font-weight:600;margin-top:2px">🏢 <?= htmlspecialchars($_userSatker) ?></div>
            <?php endif; ?>
          </div>
          <a href="profile.php" class="dd-item">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Profil Saya
          </a>
          <div class="dd-divider"></div>
          <a href="logout.php" class="dd-item red">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Content wrapper -->
  <div class="content">
<?php
// ── JS shared (sidebar, modal, toast) disertakan di footer
function layoutFooter() { ?>
  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<div class="toast" id="toast"></div>

<script>
// ── Sidebar ──────────────────────────────────────────
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('mobOverlay');
  sb.classList.toggle('mob-open');
  ov.classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('mob-open');
  document.getElementById('mobOverlay').classList.remove('show');
}

// ── Sub-menu ─────────────────────────────────────────
function toggleSub(id, el) {
  const sub   = document.getElementById(id);
  const isOpen = sub.classList.contains('open');
  document.querySelectorAll('.nav-sub').forEach(s => s.classList.remove('open'));
  document.querySelectorAll('.nav-arrow').forEach(a => a.style.transform = '');
  if (!isOpen) {
    sub.classList.add('open');
    const arr = el.querySelector('.nav-arrow');
    if (arr) arr.style.transform = 'rotate(180deg)';
  }
}

// Buka sub-menu yang aktif saat load
document.querySelectorAll('.nav-arrow.rotated').forEach(a => {
  a.style.transform = 'rotate(180deg)';
});

// ── User dropdown ─────────────────────────────────────
function toggleUser(e) {
  e.stopPropagation();
  document.getElementById('userPill').classList.toggle('open');
}
document.addEventListener('click', () => {
  document.getElementById('userPill')?.classList.remove('open');
});

// ── Modal ─────────────────────────────────────────────
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
function closeModalOutside(e, id) {
  if (e.target === document.getElementById(id)) closeModal(id);
}

// ── Toast ─────────────────────────────────────────────
function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3200);
}
</script>
</body>
</html>
<?php } // end layoutFooter()
