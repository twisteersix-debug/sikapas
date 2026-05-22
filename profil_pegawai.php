<?php
// ============================================================
//  SIPATEN — Halaman Profil Pegawai (dengan Edit + Upload Foto)
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
  *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
  :root {
    --navy:#1a2e5a; --navy-dark:#0f1d3a; --navy-mid:#243570;
    --blue:#1e6fbf; --blue-light:#3a8fd8; --blue-pale:#e8f2fb;
    --white:#ffffff; --gray-100:#f4f6fb; --gray-200:#e8edf5;
    --gray-400:#9eadc8; --gray-600:#5a6a8a; --text:#1a2e5a;
    --shadow:0 2px 12px rgba(30,60,120,0.10); --radius:12px;
  }
  body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--gray-100); color:var(--text); min-height:100vh; display:flex; flex-direction:column; }

  /* Header */
  header { background:linear-gradient(135deg,var(--navy-dark) 0%,var(--navy-mid) 60%,var(--blue) 100%); padding:0 2rem; height:64px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 2px 16px rgba(0,0,0,0.25); }
  .logo { display:flex; align-items:center; gap:12px; }
  .brand { font-size:22px; font-weight:700; color:#fff; letter-spacing:1px; }
  .tagline { font-size:10px; color:rgba(255,255,255,0.65); }
  .user-badge { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:32px; padding:6px 14px 6px 8px; text-decoration:none; }
  .user-avatar-sm { width:32px; height:32px; border-radius:50%; background:#e87d2a; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; color:#fff; }
  .user-name { font-size:13px; font-weight:500; color:#fff; }

  /* Layout */
  main { flex:1; padding:2rem; max-width:900px; margin:0 auto; width:100%; }
  .back-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--blue); cursor:pointer; margin-bottom:1.25rem; padding:6px 0; background:none; border:none; font-family:inherit; text-decoration:none; }
  .back-btn:hover { color:var(--navy); }
  .back-btn svg { width:16px; height:16px; }

  /* Hero Card */
  .hero-card { background:linear-gradient(135deg,var(--navy-dark) 0%,var(--navy-mid) 60%,var(--blue) 100%); border-radius:var(--radius); padding:2rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:1.5rem; box-shadow:0 4px 20px rgba(30,60,120,0.2); position:relative; overflow:hidden; }
  .hero-card::before { content:''; position:absolute; top:-40px; right:-40px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,0.05); }

  /* Foto Avatar */
  .avatar-wrap { position:relative; flex-shrink:0; cursor:pointer; }
  .hero-avatar { width:90px; height:90px; border-radius:50%; background:#e87d2a; display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:700; color:#fff; border:3px solid rgba(255,255,255,0.35); overflow:hidden; object-fit:cover; }
  .avatar-wrap img.hero-avatar { object-fit:cover; }
  .avatar-overlay { position:absolute; inset:0; border-radius:50%; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity .2s; }
  .avatar-wrap:hover .avatar-overlay { opacity:1; }
  .avatar-overlay svg { width:22px; height:22px; fill:none; stroke:#fff; stroke-width:2; }
  .avatar-hint { font-size:10px; color:rgba(255,255,255,0.55); margin-top:5px; text-align:center; }
  #foto-input { display:none; }

  .hero-info { flex:1; }
  .hero-nama { font-size:22px; font-weight:700; color:#fff; margin-bottom:4px; }
  .hero-jabatan { font-size:14px; color:rgba(255,255,255,0.75); margin-bottom:10px; }
  .hero-badges { display:flex; gap:8px; flex-wrap:wrap; }
  .hero-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.25); }
  .hero-badge.aktif { background:rgba(26,122,56,0.4); border-color:rgba(26,122,56,0.6); }
  .hero-badge.pensiun { background:rgba(155,34,34,0.4); border-color:rgba(155,34,34,0.6); }
  .hero-actions { display:flex; flex-direction:column; gap:8px; flex-shrink:0; }

  /* Buttons */
  .btn { padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; transition:all .18s; border:none; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
  .btn-primary { background:var(--blue); color:#fff; }
  .btn-primary:hover { background:var(--navy); }
  .btn-outline-white { background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.35); }
  .btn-outline-white:hover { background:rgba(255,255,255,0.28); }
  .btn-outline { background:var(--white); color:var(--blue); border:1.5px solid var(--blue); }
  .btn-outline:hover { background:var(--blue-pale); }
  .btn-danger { background:#fce8e8; color:#9b2222; border:1px solid #f5c0c0; }
  .btn-danger:hover { background:#f5c0c0; }
  .btn svg { width:14px; height:14px; fill:none; stroke:currentColor; stroke-width:2; }

  /* Cards Grid */
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

  /* Badge */
  .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
  .badge-active { background:#e2f5ea; color:#1a7a38; }
  .badge-inactive { background:#fce8e8; color:#9b2222; }
  .badge-pending { background:#fef3dd; color:#8a5500; }

  /* Modal */
  .modal-overlay { position:fixed; inset:0; background:rgba(10,20,50,0.55); display:none; align-items:center; justify-content:center; z-index:200; }
  .modal-overlay.open { display:flex; }
  .modal { background:var(--white); border-radius:16px; padding:2rem; width:100%; max-width:620px; max-height:92vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
  .modal-title { font-size:18px; font-weight:700; color:var(--navy); margin-bottom:1.25rem; }
  .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
  .form-group { display:flex; flex-direction:column; gap:6px; }
  .form-group.full { grid-column:1/-1; }
  label { font-size:13px; font-weight:600; color:var(--gray-600); }
  input[type=text],input[type=date],select,textarea { padding:10px 14px; border:1px solid var(--gray-200); border-radius:8px; font-size:13px; font-family:inherit; color:var(--text); outline:none; transition:border-color .2s; background:#fff; width:100%; }
  input:focus,select:focus,textarea:focus { border-color:var(--blue-light); }
  select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:32px; }
  .form-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:1.25rem; }

  /* Toast */
  .toast { position:fixed; bottom:24px; right:24px; background:#1a2e5a; color:#fff; padding:12px 20px; border-radius:10px; font-size:14px; font-weight:500; box-shadow:0 4px 20px rgba(0,0,0,0.2); z-index:999; opacity:0; transform:translateY(10px); transition:all .3s; pointer-events:none; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#1a7a38; }
  .toast.error { background:#9b2222; }

  /* Loading */
  .loading { text-align:center; padding:3rem; color:var(--gray-400); font-size:14px; }
  .error-box { background:#fce8e8; border:1px solid #f5c0c0; border-radius:var(--radius); padding:1.5rem; text-align:center; color:#9b2222; }

  footer { background:var(--white); border-top:1px solid var(--gray-200); padding:14px 2rem; display:flex; justify-content:space-between; align-items:center; }
  footer span { font-size:12px; color:var(--gray-400); }
  .version { font-size:12px; font-weight:600; color:var(--blue); }

  @media(max-width:640px) {
    .cards-grid { grid-template-columns:1fr; }
    .hero-card { flex-direction:column; text-align:center; }
    .hero-badges { justify-content:center; }
    .hero-actions { flex-direction:row; }
    main { padding:1rem; }
    .form-grid { grid-template-columns:1fr; }
  }
</style>
</head>
<body>

<header>
  <div class="logo">
    <div style="width:42px;height:42px;background:#fff;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center">
      <img src="logo.png" alt="SIPATEN" style="width:42px;height:42px;object-fit:contain;border-radius:50%">
    </div>
    <div>
      <div class="brand">SIPATEN</div>
      <div class="tagline">Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman</div>
    </div>
  </div>
  <a href="profile.php" class="user-badge">
    <div class="user-avatar-sm"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>
    <span class="user-name"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></span>
  </a>
</header>

<main>
  <a href="javascript:history.back()" class="back-btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali
  </a>
  <div id="profil-content"><div class="loading">⏳ Memuat data pegawai...</div></div>
</main>

<!-- MODAL EDIT PEGAWAI -->
<div class="modal-overlay" id="modal-edit" onclick="closeModalOutside(event,'modal-edit')">
  <div class="modal">
    <p class="modal-title">✏️ Edit Data Pegawai</p>
    <input type="hidden" id="edit-id">
    <div class="form-grid">
      <div class="form-group"><label>NIP <span style="color:#e74c3c">*</span></label><input type="text" id="edit-nip" placeholder="18 digit NIP"></div>
      <div class="form-group"><label>Nama Lengkap <span style="color:#e74c3c">*</span></label><input type="text" id="edit-nama" placeholder="Nama lengkap beserta gelar"></div>
      <div class="form-group"><label>Jabatan</label><input type="text" id="edit-jabatan" placeholder="Jabatan struktural/fungsional"></div>
      <div class="form-group"><label>Pangkat</label><input type="text" id="edit-pangkat" placeholder="Contoh: Penata Muda Tk. I"></div>
      <div class="form-group"><label>Golongan / Ruang</label>
        <select id="edit-golongan">
          <option value="">Pilih golongan</option>
          <?php
          $gol = ['I/a','I/b','I/c','I/d','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d'];
          foreach ($gol as $g) echo "<option value=\"$g\">$g</option>";
          ?>
        </select>
      </div>
      <div class="form-group"><label>Status Pegawai</label>
        <select id="edit-status">
          <option value="Aktif">Aktif</option>
          <option value="Pensiun">Pensiun</option>
          <option value="Dalam Proses">Dalam Proses</option>
        </select>
      </div>
      <div class="form-group"><label>Satuan Kerja</label>
        <select id="edit-satker"><option value="">Pilih Satker</option></select>
      </div>
      <div class="form-group"><label>Unit Kerja</label><input type="text" id="edit-unitkerja" placeholder="Unit / Seksi / Bidang"></div>
      <div class="form-group"><label>TMT PNS</label><input type="date" id="edit-tmt"></div>
      <div class="form-group"><label>Tanggal Lahir</label><input type="date" id="edit-tgllahir"></div>
      <div class="form-group"><label>Nomor Telepon</label><input type="text" id="edit-telp" placeholder="08xxxxxxxxxx"></div>
      <div class="form-group"><label>Email</label><input type="text" id="edit-email" placeholder="email@domain.com"></div>
      <div class="form-group full"><label>Alamat Lengkap</label>
        <textarea id="edit-alamat" rows="3" placeholder="Jl. ... No. ..., Kelurahan, Kecamatan, Kota" style="resize:vertical"></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-outline" onclick="closeModal('modal-edit')">Batal</button>
      <button class="btn btn-primary" onclick="simpanEdit()">💾 Simpan Perubahan</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<footer>
  <span>Copyright © 2026 SIPATEN. Kementerian Imigrasi &amp; Pemasyarakatan.</span>
  <span class="version">✦ Versi 1.0</span>
</footer>

<!-- Input file foto (hidden) -->
<input type="file" id="foto-input" accept="image/jpeg,image/png,image/webp" onchange="uploadFoto(this)">

<script>
const API    = 'api/index.php';
const PEG_ID = <?= $id ?>;
const IS_ADMIN = <?= isAdmin() ? 'true' : 'false' ?>;

let pegawaiData = null;

// ── Toast ──────────────────────────────────────────────
function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3200);
}

// ── Modal ──────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalOutside(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }

// ── Helpers ────────────────────────────────────────────
function val(v, fmt) {
  if (!v || v === '') return '<span class="info-value empty">—</span>';
  if (fmt === 'date') {
    const d = new Date(v);
    return `<span class="info-value">${d.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})}</span>`;
  }
  return `<span class="info-value">${v}</span>`;
}
function badgeStatus(s) {
  const map = { 'Aktif':'badge-active', 'Pensiun':'badge-inactive', 'Dalam Proses':'badge-pending' };
  return `<span class="badge ${map[s]||'badge-pending'}">${s}</span>`;
}
function inisial(nama) {
  return (nama||'P').split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase();
}

// ── Load Profil ────────────────────────────────────────
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
        '<div class="error-box">❌ Data pegawai tidak ditemukan.</div>';
      return;
    }
    pegawaiData = res.data;
    renderProfil(res.data);
  } catch(e) {
    document.getElementById('profil-content').innerHTML =
      '<div class="error-box">❌ Gagal memuat data. Silakan coba lagi.</div>';
  }
}

// ── Render Profil ──────────────────────────────────────
function renderProfil(p) {
  const statusCls = p.status === 'Aktif' ? 'aktif' : p.status === 'Pensiun' ? 'pensiun' : '';
  const fotoSrc   = p.foto ? p.foto : null;
  const avatarEl  = fotoSrc
    ? `<img src="${fotoSrc}" class="hero-avatar" alt="${p.nama}" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.35)">`
    : `<div class="hero-avatar" style="font-size:32px;font-weight:700">${inisial(p.nama)}</div>`;

  const editBtn = IS_ADMIN ? `
    <button class="btn btn-outline-white" onclick="bukaModalEdit()">
      <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Data
    </button>` : '';

  document.getElementById('profil-content').innerHTML = `
    <!-- Hero -->
    <div class="hero-card">
      <div class="avatar-wrap" onclick="${IS_ADMIN ? 'document.getElementById(\'foto-input\').click()' : ''}">
        ${avatarEl}
        ${IS_ADMIN ? `
        <div class="avatar-overlay">
          <svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
        </div>
        <div class="avatar-hint">Klik untuk ganti foto</div>` : ''}
      </div>
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
      <div class="hero-actions">${editBtn}</div>
    </div>

    <!-- Grid Info -->
    <div class="cards-grid">
      <div class="info-card">
        <div class="card-title">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Data Kepegawaian
        </div>
        <div class="info-row"><div class="info-label">NIP</div>${val(p.nip)}</div>
        <div class="info-row"><div class="info-label">Jabatan</div>${val(p.jabatan)}</div>
        <div class="info-row"><div class="info-label">Pangkat</div>${val(p.pangkat)}</div>
        <div class="info-row"><div class="info-label">Golongan / Ruang</div>${val(p.golongan)}</div>
        <div class="info-row"><div class="info-label">Status Pegawai</div><div>${badgeStatus(p.status||'Aktif')}</div></div>
        <div class="info-row"><div class="info-label">TMT PNS</div>${val(p.tmt_pns,'date')}</div>
      </div>

      <div class="info-card">
        <div class="card-title">
          <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Unit Kerja & Kontak
        </div>
        <div class="info-row"><div class="info-label">Satuan Kerja</div>${val(p.satker||p.satker_nama)}</div>
        <div class="info-row"><div class="info-label">Unit Kerja</div>${val(p.unit_kerja)}</div>
        <div class="info-row"><div class="info-label">Nomor Telepon</div>
          ${p.no_telepon ? `<span class="info-value"><a href="tel:${p.no_telepon}" style="color:var(--blue);text-decoration:none">${p.no_telepon}</a></span>` : val(null)}
        </div>
        <div class="info-row"><div class="info-label">Email</div>
          ${p.email ? `<span class="info-value"><a href="mailto:${p.email}" style="color:var(--blue);text-decoration:none">${p.email}</a></span>` : val(null)}
        </div>
        <div class="info-row"><div class="info-label">Tanggal Lahir</div>${val(p.tanggal_lahir,'date')}</div>
      </div>

      <div class="info-card full">
        <div class="card-title">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Alamat Lengkap
        </div>
        ${p.alamat
          ? `<span class="info-value" style="line-height:1.6">${p.alamat}</span>`
          : `<span class="info-value empty">Alamat belum diisi</span>`}
      </div>
    </div>
  `;
}

// ── Buka Modal Edit ────────────────────────────────────
async function bukaModalEdit() {
  if (!pegawaiData) return;
  const p = pegawaiData;

  // Isi field
  document.getElementById('edit-id').value        = p.id;
  document.getElementById('edit-nip').value        = p.nip        || '';
  document.getElementById('edit-nama').value       = p.nama       || '';
  document.getElementById('edit-jabatan').value    = p.jabatan    || '';
  document.getElementById('edit-pangkat').value    = p.pangkat    || '';
  document.getElementById('edit-golongan').value   = p.golongan   || '';
  document.getElementById('edit-status').value     = p.status     || 'Aktif';
  document.getElementById('edit-unitkerja').value  = p.unit_kerja || '';
  document.getElementById('edit-tmt').value        = p.tmt_pns    || '';
  document.getElementById('edit-tgllahir').value   = p.tanggal_lahir || '';
  document.getElementById('edit-telp').value       = p.no_telepon || '';
  document.getElementById('edit-email').value      = p.email      || '';
  document.getElementById('edit-alamat').value     = p.alamat     || '';

  // Load satker options
  const resSatker = await fetch(`${API}?module=satker&action=list`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
  const dataSatker = await resSatker.json();
  if (dataSatker?.success) {
    document.getElementById('edit-satker').innerHTML =
      '<option value="">Pilih Satker</option>' +
      dataSatker.data.map(s => `<option value="${s.id}" ${s.id == p.satker_id ? 'selected' : ''}>${s.nama}</option>`).join('');
  }

  openModal('modal-edit');
}

// ── Simpan Edit ────────────────────────────────────────
async function simpanEdit() {
  const nip  = document.getElementById('edit-nip').value.trim();
  const nama = document.getElementById('edit-nama').value.trim();
  if (!nip)  { toast('NIP wajib diisi','error'); return; }
  if (!nama) { toast('Nama wajib diisi','error'); return; }

  const body = {
    id:            document.getElementById('edit-id').value,
    nip,
    nama,
    jabatan:       document.getElementById('edit-jabatan').value.trim(),
    pangkat:       document.getElementById('edit-pangkat').value.trim(),
    golongan:      document.getElementById('edit-golongan').value,
    status:        document.getElementById('edit-status').value,
    satker_id:     document.getElementById('edit-satker').value,
    unit_kerja:    document.getElementById('edit-unitkerja').value.trim(),
    tmt_pns:       document.getElementById('edit-tmt').value,
    tanggal_lahir: document.getElementById('edit-tgllahir').value,
    no_telepon:    document.getElementById('edit-telp').value.trim(),
    email:         document.getElementById('edit-email').value.trim(),
    alamat:        document.getElementById('edit-alamat').value.trim(),
  };

  const r   = await fetch(`${API}?module=pegawai&action=save`, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify(body)
  });
  const res = await r.json();

  if (res?.success) {
    closeModal('modal-edit');
    toast(res.message || 'Data berhasil disimpan');
    await loadProfil(); // refresh tampilan
  } else {
    toast(res?.message || 'Gagal menyimpan','error');
  }
}

// ── Upload Foto ────────────────────────────────────────
async function uploadFoto(input) {
  if (!input.files.length) return;
  const file = input.files[0];

  // Validasi ukuran (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast('Ukuran foto maksimal 2MB','error');
    input.value = '';
    return;
  }

  toast('Mengupload foto...','success');

  const fd = new FormData();
  fd.append('foto', file);
  fd.append('pegawai_id', PEG_ID);

  const r   = await fetch(`${API}?module=pegawai&action=upload_foto`, {
    method:'POST',
    headers:{'X-Requested-With':'XMLHttpRequest'},
    body: fd
  });
  const res = await r.json();

  if (res?.success) {
    toast('Foto berhasil diupload');
    await loadProfil();
  } else {
    toast(res?.message || 'Gagal upload foto','error');
  }
  input.value = '';
}

// ── Init ───────────────────────────────────────────────
loadProfil();
</script>
</body>
</html>
