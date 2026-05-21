<?php
// ============================================================
//  SIPATEN — Halaman Utama (Frontend + Backend Integration)
//  File: index.php
// ============================================================
require_once 'includes/config.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIPATEN - Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy: #1a2e5a; --navy-dark: #0f1d3a; --navy-mid: #243570;
    --blue: #1e6fbf; --blue-light: #3a8fd8; --blue-pale: #e8f2fb;
    --white: #ffffff; --gray-100: #f4f6fb; --gray-200: #e8edf5;
    --gray-400: #9eadc8; --gray-600: #5a6a8a; --text: #1a2e5a;
    --shadow: 0 2px 12px rgba(30,60,120,0.10);
    --shadow-hover: 0 6px 24px rgba(30,60,120,0.16); --radius: 12px;
  }
  body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--gray-100); color:var(--text); min-height:100vh; display:flex; flex-direction:column; }
  header { background:linear-gradient(135deg,var(--navy-dark) 0%,var(--navy-mid) 60%,var(--blue) 100%); padding:0 2rem; height:64px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 2px 16px rgba(0,0,0,0.25); }
  .logo { display:flex; align-items:center; gap:12px; }
  .logo-emblem { width:42px; height:42px; background:var(--white); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
  .logo-emblem svg { width:32px; height:32px; }
  .logo-text { line-height:1.2; }
  .logo-text .brand { font-size:22px; font-weight:700; color:var(--white); letter-spacing:1px; }
  .logo-text .tagline { font-size:10px; color:rgba(255,255,255,0.65); font-weight:400; }
  .header-right { display:flex; align-items:center; gap:10px; }
  .user-badge { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:32px; padding:6px 14px 6px 8px; cursor:pointer; transition:background .2s; text-decoration:none; }
  .user-badge:hover { background:rgba(255,255,255,0.2); }
  .user-avatar { width:32px; height:32px; border-radius:50%; background:#e87d2a; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; color:var(--white); }
  .user-name { font-size:13px; font-weight:500; color:var(--white); }
  .btn-logout { padding:6px 14px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); border-radius:8px; color:#fff; font-size:12px; font-weight:600; font-family:inherit; cursor:pointer; text-decoration:none; }
  .btn-logout:hover { background:rgba(255,0,0,0.3); }
  .hero { background:linear-gradient(135deg,var(--navy-dark) 0%,var(--navy-mid) 60%,var(--blue) 100%); padding:2.5rem 2rem 3.5rem; text-align:center; position:relative; overflow:hidden; }
  .hero::before { content:''; position:absolute; top:-60px; right:-80px; width:300px; height:300px; border-radius:50%; background:rgba(255,255,255,0.04); }
  .hero::after { content:''; position:absolute; bottom:-40px; left:-60px; width:220px; height:220px; border-radius:50%; background:rgba(255,255,255,0.04); }
  .hero h1 { font-size:18px; font-weight:600; color:rgba(255,255,255,0.85); margin-bottom:4px; }
  .hero h2 { font-size:22px; font-weight:700; color:var(--white); margin-bottom:1.5rem; }
  .search-box { max-width:540px; margin:0 auto; position:relative; z-index:1; }
  .search-box input { width:100%; padding:13px 20px 13px 48px; border-radius:32px; border:none; font-size:14px; font-family:inherit; background:var(--white); color:var(--text); box-shadow:0 4px 20px rgba(0,0,0,0.15); outline:none; transition:box-shadow .2s; }
  .search-box input:focus { box-shadow:0 4px 24px rgba(0,0,0,0.22); }
  .search-box input::placeholder { color:var(--gray-400); }
  .search-icon { position:absolute; left:18px; top:50%; transform:translateY(-50%); color:var(--gray-400); pointer-events:none; }
  #search-results { position:absolute; top:calc(100% + 6px); left:0; right:0; background:#fff; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.15); overflow:hidden; display:none; z-index:10; }
  main { flex:1; padding:2rem; max-width:960px; margin:0 auto; width:100%; }
  .section-title { font-size:13px; font-weight:600; color:var(--gray-600); letter-spacing:0.5px; text-transform:uppercase; margin-bottom:1rem; }
  .menu-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:2.5rem; }
  .menu-card { background:var(--white); border-radius:var(--radius); padding:1.75rem 1.25rem 1.5rem; display:flex; flex-direction:column; align-items:center; gap:14px; cursor:pointer; border:1.5px solid var(--gray-200); box-shadow:var(--shadow); transition:transform .18s,box-shadow .18s,border-color .18s; text-decoration:none; color:inherit; position:relative; overflow:hidden; }
  .menu-card::before { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--blue),var(--blue-light)); transform:scaleX(0); transition:transform .2s; }
  .menu-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-hover); border-color:var(--blue-light); }
  .menu-card:hover::before { transform:scaleX(1); }
  .icon-wrap { width:64px; height:64px; border-radius:16px; background:var(--blue-pale); display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .menu-card:hover .icon-wrap { background:#d0e7f7; }
  .icon-wrap svg { width:34px; height:34px; color:var(--blue); fill:none; stroke:currentColor; stroke-width:1.6; stroke-linecap:round; stroke-linejoin:round; }
  .menu-label { font-size:14px; font-weight:600; color:var(--navy); text-align:center; line-height:1.3; }
  .page { display:none; }
  .page.active { display:block; }
  #page-home.active { display:block; }
  .stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
  .stat-card { background:var(--white); border-radius:var(--radius); padding:1.25rem; border:1px solid var(--gray-200); box-shadow:var(--shadow); }
  .stat-label { font-size:12px; color:var(--gray-600); font-weight:500; margin-bottom:4px; }
  .stat-value { font-size:28px; font-weight:700; color:var(--navy); }
  .stat-sub { font-size:12px; color:var(--gray-400); margin-top:2px; }
  .table-wrapper { background:var(--white); border-radius:var(--radius); border:1px solid var(--gray-200); box-shadow:var(--shadow); overflow:hidden; }
  .table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--gray-200); gap:8px; flex-wrap:wrap; }
  .table-toolbar input { padding:8px 14px; border:1px solid var(--gray-200); border-radius:8px; font-size:13px; font-family:inherit; outline:none; width:240px; transition:border-color .2s; }
  .table-toolbar input:focus { border-color:var(--blue-light); }
  .btn { padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; transition:all .18s; border:none; }
  .btn-primary { background:var(--blue); color:var(--white); }
  .btn-primary:hover { background:var(--navy); }
  .btn-outline { background:var(--white); color:var(--blue); border:1.5px solid var(--blue); }
  .btn-outline:hover { background:var(--blue-pale); }
  .btn-danger { background:#fce8e8; color:#9b2222; border:1px solid #f5c0c0; }
  .btn-danger:hover { background:#f5c0c0; }
  .btn-sm { padding:4px 10px; font-size:12px; }
  table { width:100%; border-collapse:collapse; }
  th { background:var(--gray-100); padding:10px 14px; font-size:12px; font-weight:600; color:var(--gray-600); text-align:left; text-transform:uppercase; letter-spacing:0.4px; }
  td { padding:12px 14px; font-size:13px; border-bottom:1px solid var(--gray-100); color:var(--text); }
  tr:last-child td { border-bottom:none; }
  tr:hover td { background:var(--blue-pale); }
  .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
  .badge-active { background:#e2f5ea; color:#1a7a38; }
  .badge-inactive { background:#fce8e8; color:#9b2222; }
  .badge-pending { background:#fef3dd; color:#8a5500; }
  .back-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--blue); cursor:pointer; margin-bottom:1.25rem; padding:6px 0; background:none; border:none; font-family:inherit; transition:color .18s; }
  .back-btn:hover { color:var(--navy); }
  .back-btn svg { width:16px; height:16px; }
  .page-title { font-size:20px; font-weight:700; color:var(--navy); margin-bottom:1.25rem; }
  footer { background:var(--white); border-top:1px solid var(--gray-200); padding:14px 2rem; display:flex; justify-content:space-between; align-items:center; }
  footer span { font-size:12px; color:var(--gray-400); }
  .version { font-size:12px; font-weight:600; color:var(--blue); }
  .form-card { background:var(--white); border-radius:var(--radius); border:1px solid var(--gray-200); box-shadow:var(--shadow); padding:1.5rem; margin-bottom:1.5rem; }
  .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
  .form-group { display:flex; flex-direction:column; gap:6px; }
  .form-group.full { grid-column:1/-1; }
  label { font-size:13px; font-weight:600; color:var(--gray-600); }
  input[type="text"],input[type="date"],input[type="number"],select,textarea { padding:10px 14px; border:1px solid var(--gray-200); border-radius:8px; font-size:13px; font-family:inherit; color:var(--text); outline:none; transition:border-color .2s; background:#fff; }
  input:focus,select:focus,textarea:focus { border-color:var(--blue-light); }
  .form-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:1.25rem; }
  .modal-overlay { position:fixed; inset:0; background:rgba(10,20,50,0.55); display:none; align-items:center; justify-content:center; z-index:200; }
  .modal-overlay.open { display:flex; }
  .modal { background:var(--white); border-radius:16px; padding:2rem; width:100%; max-width:520px; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
  .modal-title { font-size:18px; font-weight:700; color:var(--navy); margin-bottom:1.25rem; }
  .file-list { display:flex; flex-direction:column; gap:10px; }
  .file-item { background:var(--white); border:1px solid var(--gray-200); border-radius:var(--radius); padding:1rem 1.25rem; display:flex; align-items:center; gap:14px; box-shadow:var(--shadow); }
  .file-icon { width:44px; height:44px; border-radius:10px; background:var(--blue-pale); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .file-icon svg { width:24px; height:24px; fill:none; stroke:var(--blue); stroke-width:1.8; }
  .file-info { flex:1; min-width:0; }
  .file-name { font-size:14px; font-weight:600; color:var(--navy); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .file-meta { font-size:12px; color:var(--gray-400); margin-top:2px; }
  .file-action { padding:6px 14px; background:var(--blue-pale); border:none; border-radius:6px; font-size:12px; font-weight:600; color:var(--blue); cursor:pointer; text-decoration:none; }
  .file-action:hover { background:#d0e7f7; }
  .toast { position:fixed; bottom:24px; right:24px; background:#1a2e5a; color:#fff; padding:12px 20px; border-radius:10px; font-size:14px; font-weight:500; box-shadow:0 4px 20px rgba(0,0,0,0.2); z-index:999; opacity:0; transform:translateY(10px); transition:all .3s; pointer-events:none; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#1a7a38; }
  .toast.error   { background:#9b2222; }
  .loading { text-align:center; padding:2rem; color:var(--gray-400); font-size:14px; }
  select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:32px; }
  @media(max-width:640px) {
    .menu-grid { grid-template-columns:repeat(2,1fr); }
    .stats-grid { grid-template-columns:repeat(2,1fr); }
    .form-grid { grid-template-columns:1fr; }
    main { padding:1rem; }
    .table-toolbar { flex-direction:column; align-items:stretch; }
    .table-toolbar input { width:100%; }
  }
</style>
</head>
<body>

<!-- HEADER -->
<header>
  <div class="logo-emblem">
  <img src="logo.png" alt="SIPATEN" style="width:42px;height:42px;object-fit:contain;border-radius:50%;">
</div>
    <div class="logo-text">
      <div class="brand">SIPATEN</div>
      <div class="tagline">Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman</div>
    </div>
  </div>
  <div class="header-right">
    <a href="profile.php" class="user-badge">
  <div class="user-avatar"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>
  <span class="user-name"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></span>
</a>
    </div>
    <a href="logout.php" class="btn-logout">Keluar</a>
  </div>
</header>

<!-- ────────── HOME PAGE ────────── -->
<div id="page-home" class="page active">
  <div class="hero">
    <h1>Selamat Datang, <?= htmlspecialchars($user['nama'] ?? 'Admin') ?></h1>
    <h2>Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman</h2>
    <div class="search-box">
      <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" id="search-input" placeholder="Cari nama pegawai atau NIP..." oninput="handleSearch(this.value)" autocomplete="off">
      <div id="search-results"></div>
    </div>
  </div>

  <main>
    <p class="section-title">Menu Utama</p>
    <div class="menu-grid">
      <div class="menu-card" onclick="showPage('ringkasan')">
        <div class="icon-wrap"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div>
        <span class="menu-label">Ringkasan Kepegawaian</span>
      </div>
      <div class="menu-card" onclick="showPage('data-pegawai')">
        <div class="icon-wrap"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <span class="menu-label">Data Pegawai</span>
      </div>
      <div class="menu-card" onclick="showPage('kenaikan-gaji')">
        <div class="icon-wrap"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <span class="menu-label">Kenaikan Gaji Berkala</span>
      </div>
      <div class="menu-card" onclick="showPage('tunjangan')">
        <div class="icon-wrap"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <span class="menu-label">Tunjangan Pemasyarakatan</span>
      </div>
      <div class="menu-card" onclick="showPage('slks')">
        <div class="icon-wrap"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
        <span class="menu-label">SLKS / SKP</span>
      </div>
      <div class="menu-card" onclick="showPage('arsip')">
        <div class="icon-wrap"><svg viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg></div>
        <span class="menu-label">Arsip File</span>
      </div>
     <?php if (isAdmin()): ?>
 <div class="menu-card" onclick="window.location.href='dokumen.php'">
  <div class="icon-wrap"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
  <span class="menu-label">Arsip Dokumen</span>
</div>
<div class="menu-card" onclick="window.location.href='pengingat.php'">
  <div class="icon-wrap"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
  <span class="menu-label">Pengingat Pensiun</span>
</div>
<?php if (isAdmin()): ?>
<div class="menu-card" onclick="window.location.href='aktivitas.php'">
  <div class="icon-wrap"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
  <span class="menu-label">Riwayat Aktivitas</span>
</div>
<?php endif; ?>
<div class="menu-card" onclick="window.location.href='users.php'">
  <div class="icon-wrap">
    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
  </div>
  <span class="menu-label">Kelola User</span>
</div>
<div class="menu-card" onclick="window.location.href='profile.php'">
  <div class="icon-wrap">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
  </div>
  <span class="menu-label">Profil Saya</span>
</div>
<?php else: ?>
<div class="menu-card" onclick="window.location.href='profile.php'">
  <div class="icon-wrap">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
  </div>
  <span class="menu-label">Profil Saya</span>
</div>
<?php endif; ?>
    </div>
  </main>
</div>

<!-- ────────── RINGKASAN PAGE ────────── -->
<div id="page-ringkasan" class="page">
  <main>
    <button class="back-btn" onclick="goHome()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg> Kembali
    </button>
    <p class="page-title">Ringkasan Kepegawaian</p>
    <div class="stats-grid" id="stats-grid"><div class="loading">Memuat data...</div></div>
    <div class="table-wrapper">
      <div class="table-toolbar">
        <span style="font-size:14px;font-weight:700;color:var(--navy)">Daftar Satuan Kerja</span>
      </div>
      <table><thead><tr><th>No</th><th>Satuan Kerja</th><th>Total</th><th>Aktif</th></tr></thead>
        <tbody id="satker-tbody"><tr><td colspan="4" class="loading">Memuat...</td></tr></tbody>
      </table>
    </div>
  </main>
</div>

<!-- ────────── DATA PEGAWAI PAGE ────────── -->
<div id="page-data-pegawai" class="page">
  <main>
    <button class="back-btn" onclick="goHome()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg> Kembali
    </button>
    <p class="page-title">Data Pegawai</p>
    <div class="table-wrapper">
      <div class="table-toolbar">
        <input type="text" id="pegawai-search" placeholder="Cari NIP / Nama..." oninput="loadPegawai(this.value)">
        <button class="btn btn-primary" onclick="openModalPegawai()">+ Tambah Pegawai</button>
      </div>
      <table><thead><tr><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Gol.</th><th>Satker</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody id="pegawai-tbody"><tr><td colspan="7" class="loading">Memuat data...</td></tr></tbody>
      </table>
    </div>
  </main>
</div>

<!-- ────────── KENAIKAN GAJI BERKALA ────────── -->
<div id="page-kenaikan-gaji" class="page">
  <main>
    <button class="back-btn" onclick="goHome()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg> Kembali
    </button>
    <p class="page-title">Kenaikan Gaji Berkala</p>
    <div class="table-wrapper" style="margin-bottom:1.5rem">
      <div class="table-toolbar">
        <input type="text" id="kgb-search" placeholder="Cari nama pegawai..." oninput="loadKGB(this.value)">
        <div style="display:flex;gap:8px">
          <select id="kgb-bulan" onchange="loadKGB()">
            <option value="">Semua Bulan</option>
            <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
            <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
            <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
            <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
          </select>
          <select id="kgb-tahun" onchange="loadKGB()">
            <option>2026</option><option>2025</option><option>2024</option>
          </select>
        </div>
      </div>
      <table><thead><tr><th>Nama</th><th>NIP</th><th>Gol. Lama</th><th>Gol. Baru</th><th>TMT</th><th>No. SK</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody id="kgb-tbody"><tr><td colspan="8" class="loading">Memuat data...</td></tr></tbody>
      </table>
    </div>
    <p class="section-title">Ajukan Kenaikan Gaji Berkala</p>
    <div class="form-card">
      <input type="hidden" id="kgb-id">
      <div class="form-grid">
        <div class="form-group"><label>NIP Pegawai</label><input type="text" id="kgb-nip" placeholder="Masukkan NIP"></div>
        <div class="form-group"><label>Nama Pegawai</label><input type="text" id="kgb-nama" placeholder="Otomatis terisi" readonly style="background:#f4f6fb"></div>
        <div class="form-group"><label>Golongan Lama</label>
          <select id="kgb-gol-lama"><?= golonganOptions() ?></select></div>
        <div class="form-group"><label>Golongan Baru</label>
          <select id="kgb-gol-baru"><?= golonganOptions() ?></select></div>
        <div class="form-group"><label>TMT</label><input type="date" id="kgb-tmt"></div>
        <div class="form-group"><label>No. SK</label><input type="text" id="kgb-nosk" placeholder="Nomor surat keputusan"></div>
      </div>
      <div class="form-actions">
        <button class="btn btn-outline" onclick="resetKGBForm()">Reset</button>
        <button class="btn btn-primary" onclick="saveKGB()">Simpan</button>
      </div>
    </div>
  </main>
</div>

<!-- ────────── TUNJANGAN PAGE ────────── -->
<div id="page-tunjangan" class="page">
  <main>
    <button class="back-btn" onclick="goHome()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg> Kembali
    </button>
    <p class="page-title">Tunjangan Pemasyarakatan</p>
    <div class="table-wrapper" style="margin-bottom:1.5rem">
      <div class="table-toolbar">
        <input type="text" id="tunj-search" placeholder="Cari nama / NIP..." oninput="loadTunjangan(this.value)">
        <button class="btn btn-primary" onclick="openModalTunjangan()">+ Tambah Data</button>
      </div>
      <table><thead><tr><th>Nama</th><th>NIP</th><th>Jenis Tunjangan</th><th>Nominal</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody id="tunj-tbody"><tr><td colspan="7" class="loading">Memuat data...</td></tr></tbody>
      </table>
    </div>
  </main>
</div>

<!-- ────────── SLKS PAGE ────────── -->
<div id="page-slks" class="page">
  <main>
    <button class="back-btn" onclick="goHome()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg> Kembali
    </button>
    <p class="page-title">SLKS – Sasaran Kinerja Pegawai</p>
    <div class="table-wrapper" style="margin-bottom:1.5rem">
      <div class="table-toolbar">
        <input type="text" id="slks-search" placeholder="Cari nama / NIP..." oninput="loadSLKS(this.value)">
        <button class="btn btn-primary" onclick="openModalSLKS()">+ Input SLKS</button>
      </div>
      <table><thead><tr><th>Nama</th><th>NIP</th><th>Periode</th><th>Nilai SKP</th><th>Perilaku</th><th>Total</th><th>Ket.</th><th>Aksi</th></tr></thead>
        <tbody id="slks-tbody"><tr><td colspan="8" class="loading">Memuat data...</td></tr></tbody>
      </table>
    </div>
  </main>
</div>

<!-- ────────── ARSIP PAGE ────────── -->
<div id="page-arsip" class="page">
  <main>
    <button class="back-btn" onclick="goHome()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg> Kembali
    </button>
    <p class="page-title">Arsip File</p>
    <div style="display:flex;gap:10px;margin-bottom:1.25rem;align-items:center;flex-wrap:wrap">
      <input type="text" id="arsip-search" placeholder="Cari dokumen..." oninput="loadArsip(this.value)"
             style="padding:10px 14px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none;width:280px;transition:border-color .2s"
             onfocus="this.style.borderColor='var(--blue-light)'" onblur="this.style.borderColor='var(--gray-200)'">
      <button class="btn btn-primary" onclick="document.getElementById('file-upload').click()">+ Upload Dokumen</button>
      <input type="file" id="file-upload" style="display:none"
             accept=".pdf,.xlsx,.xls,.docx,.doc,.png,.jpg,.jpeg"
             onchange="uploadFile(this)">
    </div>
    <div class="file-list" id="arsip-list"><div class="loading">Memuat arsip...</div></div>
  </main>
</div>

<!-- ──── MODAL TAMBAH/EDIT PEGAWAI ──── -->
<div class="modal-overlay" id="modal-pegawai" onclick="closeModalOutside(event,'modal-pegawai')">
  <div class="modal">
    <p class="modal-title" id="modal-pegawai-title">Tambah Data Pegawai Baru</p>
    <input type="hidden" id="pegawai-id">
    <div class="form-grid">
      <div class="form-group"><label>NIP</label><input type="text" id="peg-nip" placeholder="18 digit NIP"></div>
      <div class="form-group"><label>Nama Lengkap</label><input type="text" id="peg-nama" placeholder="Nama dan gelar"></div>
      <div class="form-group"><label>Jabatan</label><input type="text" id="peg-jabatan" placeholder="Jabatan struktural/fungsional"></div>
      <div class="form-group"><label>Golongan</label>
        <select id="peg-golongan"><?= golonganOptions() ?></select></div>
      <div class="form-group"><label>Satuan Kerja</label>
        <select id="peg-satker"><option value="">Pilih Satker</option></select></div>
      <div class="form-group"><label>TMT PNS</label><input type="date" id="peg-tmt"></div>
      <div class="form-group"><label>Status</label>
        <select id="peg-status">
          <option value="Aktif">Aktif</option>
          <option value="Pensiun">Pensiun</option>
          <option value="Dalam Proses">Dalam Proses</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-outline" onclick="closeModal('modal-pegawai')">Batal</button>
      <button class="btn btn-primary" onclick="savePegawai()">Simpan Data</button>
    </div>
  </div>
</div>

<!-- ──── MODAL TUNJANGAN ──── -->
<div class="modal-overlay" id="modal-tunjangan" onclick="closeModalOutside(event,'modal-tunjangan')">
  <div class="modal">
    <p class="modal-title">Tambah / Edit Tunjangan</p>
    <input type="hidden" id="tunj-id">
    <div class="form-grid">
      <div class="form-group"><label>NIP Pegawai</label><input type="text" id="tunj-nip" placeholder="Masukkan NIP"></div>
      <div class="form-group"><label>Jenis Tunjangan</label>
        <select id="tunj-jenis">
          <option>Tunjangan Kinerja</option><option>Tunjangan Jabatan</option>
          <option>Tunjangan Umum</option><option>Tunjangan Khusus</option>
        </select></div>
      <div class="form-group"><label>Nominal (Rp)</label><input type="number" id="tunj-nominal" placeholder="Contoh: 5500000"></div>
      <div class="form-group"><label>Periode</label><input type="text" id="tunj-periode" placeholder="Contoh: Mei 2026"></div>
      <div class="form-group"><label>Status</label>
        <select id="tunj-status">
          <option value="Aktif">Aktif</option><option value="Verifikasi">Verifikasi</option><option value="Nonaktif">Nonaktif</option>
        </select></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-outline" onclick="closeModal('modal-tunjangan')">Batal</button>
      <button class="btn btn-primary" onclick="saveTunjangan()">Simpan</button>
    </div>
  </div>
</div>

<!-- ──── MODAL SLKS ──── -->
<div class="modal-overlay" id="modal-slks" onclick="closeModalOutside(event,'modal-slks')">
  <div class="modal">
    <p class="modal-title">Input Data SLKS / SKP</p>
    <input type="hidden" id="slks-id">
    <div class="form-grid">
      <div class="form-group"><label>NIP Pegawai</label><input type="text" id="slks-nip" placeholder="Masukkan NIP"></div>
      <div class="form-group"><label>Periode (Tahun)</label><input type="number" id="slks-periode" value="<?= date('Y') ?>"></div>
      <div class="form-group"><label>Nilai SKP</label><input type="number" id="slks-skp" placeholder="0-100" min="0" max="100"></div>
      <div class="form-group"><label>Nilai Perilaku</label><input type="number" id="slks-perilaku" placeholder="0-100" min="0" max="100"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-outline" onclick="closeModal('modal-slks')">Batal</button>
      <button class="btn btn-primary" onclick="saveSLKS()">Simpan</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- FOOTER -->
<footer>
  <span>Copyright © 2026 SIPATEN. Kementerian Imigrasi & Pemasyarakatan.</span>
  <span class="version">✦ Versi 1.0</span>
</footer>

<script>
// ═══════════════════════════════════════════════════════
//  SIPATEN Frontend JS — Terhubung ke API Backend
// ═══════════════════════════════════════════════════════

const API = 'api/index.php';
const pages = ['home','ringkasan','data-pegawai','kenaikan-gaji','tunjangan','slks','arsip'];

// ── Navigasi ──────────────────────────────────────────
function showPage(id) {
  pages.forEach(p => {
    const el = document.getElementById('page-' + p);
    if (el) el.classList.remove('active');
  });
  const target = document.getElementById('page-' + id);
  if (target) { target.classList.add('active'); window.scrollTo(0,0); }
  // Muat data sesuai halaman
  if (id === 'ringkasan')      loadRingkasan();
  if (id === 'data-pegawai')   loadPegawai();
  if (id === 'kenaikan-gaji')  { loadKGB(); loadSatkerOptions(); }
  if (id === 'tunjangan')      loadTunjangan();
  if (id === 'slks')           loadSLKS();
  if (id === 'arsip')          loadArsip();
}
function goHome() { showPage('home'); }

// ── Toast ────────────────────────────────────────────
function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3000);
}

// ── API fetch helper ──────────────────────────────────
async function api(module, action, params={}) {
  const url = new URL(API, location.href);
  url.searchParams.set('module', module);
  url.searchParams.set('action', action);
  Object.entries(params).forEach(([k,v]) => url.searchParams.set(k, v));
  const r = await fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} });
  if (r.status === 401) { location.href = 'login.php'; return null; }
  return r.json();
}

async function apiPost(module, action, body={}) {
  const url = `${API}?module=${module}&action=${action}`;
  const r = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
    body: JSON.stringify(body)
  });
  if (r.status === 401) { location.href = 'login.php'; return null; }
  return r.json();
}

// ── Badge HTML ────────────────────────────────────────
function badge(txt) {
  const map = {
    'Aktif':'badge-active','Selesai':'badge-active','Sangat Baik':'badge-active','Baik':'badge-active',
    'Pensiun':'badge-inactive','Belum':'badge-inactive','Nonaktif':'badge-inactive','Kurang':'badge-inactive',
    'Proses':'badge-pending','Verifikasi':'badge-pending','Cukup':'badge-pending','Dalam Proses':'badge-pending'
  };
  const cls = map[txt] || 'badge-pending';
  return `<span class="badge ${cls}">${txt}</span>`;
}

// ═══════════════ RINGKASAN ═══════════════════════════
async function loadRingkasan() {
  const res = await api('ringkasan','list');
  if (!res?.success) return;
  const d = res.data;
  document.getElementById('stats-grid').innerHTML = `
    <div class="stat-card"><div class="stat-label">Total Pegawai</div><div class="stat-value">${d.total_pegawai}</div><div class="stat-sub">Aktif & Tidak Aktif</div></div>
    <div class="stat-card"><div class="stat-label">Pegawai Aktif</div><div class="stat-value" style="color:#1a7a38">${d.pegawai_aktif}</div><div class="stat-sub">Per bulan ini</div></div>
    <div class="stat-card"><div class="stat-label">Pegawai Pensiun</div><div class="stat-value" style="color:#9b2222">${d.pensiun}</div><div class="stat-sub">Total</div></div>
    <div class="stat-card"><div class="stat-label">Kenaikan Gaji Berkala</div><div class="stat-value" style="color:#1e6fbf">${d.kgb_bulan_ini}</div><div class="stat-sub">Bulan ini</div></div>
    <div class="stat-card"><div class="stat-label">Tunjangan Aktif</div><div class="stat-value" style="color:#7b4f00">${d.tunjangan_aktif}</div><div class="stat-sub">Penerima tunjangan</div></div>
    <div class="stat-card"><div class="stat-label">Arsip File</div><div class="stat-value">${d.arsip}</div><div class="stat-sub">Dokumen tersimpan</div></div>
  `;
  document.getElementById('satker-tbody').innerHTML = d.satker.map((s,i) => `
    <tr><td>${i+1}</td><td>${s.satker}</td><td>${s.total}</td><td>${badge(s.aktif + ' Aktif')}</td></tr>
  `).join('') || '<tr><td colspan="4">Tidak ada data</td></tr>';
}

// ═══════════════ PEGAWAI ═════════════════════════════
async function loadPegawai(q='') {
  document.getElementById('pegawai-tbody').innerHTML = '<tr><td colspan="7" class="loading">Memuat...</td></tr>';
  const res = await api('pegawai','list',{q});
  if (!res?.success) return;
  document.getElementById('pegawai-tbody').innerHTML = res.data.map(p => `
    <tr>
      <td>${p.nip}</td><td>${p.nama}</td><td>${p.jabatan||'-'}</td>
      <td>${p.golongan||'-'}</td><td>${p.satker||'-'}</td>
      <td>${badge(p.status)}</td>
      <td style="white-space:nowrap">
        <button class="btn btn-sm btn-outline" onclick="editPegawai(${p.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deletePegawai(${p.id},'${p.nama.replace(/'/g,"\\'")}')">Hapus</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="7" style="text-align:center;padding:2rem;color:#9eadc8">Tidak ada data</td></tr>';
}

async function loadSatkerOptions() {
  const res = await api('satker','list');
  if (!res?.success) return;
  const opts = res.data.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
  document.getElementById('peg-satker').innerHTML = '<option value="">Pilih Satker</option>' + opts;
}

function openModalPegawai() {
  document.getElementById('modal-pegawai-title').textContent = 'Tambah Data Pegawai Baru';
  document.getElementById('pegawai-id').value = '';
  ['peg-nip','peg-nama','peg-jabatan','peg-tmt'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('peg-golongan').value = '';
  document.getElementById('peg-satker').value = '';
  document.getElementById('peg-status').value = 'Aktif';
  loadSatkerOptions();
  openModal('modal-pegawai');
}

async function editPegawai(id) {
  const res = await api('pegawai','get',{id});
  if (!res?.success) { toast('Gagal memuat data','error'); return; }
  const p = res.data;
  document.getElementById('modal-pegawai-title').textContent = 'Edit Data Pegawai';
  document.getElementById('pegawai-id').value   = p.id;
  document.getElementById('peg-nip').value      = p.nip;
  document.getElementById('peg-nama').value     = p.nama;
  document.getElementById('peg-jabatan').value  = p.jabatan || '';
  document.getElementById('peg-golongan').value = p.golongan || '';
  document.getElementById('peg-tmt').value      = p.tmt_pns || '';
  document.getElementById('peg-status').value   = p.status;
  await loadSatkerOptions();
  document.getElementById('peg-satker').value   = p.satker_id || '';
  openModal('modal-pegawai');
}

async function savePegawai() {
  const body = {
    id: document.getElementById('pegawai-id').value,
    nip: document.getElementById('peg-nip').value,
    nama: document.getElementById('peg-nama').value,
    jabatan: document.getElementById('peg-jabatan').value,
    golongan: document.getElementById('peg-golongan').value,
    satker_id: document.getElementById('peg-satker').value,
    tmt_pns: document.getElementById('peg-tmt').value,
    status: document.getElementById('peg-status').value,
  };
  const res = await apiPost('pegawai','save',body);
  if (res?.success) {
    closeModal('modal-pegawai');
    toast(res.message);
    loadPegawai(document.getElementById('pegawai-search').value);
  } else {
    toast(res?.message || 'Gagal menyimpan','error');
  }
}

async function deletePegawai(id, nama) {
  if (!confirm(`Hapus pegawai "${nama}"? Data terkait juga akan ikut terhapus.`)) return;
  const res = await api('pegawai','delete',{id});
  if (res?.success) { toast(res.message); loadPegawai(); }
  else toast(res?.message || 'Gagal menghapus','error');
}

// ═══════════════ KENAIKAN GAJI BERKALA ══════════════
async function loadKGB(q='') {
  q = q || document.getElementById('kgb-search')?.value || '';
  const bulan = document.getElementById('kgb-bulan')?.value || '';
  const tahun = document.getElementById('kgb-tahun')?.value || '';
  document.getElementById('kgb-tbody').innerHTML = '<tr><td colspan="8" class="loading">Memuat...</td></tr>';
  const res = await api('kgb','list',{q,bulan,tahun});
  if (!res?.success) return;
  document.getElementById('kgb-tbody').innerHTML = res.data.map(k => `
    <tr>
      <td>${k.nama}</td><td>${k.nip}</td><td>${k.gol_lama}</td><td>${k.gol_baru}</td>
      <td>${k.tmt}</td><td>${k.no_sk||'-'}</td><td>${badge(k.status)}</td>
      <td style="white-space:nowrap">
        <button class="btn btn-sm btn-outline" onclick="editKGB(${JSON.stringify(k).replace(/"/g,'&quot;')})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteKGB(${k.id})">Hapus</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="8" style="text-align:center;padding:2rem;color:#9eadc8">Tidak ada data</td></tr>';
}

function editKGB(k) {
  document.getElementById('kgb-id').value    = k.id;
  document.getElementById('kgb-nip').value   = k.nip;
  document.getElementById('kgb-nama').value  = k.nama;
  document.getElementById('kgb-gol-lama').value = k.gol_lama;
  document.getElementById('kgb-gol-baru').value = k.gol_baru;
  document.getElementById('kgb-tmt').value   = k.tmt;
  document.getElementById('kgb-nosk').value  = k.no_sk || '';
}

function resetKGBForm() {
  ['kgb-id','kgb-nip','kgb-nama','kgb-tmt','kgb-nosk'].forEach(id => document.getElementById(id).value='');
  document.getElementById('kgb-gol-lama').value='';
  document.getElementById('kgb-gol-baru').value='';
}

// Auto-isi nama berdasarkan NIP
document.addEventListener('DOMContentLoaded', () => {
  const nipEl = document.getElementById('kgb-nip');
  if (nipEl) {
    nipEl.addEventListener('blur', async () => {
      const nip = nipEl.value.trim();
      if (nip.length < 10) return;
      const res = await api('pegawai','list',{q:nip});
      const p = res?.data?.find(x => x.nip === nip);
      if (p) document.getElementById('kgb-nama').value = p.nama;
    });
  }
});

async function saveKGB() {
  const body = {
    id: document.getElementById('kgb-id').value,
    nip: document.getElementById('kgb-nip').value,
    gol_lama: document.getElementById('kgb-gol-lama').value,
    gol_baru: document.getElementById('kgb-gol-baru').value,
    tmt: document.getElementById('kgb-tmt').value,
    no_sk: document.getElementById('kgb-nosk').value,
  };
  const res = await apiPost('kgb','save',body);
  if (res?.success) { toast(res.message); resetKGBForm(); loadKGB(); }
  else toast(res?.message||'Gagal menyimpan','error');
}

async function deleteKGB(id) {
  if (!confirm('Hapus data KGB ini?')) return;
  const res = await api('kgb','delete',{id});
  if (res?.success) { toast(res.message); loadKGB(); }
  else toast(res?.message||'Gagal','error');
}

// ═══════════════ TUNJANGAN ═══════════════════════════
async function loadTunjangan(q='') {
  document.getElementById('tunj-tbody').innerHTML='<tr><td colspan="7" class="loading">Memuat...</td></tr>';
  const res = await api('tunjangan','list',{q});
  if (!res?.success) return;
  document.getElementById('tunj-tbody').innerHTML = res.data.map(t=>`
    <tr>
      <td>${t.nama}</td><td>${t.nip}</td><td>${t.jenis_tunjangan}</td>
      <td>${t.nominal_fmt}</td><td>${t.periode||'-'}</td><td>${badge(t.status)}</td>
      <td style="white-space:nowrap">
        <button class="btn btn-sm btn-outline" onclick="editTunjangan(${JSON.stringify(t).replace(/"/g,'&quot;')})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteTunjangan(${t.id})">Hapus</button>
      </td>
    </tr>
  `).join('')||'<tr><td colspan="7" style="text-align:center;padding:2rem;color:#9eadc8">Tidak ada data</td></tr>';
}

function openModalTunjangan() {
  document.getElementById('tunj-id').value='';
  ['tunj-nip','tunj-nominal','tunj-periode'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('tunj-jenis').value='Tunjangan Kinerja';
  document.getElementById('tunj-status').value='Aktif';
  openModal('modal-tunjangan');
}

function editTunjangan(t) {
  document.getElementById('tunj-id').value      = t.id;
  document.getElementById('tunj-nip').value     = t.nip;
  document.getElementById('tunj-jenis').value   = t.jenis_tunjangan;
  document.getElementById('tunj-nominal').value = t.nominal;
  document.getElementById('tunj-periode').value = t.periode||'';
  document.getElementById('tunj-status').value  = t.status;
  openModal('modal-tunjangan');
}

async function saveTunjangan() {
  const body = {
    id: document.getElementById('tunj-id').value,
    nip: document.getElementById('tunj-nip').value,
    jenis_tunjangan: document.getElementById('tunj-jenis').value,
    nominal: document.getElementById('tunj-nominal').value,
    periode: document.getElementById('tunj-periode').value,
    status: document.getElementById('tunj-status').value,
  };
  const res = await apiPost('tunjangan','save',body);
  if (res?.success) { closeModal('modal-tunjangan'); toast(res.message); loadTunjangan(); }
  else toast(res?.message||'Gagal','error');
}

async function deleteTunjangan(id) {
  if(!confirm('Hapus data tunjangan ini?')) return;
  const res = await api('tunjangan','delete',{id});
  if(res?.success){toast(res.message);loadTunjangan();}
  else toast(res?.message||'Gagal','error');
}

// ═══════════════ SLKS ═════════════════════════════════
async function loadSLKS(q='') {
  document.getElementById('slks-tbody').innerHTML='<tr><td colspan="8" class="loading">Memuat...</td></tr>';
  const res = await api('slks','list',{q});
  if(!res?.success) return;
  document.getElementById('slks-tbody').innerHTML = res.data.map(s=>`
    <tr>
      <td>${s.nama}</td><td>${s.nip}</td><td>${s.periode}</td>
      <td>${s.nilai_skp}</td><td>${s.perilaku}</td><td>${s.total}</td>
      <td>${badge(s.keterangan)}</td>
      <td style="white-space:nowrap">
        <button class="btn btn-sm btn-outline" onclick="editSLKS(${JSON.stringify(s).replace(/"/g,'&quot;')})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteSLKS(${s.id})">Hapus</button>
      </td>
    </tr>
  `).join('')||'<tr><td colspan="8" style="text-align:center;padding:2rem;color:#9eadc8">Tidak ada data</td></tr>';
}

function openModalSLKS() {
  document.getElementById('slks-id').value='';
  document.getElementById('slks-nip').value='';
  document.getElementById('slks-skp').value='';
  document.getElementById('slks-perilaku').value='';
  openModal('modal-slks');
}

function editSLKS(s) {
  document.getElementById('slks-id').value      = s.id;
  document.getElementById('slks-nip').value     = s.nip;
  document.getElementById('slks-periode').value = s.periode;
  document.getElementById('slks-skp').value     = s.nilai_skp;
  document.getElementById('slks-perilaku').value= s.perilaku;
  openModal('modal-slks');
}

async function saveSLKS() {
  const body = {
    id: document.getElementById('slks-id').value,
    nip: document.getElementById('slks-nip').value,
    periode: document.getElementById('slks-periode').value,
    nilai_skp: document.getElementById('slks-skp').value,
    perilaku: document.getElementById('slks-perilaku').value,
  };
  const res = await apiPost('slks','save',body);
  if(res?.success){closeModal('modal-slks');toast(res.message);loadSLKS();}
  else toast(res?.message||'Gagal','error');
}

async function deleteSLKS(id) {
  if(!confirm('Hapus data SLKS ini?')) return;
  const res = await api('slks','delete',{id});
  if(res?.success){toast(res.message);loadSLKS();}
  else toast(res?.message||'Gagal','error');
}

// ═══════════════ ARSIP ════════════════════════════════
async function loadArsip(q='') {
  document.getElementById('arsip-list').innerHTML='<div class="loading">Memuat arsip...</div>';
  const res = await api('arsip','list',{q});
  if(!res?.success) return;
  if(!res.data.length){document.getElementById('arsip-list').innerHTML='<div class="loading">Belum ada file tersimpan</div>';return;}
  const iconSvg = `<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`;
  document.getElementById('arsip-list').innerHTML = res.data.map(f=>`
    <div class="file-item">
      <div class="file-icon">${iconSvg}</div>
      <div class="file-info">
        <div class="file-name">${f.nama_file}</div>
        <div class="file-meta">${f.tipe_file||'File'} • ${f.ukuran_kb} KB • Diupload ${new Date(f.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})}</div>
      </div>
      <a class="file-action" href="${f.path_file}" download="${f.nama_file}">Unduh</a>
      <button class="btn btn-sm btn-danger" onclick="deleteArsip(${f.id},'${f.nama_file.replace(/'/g,"\\'")}')">Hapus</button>
    </div>
  `).join('');
}

async function uploadFile(input) {
  if(!input.files.length) return;
  const fd = new FormData();
  fd.append('file', input.files[0]);
  toast('Mengupload file...','success');
  const r = await fetch(`${API}?module=arsip&action=upload`,{
    method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd
  });
  const res = await r.json();
  if(res?.success){toast(res.message);loadArsip();}
  else toast(res?.message||'Upload gagal','error');
  input.value='';
}

async function deleteArsip(id,nama) {
  if(!confirm(`Hapus file "${nama}"?`)) return;
  const res = await api('arsip','delete',{id});
  if(res?.success){toast(res.message);loadArsip();}
  else toast(res?.message||'Gagal','error');
}

// ═══════════════ SEARCH ═══════════════════════════════
async function handleSearch(val) {
  const box = document.getElementById('search-results');
  if(!val||val.length<2){box.style.display='none';return;}
  const res = await api('pegawai','list',{q:val});
  if(!res?.data?.length){box.style.display='none';return;}
  box.innerHTML = res.data.slice(0,6).map(p=>`
    <div onclick="showPage('data-pegawai')"
         style="padding:10px 18px;cursor:pointer;border-bottom:1px solid #f0f2f6;transition:background .15s"
         onmouseenter="this.style.background='#e8f2fb'" onmouseleave="this.style.background='white'">
      <div style="font-size:14px;font-weight:600;color:#1a2e5a">${p.nama}</div>
      <div style="font-size:12px;color:#9eadc8">${p.nip} — ${p.jabatan||'-'} | ${p.satker||'-'}</div>
    </div>
  `).join('');
  box.style.display='block';
}
document.addEventListener('click', e => {
  if(!e.target.closest('.search-box')&&!e.target.closest('#search-results'))
    document.getElementById('search-results').style.display='none';
});

// ═══════════════ MODAL ════════════════════════════════
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalOutside(e, id) { if(e.target===document.getElementById(id)) closeModal(id); }
</script>
</body>
</html>
<?php
// ── Helper PHP ─────────────────────────────────────────────
function golonganOptions(): string {
    $gol = ['I/a','I/b','I/c','I/d','II/a','II/b','II/c','II/d',
            'III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d'];
    $opts = '<option value="">Pilih golongan</option>';
    foreach ($gol as $g) $opts .= "<option value=\"$g\">$g</option>";
    return $opts;
}
