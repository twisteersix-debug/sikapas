<?php
// ============================================================
//  SIPATEN — Halaman Profil Pegawai
//  File: profil_pegawai.php
// ============================================================
require_once 'includes/config.php';
requireLogin();
$user = currentUser();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Pegawai — SIPATEN</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy: #1a2e5a; --navy-dark: #0f1d3a; --navy-mid: #243570;
    --blue: #1e6fbf; --blue-light: #3a8fd8; --blue-pale: #e8f2fb;
    --white: #ffffff; --gray-100: #f4f6fb; --gray-200: #e8edf5;
    --gray-400: #9eadc8; --gray-600: #5a6a8a; --text: #1a2e5a;
    --shadow: 0 2px 12px rgba(30,60,120,0.10); --radius: 12px;
  }
  body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--gray-100); color:var(--text); min-height:100vh; display:flex; flex-direction:column; }

  /* ── Header ── */
  header { background:linear-gradient(135deg,var(--navy-dark) 0%,var(--navy-mid) 60%,var(--blue) 100%); padding:0 2rem; height:64px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 2px 16px rgba(0,0,0,0.25); }
  .logo { display:flex; align-items:center; gap:12px; }
  .logo-emblem { width:42px; height:42px; background:var(--white); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
  .brand { font-size:22px; font-weight:700; color:var(--white); letter-spacing:1px; }
  .tagline { font-size:10px; color:rgba(255,255,255,0.65); }
  .user-badge { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:32px; padding:6px 14px 6px 8px; text-decoration:none; }
  .user-avatar { width:32px; height:32px; border-radius:50%; background:#e87d2a; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; color:#fff; }
  .user-name { font-size:13px; font-weight:500; color:#fff; }

  /* ── Layout ── */
  main { flex:1; padding:2rem; max-width:900px; margin:0 auto; width:100%; }
  .back-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--blue); cursor:pointer; margin-bottom:1.25rem; padding:6px 0; background:none; border:none; font-family:inherit; text-decoration:none; }
  .back-btn:hover { color:var(--navy); }
  .back-btn svg { width:16px; height:16px; }

  /* ── Hero Card ── */
  .hero-card {
    background:linear-gradient(135deg,var(--navy-dark) 0%,var(--navy-mid) 60%,var(--blue) 100%);
    border-radius:var(--radius); padding:2rem; margin-bottom:1.5rem;
    display:flex; align-items:center; gap:1.5rem;
    box-shadow:0 4px 20px rgba(30,60,120,0.2); position:relative; overflow:hidden;
  }
  .hero-card::before { content:''; position:absolute; top:-40px; right:-40px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,0.05); }
  .hero-avatar { width:80px; height:80px; border-radius:50%; background:#e87d2a; display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:700; color:#fff; flex-shrink:0; border:3px solid rgba(255,255,255,0.3); }
  .hero-info { flex:1; }
  .hero-nama { font-size:22px; font-weight:700; color:#fff; margin-bottom:4px; }
  .hero-jabatan { font-size:14px; color:rgba(255,255,255,0.75); margin-bottom:8px; }
  .hero-badges { display:flex; gap:8px; flex-wrap:wrap; }
  .hero-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.25); }
  .hero-badge.aktif { background:rgba(26,122,56,0.4); border-color:rgba(26,122,56,0.6); }
  .hero-badge.pensiun { background:rgba(155,34,34,0.4); border-color:rgba(155,34,34,0.6); }
  .hero-actions { display:flex; flex-direction:column; gap:8px; }

  /* ── Cards Grid ── */
  .cards-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.5rem; }
  .info-card { background:var(--white); border-radius:var(--radius); border:1px solid var(--gray-200); box-shadow:var(--shadow); padding:1.5rem; }
  .info-card.full { grid-column:1/-1; }
  .card-title { font-size:12px; font-weight:700; color:var(--gray-600); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:1rem; display:flex; align-items:center; gap:8px; }
  .card-title svg { width:16px; height:16px; fill:none; stroke:var(--blue); stroke-width:2; }

  .info-row { display:flex; flex-direction:column; gap:2px; margin-bottom:1rem; }
  .info-row:last-child { margin-bottom:0; }
  .info-label { font-size:11px; font-weight:600; color:var(--gray-400); text-transform:uppercase; letter-spacing:0.4px; }
  .info-value { font-size:14px; font-weight:500; color:var(--navy); }
  .info-value.empty { color:var(--gray-400); font-style:italic; font-weight:400; }

  /* ── Buttons ── */
  .btn { padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; transition:all .18s; border:none; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
  .btn-primary { background:var(--blue); color:#fff; }
  .btn-primary:hover { background:var(--navy); }
  .btn-outline { background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.3); }
  .btn-outline:hover { background:rgba(255,255,255,0.25); }
  .btn svg { width:14px; height:14px; fill:none; stroke:currentColor; stroke-width:2; }

  /* ── Badge Status ── */
  .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
  .badge-active { background:#e2f5ea; color:#1a7a38; }
  .badge-inactive { background:#fce8e8; color:#9b2222; }
  .badge-pending { background:#fef3dd; color:#8a5500; }

  /* ── Loading / Error ── */
  .loading { text-align:center; padding:3rem; color:var(--gray-400); font-size:14px; }
  .error-box { background:#fce8e8; border:1px solid #f5c0c0; border-radius:var(--radius); padding:1.5rem; text-align:center; color:#9b2222; }

  /* ── Footer ── */
  footer { background:var(--white); border-top:1px solid var(--gray-200); padding:14px 2rem; display:flex; justify-content:space-between; align-items:center; }
  footer span { font-size:12px; color:var(--gray-400); }
  .version { font-size:12px; font-weight:600; color:var(--blue); }

  /* ── Toast ── */
  .toast { position:fixed; bottom:24px; right:24px; background:#1a2e5a; color:#fff; padding:12px 20px; border-radius:10px; font-size:14px; font-weight:500; box-shadow:0 4px 20px rgba(0,0,0,0.2); z-index:999; opacity:0; transform:translateY(10px); transition:all .3s; pointer-events:none; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#1a7a38; }
  .toast.error { background:#9b2222; }

  @media(max-width:640px) {
    .cards-grid { grid-template-columns:1fr; }
    .hero-card { flex-direction:column; text-align:center; }
    .hero-badges { justify-content:center; }
    main { padding:1rem; }
  }
</style>
</head>
<body>

<!-- HEADER -->
<header>
  <div class="logo">
    <div class="logo-emblem">
      <img src="logo.png" alt="SIPATEN" style="width:42px;height:42px;object-fit:contain;border-radius:50%;">
    </div>
    <div>
      <div class="brand">SIPATEN</div>
      <div class="tagline">Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman</div>
    </div>
  </div>
  <a href="profile.php" class="user-badge">
    <div class="user-avatar"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>
    <span class="user-name"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></span>
  </a>
</header>

<!-- MAIN -->
<main>
  <a href="index.php" class="back-btn" onclick="history.back();return false;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M19 12H5M12 5l-7 7 7 7"/>
    </svg>
    Kembali
  </a>

  <!-- Konten dimuat via JS -->
  <div id="profil-content">
    <div class="loading">⏳ Memuat data pegawai...</div>
  </div>
</main>

<div class="toast" id="toast"></div>

<footer>
  <span>Copyright © 2026 SIPATEN. Kementerian Imigrasi &amp; Pemasyarakatan.</span>
  <span class="version">✦ Versi 1.0</span>
</footer>

<script>
const API  = 'api/index.php';
const PEG_ID = <?= $id ?>;

// ── Toast ────────────────────────────────────────────
function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3000);
}

// ── Badge helper ──────────────────────────────────────
function badgeStatus(s) {
  const map = { 'Aktif':'badge-active', 'Pensiun':'badge-inactive', 'Dalam Proses':'badge-pending' };
  return `<span class="badge ${map[s]||'badge-pending'}">${s}</span>`;
}

// ── Nilai atau tanda strip ─────────────────────────────
function val(v, fmt) {
  if (!v || v === '' || v === null) return '<span class="info-value empty">—</span>';
  if (fmt === 'date') {
    const d = new Date(v);
    return `<span class="info-value">${d.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})}</span>`;
  }
  return `<span class="info-value">${v}</span>`;
}

// ── Inisial dari nama ──────────────────────────────────
function inisial(nama) {
  return (nama || 'P').split(' ').slice(0,2).map(w => w[0]).join('').toUpperCase();
}

// ── Load data pegawai ─────────────────────────────────
async function loadProfil() {
  try {
    const url = new URL(API, location.href);
    url.searchParams.set('module','pegawai');
    url.searchParams.set('action','get');
    url.searchParams.set('id', PEG_ID);
    const r   = await fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} });
    const res = await r.json();

    if (!res?.success || !res.data) {
      document.getElementById('profil-content').innerHTML =
        `<div class="error-box">❌ Data pegawai tidak ditemukan.</div>`;
      return;
    }

    renderProfil(res.data);
  } catch(e) {
    document.getElementById('profil-content').innerHTML =
      `<div class="error-box">❌ Gagal memuat data. Silakan coba lagi.</div>`;
  }
}

// ── Render halaman profil ──────────────────────────────
function renderProfil(p) {
  const statusCls = p.status === 'Aktif' ? 'aktif' : p.status === 'Pensiun' ? 'pensiun' : '';

  document.getElementById('profil-content').innerHTML = `

    <!-- Hero Card -->
    <div class="hero-card">
      <div class="hero-avatar">${inisial(p.nama)}</div>
      <div class="hero-info">
        <div class="hero-nama">${p.nama}</div>
        <div class="hero-jabatan">${p.jabatan || 'Jabatan belum diisi'}</div>
        <div class="hero-badges">
          <span class="hero-badge">${p.nip}</span>
          ${p.golongan ? `<span class="hero-badge">Gol. ${p.golongan}</span>` : ''}
          ${p.pangkat  ? `<span class="hero-badge">${p.pangkat}</span>`  : ''}
          <span class="hero-badge ${statusCls}">${p.status || 'Aktif'}</span>
        </div>
      </div>
      ${<?= isAdmin() ? 'true' : 'false' ?> ? `
      <div class="hero-actions">
        <button class="btn btn-outline" onclick="editPegawaiRedirect(${p.id})">
          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Data
        </button>
      </div>` : ''}
    </div>

    <!-- Grid Info -->
    <div class="cards-grid">

      <!-- Data Kepegawaian -->
      <div class="info-card">
        <div class="card-title">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Data Kepegawaian
        </div>
        <div class="info-row">
          <div class="info-label">NIP</div>
          ${val(p.nip)}
        </div>
        <div class="info-row">
          <div class="info-label">Jabatan</div>
          ${val(p.jabatan)}
        </div>
        <div class="info-row">
          <div class="info-label">Pangkat</div>
          ${val(p.pangkat)}
        </div>
        <div class="info-row">
          <div class="info-label">Golongan / Ruang</div>
          ${val(p.golongan)}
        </div>
        <div class="info-row">
          <div class="info-label">Status Pegawai</div>
          <div>${badgeStatus(p.status || 'Aktif')}</div>
        </div>
        <div class="info-row">
          <div class="info-label">TMT PNS</div>
          ${val(p.tmt_pns, 'date')}
        </div>
      </div>

      <!-- Unit & Kontak -->
      <div class="info-card">
        <div class="card-title">
          <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Unit Kerja & Kontak
        </div>
        <div class="info-row">
          <div class="info-label">Satuan Kerja</div>
          ${val(p.satker || p.satker_nama)}
        </div>
        <div class="info-row">
          <div class="info-label">Unit Kerja</div>
          ${val(p.unit_kerja)}
        </div>
        <div class="info-row">
          <div class="info-label">Nomor Telepon</div>
          ${p.no_telepon ? `<span class="info-value"><a href="tel:${p.no_telepon}" style="color:var(--blue);text-decoration:none">${p.no_telepon}</a></span>` : val(null)}
        </div>
        <div class="info-row">
          <div class="info-label">Email</div>
          ${p.email ? `<span class="info-value"><a href="mailto:${p.email}" style="color:var(--blue);text-decoration:none">${p.email}</a></span>` : val(null)}
        </div>
        <div class="info-row">
          <div class="info-label">Tanggal Lahir</div>
          ${val(p.tanggal_lahir, 'date')}
        </div>
      </div>

      <!-- Alamat -->
      <div class="info-card full">
        <div class="card-title">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Alamat Lengkap
        </div>
        <div class="info-row">
          ${p.alamat
            ? `<span class="info-value" style="line-height:1.6">${p.alamat}</span>`
            : `<span class="info-value empty">Alamat belum diisi</span>`}
        </div>
      </div>

    </div><!-- /.cards-grid -->
  `;
}

// ── Redirect ke index dan buka modal edit ────────────
function editPegawaiRedirect(id) {
  window.location.href = `index.php?edit_pegawai=${id}`;
}

// ── Init ──────────────────────────────────────────────
loadProfil();
</script>
</body>
</html>
