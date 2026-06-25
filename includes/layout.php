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
//  $pageTitle  = 'Nama Halaman';
//  $activeMenu = 'pegawai';
//  require_once 'includes/layout.php';
//  // ... konten halaman ...
//  include 'includes/layout_close.php';
//  ?>
//
//  KEY $activeMenu:
//  dashboard | pegawai | pegawai_satker | pengingat |
//  dokumen | arsip | satker | kgb | slks | tunjangan |
//  users | aktivitas | profil
// ============================================================

if (!isset($pageTitle))  $pageTitle  = 'SIKAPAS.RIAU';
if (!isset($activeMenu)) $activeMenu = '';
if (!isset($user))       $user       = currentUser();

// Satker nama user login
 $_lDb = getDB();
 $_lSatker = '';
if (!isAdmin()) {
    $__q = $_lDb->prepare("SELECT s.nama FROM users u LEFT JOIN satker s ON s.id=u.satker_id WHERE u.id=?");
    $__q->execute([$_SESSION['user_id']]);
    $__r = $__q->fetch();
    $_lSatker = $__r['nama'] ?? '';
}

function menuActive(string $key): string {
    global $activeMenu;
    return $activeMenu === $key ? 'active' : '';
}
function subActive(array $keys): string {
    global $activeMenu;
    return in_array($activeMenu, $keys) ? 'active' : '';
}
function subOpen(array $keys): string {
    global $activeMenu;
    return in_array($activeMenu, $keys) ? 'open' : '';
}
function arrowRotated(array $keys): string {
    global $activeMenu;
    return in_array($activeMenu, $keys) ? 'rotated' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — SIKAPAS.RIAU</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<?php if (!empty($extraHead)) echo $extraHead; ?>
<style>
/* ═══════════════════════════════════════
   ROOT & RESET
═══════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0f1e45;--navy2:#162354;--navy3:#1c2d6b;
  --blue:#1e6fbf;--blue2:#2980d4;--blue-pale:#e8f2fb;
  --green:#16a34a;--green-pale:#dcfce7;
  --red:#dc2626;--red-pale:#fee2e2;
  --orange:#ea7c17;--orange-pale:#fff3e0;
  --purple:#7c3aed;--purple-pale:#ede9fe;
  --white:#fff;
  --gray-50:#f8fafc;--gray-100:#f1f5f9;--gray-200:#e2e8f0;
  --gray-400:#94a3b8;--gray-600:#475569;--gray-800:#1e293b;
  --sidebar-w:240px;--header-h:64px;
  --radius:12px;
  --shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.05);
  --shadow-md:0 4px 12px rgba(0,0,0,.10),0 8px 32px rgba(0,0,0,.08);
}
html,body{height:100%;font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-50);color:var(--gray-800)}

/* ── Shell ── */
.shell{display:flex;height:100vh;overflow:hidden}

/* ════════════════════════════
   SIDEBAR
════════════════════════════ */
.sidebar{
  width:var(--sidebar-w);flex-shrink:0;
  background:linear-gradient(180deg,var(--navy) 0%,var(--navy2) 65%,#0b1733 100%);
  display:flex;flex-direction:column;
  overflow-y:auto;overflow-x:hidden;
  transition:width .25s ease,transform .25s ease;
  z-index:20;position:relative;
}
.sidebar::-webkit-scrollbar{width:3px}
.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}
.sidebar.collapsed{width:0;overflow:hidden}

.sb-head{padding:18px 14px 14px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px;flex-shrink:0}
.sb-logo-wrap{width:42px;height:42px;flex-shrink:0;border-radius:8px;overflow:hidden;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center}
.sb-logo-wrap img{width:42px;height:42px;object-fit:contain}
.sb-brand{font-size:14px;font-weight:800;color:#fff;letter-spacing:.3px;line-height:1.2}
.sb-tagline{font-size:9px;color:rgba(255,255,255,.4);line-height:1.5;margin-top:2px}

.sb-section{padding:14px 14px 5px;font-size:9px;font-weight:700;letter-spacing:1.4px;color:rgba(255,255,255,.28);text-transform:uppercase}

.nav-item{
  display:flex;align-items:center;gap:9px;
  padding:9px 12px;margin:1px 8px;border-radius:8px;
  cursor:pointer;color:rgba(255,255,255,.58);font-size:12.5px;font-weight:500;
  text-decoration:none;transition:all .16s;user-select:none;
}
.nav-item:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.88)}
.nav-item.active{background:var(--blue);color:#fff;font-weight:600;box-shadow:0 3px 10px rgba(30,111,191,.4)}
.nav-item svg.nav-icon{width:16px;height:16px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.nav-badge{margin-left:auto;background:#ef4444;color:#fff;border-radius:20px;font-size:9px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center}
.nav-arrow{margin-left:auto;width:13px;height:13px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2.5;transition:transform .2s}
.nav-arrow.rotated{transform:rotate(180deg)}

.nav-sub{overflow:hidden;max-height:0;transition:max-height .3s ease}
.nav-sub.open{max-height:300px}
.nav-sub-item{
  display:flex;align-items:center;gap:7px;
  padding:7px 12px 7px 36px;margin:0 8px;border-radius:7px;
  color:rgba(255,255,255,.42);font-size:12px;font-weight:500;
  text-decoration:none;transition:all .15s;cursor:pointer;
}
.nav-sub-item:hover{color:rgba(255,255,255,.78);background:rgba(255,255,255,.05)}
.nav-sub-item.active{color:#60a5fa;font-weight:600}
.nav-sub-item::before{content:'';width:4px;height:4px;border-radius:50%;background:currentColor;flex-shrink:0;opacity:.7}

.sb-footer{margin-top:auto;padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:8px;flex-shrink:0}
.sb-foot-logo{width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center}
.sb-foot-logo img{width:32px;height:32px;object-fit:contain}
.sb-foot-text{font-size:8.5px;color:rgba(255,255,255,.32);line-height:1.6}
.sb-foot-text strong{display:block;font-size:9px;color:rgba(255,255,255,.52);font-weight:700}

.mob-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:19;display:none}
.mob-overlay.show{display:block}

/* ════════════════════════════
   MAIN AREA
════════════════════════════ */
.main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}

.topbar{
  height:var(--header-h);flex-shrink:0;
  background:#fff;border-bottom:1px solid var(--gray-200);
  display:flex;align-items:center;padding:0 22px;gap:12px;
  position:sticky;top:0;z-index:10;
}
.topbar-left{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.menu-toggle{background:none;border:none;cursor:pointer;padding:7px;border-radius:8px;color:var(--gray-600);transition:background .15s;display:flex;align-items:center}
.menu-toggle:hover{background:var(--gray-100)}
.menu-toggle svg{width:19px;height:19px;stroke:currentColor;fill:none;stroke-width:2;display:block}
.topbar-title{font-size:17px;font-weight:700;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.topbar-right{display:flex;align-items:center;gap:6px}
.icon-btn{position:relative;width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;background:none;border:none;cursor:pointer;color:var(--gray-600);transition:background .15s}
.icon-btn:hover{background:var(--gray-100)}
.icon-btn svg{width:19px;height:19px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.notif-badge{position:absolute;top:6px;right:6px;width:16px;height:16px;background:#ef4444;border-radius:50%;font-size:9px;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff}

/* ═══════════════════════════════════════════════════
   USER PILL / DROPDOWN — PERBAIKAN PRESISI KLIK
════════════════════════════════════════════════════ */
.user-pill{
  display:flex;align-items:center;gap:8px;
  padding:5px 12px 5px 5px;border-radius:30px;
  background:var(--gray-50);border:1px solid var(--gray-200);
  cursor:pointer;transition:background .15s,border-color .15s;
  position:relative;user-select:none;
  /* FIX: pastikan tidak ada overflow yang memotong dropdown */
  overflow:visible;
}
.user-pill:hover{background:var(--gray-100);border-color:var(--gray-200)}
/* FIX: feedback visual saat diklik */
.user-pill:active{background:var(--gray-200)}

.user-ava{
  width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,var(--blue),var(--navy3));
  display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:13px;color:#fff;flex-shrink:0;
  /* FIX: cegah klik pada avatar menembus ke elemen di bawahnya */
  pointer-events:none;
}
.user-info{line-height:1.3;pointer-events:none}
.user-name{font-size:12.5px;font-weight:700;color:var(--gray-800);max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.user-role-lbl{font-size:10.5px;color:var(--gray-400)}
.user-caret{
  width:13px;height:13px;stroke:var(--gray-400);fill:none;stroke-width:2.5;
  flex-shrink:0;transition:transform .2s ease;pointer-events:none;
}
.user-pill.open .user-caret{transform:rotate(180deg)}

/* FIX: dropdown pakai opacity/visibility agar transisi smooth & presisi */
.user-dropdown{
  position:absolute;top:calc(100% + 8px);right:0;
  background:#fff;border-radius:12px;min-width:210px;
  box-shadow:var(--shadow-md);border:1px solid var(--gray-200);
  overflow:hidden;
  /* FIX: pakai opacity/visibility bukan display:none */
  opacity:0;visibility:hidden;
  transform:translateY(-6px) scale(.97);
  transition:opacity .18s ease,visibility .18s ease,transform .18s cubic-bezier(.16,1,.3,1);
  z-index:100;
  /* FIX: cegah klik ghost pada dropdown yang tertutup */
  pointer-events:none;
}
.user-pill.open .user-dropdown{
  opacity:1;visibility:visible;
  transform:translateY(0) scale(1);
  pointer-events:auto;
}

.dd-info{padding:13px 15px 10px;border-bottom:1px solid var(--gray-100)}
.dd-info-name{font-size:13px;font-weight:700;color:var(--gray-800)}
.dd-info-role{font-size:11px;color:var(--gray-400);margin-top:1px}
.dd-info-satker{font-size:11px;color:var(--blue);font-weight:600;margin-top:2px}
.dd-item{
  display:flex;align-items:center;gap:9px;
  padding:10px 15px;font-size:12.5px;color:var(--gray-800);
  text-decoration:none;transition:background .14s;cursor:pointer;
  border:none;background:none;width:100%;font-family:inherit;
  /* FIX: area klik harus nyaman, tidak terlalu kecil */
  min-height:40px;
}
.dd-item:hover{background:var(--gray-50)}
.dd-item svg{width:14px;height:14px;stroke:var(--gray-400);fill:none;stroke-width:2}
.dd-divider{height:1px;background:var(--gray-100);margin:3px 0}
.dd-item.red{color:var(--red)}
.dd-item.red svg{stroke:var(--red)}
.dd-item.red:hover{background:#fff5f5}

/* ════════════════════════════
   CONTENT AREA
════════════════════════════ */
.content{flex:1;overflow-y:auto;padding:24px 24px 40px}
.content::-webkit-scrollbar{width:5px}
.content::-webkit-scrollbar-thumb{background:var(--gray-200);border-radius:5px}

.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.ph-title{font-size:20px;font-weight:800;color:var(--navy);display:flex;align-items:center;gap:8px;margin-bottom:3px}
.ph-title svg{width:20px;height:20px;stroke:var(--blue);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.ph-sub{font-size:13px;color:var(--gray-400)}
.ph-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}

.btn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .16s;display:inline-flex;align-items:center;gap:6px;text-decoration:none;white-space:nowrap}
.btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;flex-shrink:0}
.btn-primary{background:var(--blue);color:#fff}
.btn-primary:hover{background:#155fa0}
.btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
.btn-outline:hover{background:var(--blue-pale)}
.btn-danger{background:var(--red-pale);color:var(--red);border:1px solid #fca5a5}
.btn-danger:hover{background:#fecaca}
.btn-warning{background:var(--orange-pale);color:var(--orange);border:1px solid #fed7aa}
.btn-warning:hover{background:#fde68a}
.btn-sm{padding:5px 12px;font-size:12px}
.btn-xs{padding:3px 9px;font-size:11px}

.card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden}
.card-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--gray-100);gap:10px}
.card-title{font-size:13.5px;font-weight:700;color:var(--gray-800)}
.card-body{padding:20px}

.stat-mini-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px}
.stat-mini{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:16px 18px;display:flex;align-items:center;gap:12px}
.stat-mini-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-mini-icon svg{width:22px;height:22px;stroke:#fff;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.si-blue{background:linear-gradient(135deg,#1e6fbf,#2980d4)}
.si-green{background:linear-gradient(135deg,#16a34a,#22c55e)}
.si-orange{background:linear-gradient(135deg,#ea7c17,#f59e0b)}
.si-purple{background:linear-gradient(135deg,#7c3aed,#a855f7)}
.si-red{background:linear-gradient(135deg,#dc2626,#ef4444)}
.stat-mini-val{font-size:24px;font-weight:800;color:var(--gray-800);line-height:1}
.stat-mini-lbl{font-size:11px;color:var(--gray-400);margin-top:2px;font-weight:500}

.tbl-wrap{overflow-x:auto}
table.sikapas-tbl{width:100%;border-collapse:collapse}
table.sikapas-tbl th{background:var(--gray-50);padding:9px 16px;font-size:11px;font-weight:700;color:var(--gray-400);text-align:left;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
table.sikapas-tbl td{padding:11px 16px;font-size:13px;border-bottom:1px solid var(--gray-100);color:var(--gray-800);vertical-align:middle}
table.sikapas-tbl tr:last-child td{border-bottom:none}
table.sikapas-tbl tr:hover td{background:var(--gray-50)}

.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;gap:4px;white-space:nowrap}
.badge-green{background:var(--green-pale);color:var(--green)}
.badge-red{background:var(--red-pale);color:var(--red)}
.badge-orange{background:var(--orange-pale);color:var(--orange)}
.badge-blue{background:var(--blue-pale);color:var(--blue)}
.badge-purple{background:var(--purple-pale);color:var(--purple)}
.badge-gray{background:var(--gray-100);color:var(--gray-600)}

.alert{padding:11px 16px;border-radius:8px;font-size:13px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:9px}
.alert svg{width:15px;height:15px;flex-shrink:0;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.alert-success{background:var(--green-pale);color:var(--green);border:1px solid #86efac}
.alert-error{background:var(--red-pale);color:var(--red);border:1px solid #fca5a5}
.alert-info{background:var(--blue-pale);color:var(--blue);border:1px solid #93c5fd}
.alert-warning{background:var(--orange-pale);color:var(--orange);border:1px solid #fed7aa}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
.form-label{font-size:12.5px;font-weight:600;color:var(--gray-600)}
.form-section-label{font-size:11px;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:.5px;grid-column:1/-1;padding:8px 0 2px;border-bottom:1px solid var(--gray-200);margin-top:6px}
.form-control{padding:9px 13px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--gray-800);outline:none;transition:border-color .18s,box-shadow .18s;background:#fff;width:100%}
.form-control:focus{border-color:var(--blue2);box-shadow:0 0 0 3px rgba(30,111,191,.08)}
select.form-control{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 11px center;padding-right:30px}
textarea.form-control{resize:vertical;min-height:80px}

.modal-overlay{position:fixed;inset:0;background:rgba(10,20,50,.5);display:none;z-index:200;overflow-y:auto;backdrop-filter:blur(2px)}
.modal-overlay.open{display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem}
.modal{background:#fff;border-radius:16px;width:100%;max-width:600px;box-shadow:0 24px 64px rgba(0,0,0,.2);margin:auto;overflow:hidden}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--gray-100)}
.modal-title{font-size:16px;font-weight:700;color:var(--navy)}
.modal-close{background:none;border:none;font-size:22px;color:var(--gray-400);cursor:pointer;line-height:1;padding:2px 6px;border-radius:6px;transition:background .14s}
.modal-close:hover{background:var(--gray-100);color:var(--gray-600)}
.modal-body{padding:22px}
.modal-foot{display:flex;justify-content:flex-end;gap:8px;padding:14px 22px;border-top:1px solid var(--gray-100);background:var(--gray-50)}

.loading{text-align:center;padding:2.5rem;color:var(--gray-400);font-size:13px}

.toast{position:fixed;bottom:24px;right:24px;background:#1a2e5a;color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:500;box-shadow:var(--shadow-md);z-index:999;opacity:0;transform:translateY(8px);transition:all .28s;pointer-events:none;max-width:320px}
.toast.show{opacity:1;transform:translateY(0)}
.toast.success{background:#166534}
.toast.error{background:#991b1b}
.toast.warning{background:#92400e}

@media(max-width:900px){
  .sidebar{position:fixed;top:0;left:0;height:100%;transform:translateX(-100%);width:var(--sidebar-w) !important}
  .sidebar.mob-open{transform:translateX(0)}
  .form-grid{grid-template-columns:1fr}
  .content{padding:16px 14px 32px}
  .stat-mini-row{grid-template-columns:1fr 1fr}
}
@media(max-width:560px){
  .stat-mini-row{grid-template-columns:1fr}
  .ph-title{font-size:17px}
  .topbar{padding:0 14px}
}
</style>
</head>
<body>
<div class="shell">

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar" id="sidebar">

  <div class="sb-head">
    <div class="sb-logo-wrap">
      <img src="logo.png" alt="SIKAPAS" onerror="this.parentElement.innerHTML='&#x1F3DB;&#xFE0F;'">
    </div>
    <div>
      <div class="sb-brand">SIKAPAS.RIAU</div>
      <div class="sb-tagline">Sistem Informasi Kepegawaian<br>dan Administrasi Pemasyarakatan</div>
    </div>
  </div>

  <div class="sb-section">Menu Utama</div>

  <a href="dashboard.php" class="nav-item <?= menuActive('dashboard') ?>">
    <svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Dashboard
  </a>

  <div class="nav-item <?= subActive(['pegawai','pegawai_satker','pengingat']) ?>"
       onclick="toggleSub('sub-pegawai',this)">
    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Pegawai
    <svg class="nav-arrow <?= arrowRotated(['pegawai','pegawai_satker','pengingat']) ?>" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub <?= subOpen(['pegawai','pegawai_satker','pengingat']) ?>" id="sub-pegawai">
    <a href="pegawai_satker.php" class="nav-sub-item <?= menuActive('pegawai_satker') ?>">Data per Satker</a>
    <a href="index.php"          class="nav-sub-item <?= menuActive('pegawai') ?>">Semua Pegawai</a>
    <a href="pengingat.php"      class="nav-sub-item <?= menuActive('pengingat') ?>">Pengingat Pensiun</a>
  </div>

  <div class="nav-item <?= subActive(['dokumen','arsip']) ?>"
       onclick="toggleSub('sub-arsip',this)">
    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    Arsip Kepegawaian
    <svg class="nav-arrow <?= arrowRotated(['dokumen','arsip']) ?>" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub <?= subOpen(['dokumen','arsip']) ?>" id="sub-arsip">
    <a href="dokumen.php" class="nav-sub-item <?= menuActive('dokumen') ?>">Dokumen Pegawai</a>
    <a href="arsip.php"   class="nav-sub-item <?= menuActive('arsip') ?>">Arsip File</a>
  </div>

  <a href="satker.php" class="nav-item <?= menuActive('satker') ?>">
    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Satker
  </a>

  <div class="nav-item <?= subActive(['kgb','slks','tunjangan']) ?>"
       onclick="toggleSub('sub-lap',this)">
    <svg class="nav-icon" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    Laporan
    <svg class="nav-arrow <?= arrowRotated(['kgb','slks','tunjangan']) ?>" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub <?= subOpen(['kgb','slks','tunjangan']) ?>" id="sub-lap">
    <a href="index.php" class="nav-sub-item <?= menuActive('kgb') ?>">Kenaikan Gaji Berkala</a>
    <a href="index.php" class="nav-sub-item <?= menuActive('slks') ?>">SLKS / SKP</a>
    <a href="index.php" class="nav-sub-item <?= menuActive('tunjangan') ?>">Tunjangan</a>
  </div>

  <?php if (isAdmin()): ?>
  <div class="sb-section">Pengaturan</div>
  <a href="users.php" class="nav-item <?= menuActive('users') ?>">
    <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    Kelola User
  </a>
  <a href="aktivitas.php" class="nav-item <?= menuActive('aktivitas') ?>">
    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
    Log Aktivitas
  </a>
  <?php endif; ?>

  <div class="sb-section">Informasi</div>
  <a href="#" class="nav-item">
    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    Pengumuman
    <span class="nav-badge">2</span>
  </a>
  <a href="#" class="nav-item">
    <svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    Kalender Kegiatan
  </a>
  <a href="profile.php" class="nav-item <?= menuActive('profil') ?>">
    <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    Profil Saya
  </a>

  <div class="sb-footer">
    <div class="sb-foot-logo">
      <img src="logo.png" alt="" onerror="this.style.display='none'">
    </div>
    <div class="sb-foot-text">
      <strong>KEMENTERIAN IMIGRASI<br>DAN PEMASYARAKATAN</strong>
      © 2026 SIKAPAS.RIAU
    </div>
  </div>

</aside>

<!-- Mobile overlay -->
<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>

<!-- ══════════════ MAIN ══════════════ -->
<div class="main">

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
      <!-- PERBAIKAN: dropdown administrator presisi -->
      <div class="user-pill" id="userPill" onclick="toggleUser(event)" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
        <div class="user-ava"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>
        <div class="user-info">
          <div class="user-name"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></div>
          <div class="user-role-lbl"><?= isAdmin() ? 'Administrator' : ucfirst($user['role'] ?? '') ?></div>
        </div>
        <svg class="user-caret" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        <div class="user-dropdown" role="menu">
          <div class="dd-info">
            <div class="dd-info-name"><?= htmlspecialchars($user['nama'] ?? '') ?></div>
            <div class="dd-info-role"><?= isAdmin() ? 'Administrator' : ucfirst($user['role'] ?? '') ?></div>
            <?php if ($_lSatker): ?><div class="dd-info-satker">&#x1F3E2; <?= htmlspecialchars($_lSatker) ?></div><?php endif; ?>
          </div>
          <a href="profile.php" class="dd-item" role="menuitem">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Profil Saya
          </a>
          <div class="dd-divider"></div>
          <a href="logout.php" class="dd-item red" role="menuitem">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
          </a>
        </div>
      </div>
    </div>
  </header>

  <div class="content">
