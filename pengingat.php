<?php
require_once 'includes/config.php';
requireLogin();
$db = getDB();

// Auto-sync: tambah pegawai yang belum ada di pengingat tapi punya tanggal lahir
$db->query("
    INSERT IGNORE INTO pengingat (pegawai_id, tgl_pensiun, catatan)
    SELECT id, DATE_ADD(tanggal_lahir, INTERVAL 58 YEAR), 'Auto dari data pegawai'
    FROM pegawai 
    WHERE tanggal_lahir IS NOT NULL 
    AND status='Aktif'
    AND id NOT IN (SELECT pegawai_id FROM pengingat)
");

// Hitung statistik
$total        = $db->query("SELECT COUNT(*) FROM pengingat pg JOIN pegawai p ON p.id=pg.pegawai_id")->fetchColumn();
$soon_90      = $db->query("SELECT COUNT(*) FROM pengingat WHERE DATEDIFF(tgl_pensiun, NOW()) BETWEEN 0 AND 90")->fetchColumn();
$soon_365     = $db->query("SELECT COUNT(*) FROM pengingat WHERE DATEDIFF(tgl_pensiun, NOW()) BETWEEN 91 AND 365")->fetchColumn();
$belum_data   = $db->query("SELECT COUNT(*) FROM pegawai WHERE tanggal_lahir IS NULL AND status='Aktif'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengingat Pensiun — SIPATEN</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--navy:#1a2e5a;--navy-dark:#0f1d3a;--blue:#1e6fbf;--blue-pale:#e8f2fb;--white:#fff;--gray-100:#f4f6fb;--gray-200:#e8edf5;--gray-400:#9eadc8;--gray-600:#5a6a8a;--shadow:0 2px 12px rgba(30,60,120,.10);--radius:12px}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-100);color:var(--navy);min-height:100vh;display:flex;flex-direction:column}
  header{background:linear-gradient(135deg,var(--navy-dark) 0%,#243570 60%,var(--blue) 100%);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 16px rgba(0,0,0,.25)}
  .logo{display:flex;align-items:center;gap:12px;text-decoration:none}
  .logo-emblem{width:42px;height:42px;background:#fff;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center}
  .logo-emblem img{width:42px;height:42px;object-fit:contain;border-radius:50%}
  .brand{font-size:20px;font-weight:700;color:#fff;letter-spacing:1px}
  .tagline{font-size:10px;color:rgba(255,255,255,.65)}
  .btn-back{padding:6px 14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;color:#fff;font-size:12px;font-weight:600;text-decoration:none}
  .btn-back:hover{background:rgba(255,255,255,.25)}
  main{flex:1;padding:2rem;max-width:1100px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;margin-bottom:.5rem;display:flex;align-items:center;gap:10px}
  .page-sub{font-size:13px;color:var(--gray-600);margin-bottom:1.5rem}
  /* Stats */
  .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}
  .stat-card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.25rem;text-align:center}
  .stat-num{font-size:28px;font-weight:700;margin-bottom:4px}
  .stat-label{font-size:12px;color:var(--gray-600)}
  .stat-red{color:#9b2222}
  .stat-yellow{color:#8a5500}
  .stat-green{color:#1a7a38}
  .stat-blue{color:var(--blue)}
  /* Alert */
  .alert-warning{background:#fef3dd;border:1px solid #f0d080;border-radius:10px;padding:12px 16px;font-size:13px;color:#8a5500;margin-bottom:1.25rem;display:flex;align-items:center;gap:10px}
  /* Filter */
  .filter-bar{display:flex;gap:10px;margin-bottom:1rem;flex-wrap:wrap;align-items:center}
  .filter-bar input,.filter-bar select{padding:9px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none;background:#fff;transition:border-color .2s}
  .filter-bar input{flex:1;min-width:200px}
  .filter-bar input:focus,.filter-bar select:focus{border-color:#3a8fd8}
  select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
  /* Table */
  .table-wrapper{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden}
  table{width:100%;border-collapse:collapse}
  th{background:var(--gray-100);padding:10px 14px;font-size:12px;font-weight:600;color:var(--gray-600);text-align:left;text-transform:uppercase}
  td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--gray-100);vertical-align:middle}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--blue-pale)}
  /* Badges */
  .chip{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap}
  .chip-red{background:#fce8e8;color:#9b2222}
  .chip-orange{background:#fff0e0;color:#c05000}
  .chip-yellow{background:#fef3dd;color:#8a5500}
  .chip-green{background:#e2f5ea;color:#1a7a38}
  .chip-gray{background:var(--gray-100);color:var(--gray-600)}
  /* Progress bar umur */
  .age-bar{width:100%;height:6px;background:var(--gray-200);border-radius:3px;overflow:hidden;margin-top:4px}
  .age-fill{height:100%;border-radius:3px;transition:width .3s}
  /* Pegawai tanpa data */
  .no-data-section{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.25rem;margin-top:1.5rem}
  .no-data-title{font-size:14px;font-weight:700;color:var(--navy);margin-bottom:.75rem;display:flex;align-items:center;gap:8px}
  .peg-chip{display:inline-block;padding:4px 10px;background:#fef3dd;border:1px solid #f0d080;border-radius:20px;font-size:12px;color:#8a5500;margin:3px}
  /* Loading */
  .loading{text-align:center;padding:2rem;color:var(--gray-400)}
  /* Toast */
  .toast{position:fixed;bottom:24px;right:24px;background:#1a2e5a;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.2);z-index:999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none}
  .toast.show{opacity:1;transform:translateY(0)}
  .toast.success{background:#1a7a38}
  .toast.error{background:#9b2222}
  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;display:flex;justify-content:space-between}
  footer span{font-size:12px;color:var(--gray-400)}
  @media(max-width:768px){.stats-row{grid-template-columns:repeat(2,1fr)}main{padding:1rem}}
</style>
</head>
<body>
<header>
  <a href="index.php" class="logo">
    <div class="logo-emblem"><img src="logo.png" alt="SIPATEN" onerror="this.parentElement.innerHTML='🏛️'"></div>
    <div><div class="brand">SIPATEN</div><div class="tagline">Sistem Penyimpanan Arsip Kepegawaian</div></div>
  </a>
  <a href="index.php" class="btn-back">← Dashboard</a>
</header>

<main>
  <div class="page-title">🔔 Pengingat Masa Pensiun</div>
  <div class="page-sub">Data otomatis dihitung dari tanggal lahir pegawai. Batas usia pensiun: <strong>58 tahun</strong>.</div>

  <!-- Statistik -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-num stat-blue"><?= $total ?></div>
      <div class="stat-label">Total Dipantau</div>
    </div>
    <div class="stat-card">
      <div class="stat-num stat-red"><?= $soon_90 ?></div>
      <div class="stat-label">Pensiun ≤ 3 Bulan</div>
    </div>
    <div class="stat-card">
      <div class="stat-num stat-yellow"><?= $soon_365 ?></div>
      <div class="stat-label">Pensiun ≤ 1 Tahun</div>
    </div>
    <div class="stat-card">
      <div class="stat-num stat-red"><?= $belum_data ?></div>
      <div class="stat-label">Tgl Lahir Belum Diisi</div>
    </div>
  </div>

  <?php if ($belum_data > 0): ?>
  <div class="alert-warning">
    ⚠️ Ada <strong><?= $belum_data ?> pegawai</strong> yang belum memiliki data tanggal lahir. Lengkapi di menu <a href="index.php" style="color:#8a5500;font-weight:700">Data Pegawai</a> agar pengingat pensiun bisa dihitung otomatis.
  </div>
  <?php endif; ?>

  <!-- Filter -->
  <div class="filter-bar">
    <input type="text" id="f-cari" placeholder="🔍 Cari nama atau NIP..." oninput="loadData()">
    <select id="f-status" onchange="loadData()">
      <option value="">Semua Status</option>
      <option value="sudah">Sudah Pensiun</option>
      <option value="segera">≤ 3 Bulan Lagi</option>
      <option value="dekat">≤ 1 Tahun Lagi</option>
      <option value="aman">Lebih dari 1 Tahun</option>
    </select>
    <?php if(canEdit()):?>
    <button onclick="syncData()" style="padding:9px 16px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">🔄 Sync Ulang</button>
    <?php endif;?>
  </div>

  <!-- Tabel -->
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Pegawai</th>
          <th>NIP</th>
          <th>Jabatan / Satker</th>
          <th>Gol.</th>
          <th>Umur Sekarang</th>
          <th>Tanggal Pensiun</th>
          <th>Status</th>
          <?php if(canEdit()):?><th>Aksi</th><?php endif;?>
        </tr>
      </thead>
      <tbody id="pg-tbody">
        <tr><td colspan="9" class="loading">Memuat data...</td></tr>
      </tbody>
    </table>
  </div>

  <!-- Pegawai tanpa tanggal lahir -->
  <?php
  $tanpaData = $db->query("SELECT nip, nama, jabatan FROM pegawai WHERE tanggal_lahir IS NULL AND status='Aktif' ORDER BY nama")->fetchAll();
  if ($tanpaData):
  ?>
  <div class="no-data-section">
    <div class="no-data-title">⚠️ Pegawai Belum Memiliki Data Tanggal Lahir (<?= count($tanpaData) ?>)</div>
    <div>
      <?php foreach($tanpaData as $p): ?>
      <span class="peg-chip" title="<?= htmlspecialchars($p['jabatan']??'') ?>"><?= htmlspecialchars($p['nama']) ?> (<?= $p['nip'] ?>)</span>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:.75rem;font-size:12px;color:var(--gray-600)">Lengkapi tanggal lahir di menu <strong>Data Pegawai → Edit</strong> agar pengingat bisa dihitung otomatis.</div>
  </div>
  <?php endif; ?>
</main>

<div class="toast" id="toast"></div>
<footer>
  <span>Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau</span>
  <span style="color:var(--blue);font-weight:600">v2.0</span>
</footer>

<script>
const API = 'api/index.php';
const canEdit = <?= canEdit() ? 'true' : 'false' ?>;

function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg; el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3000);
}

function getChip(hari) {
  const h = parseInt(hari);
  if (h < 0)        return `<span class="chip chip-gray">⬜ Sudah Pensiun</span>`;
  if (h <= 90)      return `<span class="chip chip-red">🔴 ${h} hari lagi</span>`;
  if (h <= 180)     return `<span class="chip chip-orange">🟠 ${Math.ceil(h/30)} bulan lagi</span>`;
  if (h <= 365)     return `<span class="chip chip-yellow">🟡 ${Math.ceil(h/30)} bulan lagi</span>`;
  const thn = Math.floor(h/365);
  const bln = Math.ceil((h % 365) / 30);
  return `<span class="chip chip-green">🟢 ${thn} thn ${bln} bln lagi</span>`;
}

function getAgeBar(umur) {
  const pct = Math.min((umur / 58) * 100, 100);
  let color = '#1a7a38';
  if (pct >= 95) color = '#9b2222';
  else if (pct >= 85) color = '#c05000';
  else if (pct >= 75) color = '#8a5500';
  return `<div style="font-size:13px;font-weight:600">${umur} tahun</div>
          <div class="age-bar"><div class="age-fill" style="width:${pct}%;background:${color}"></div></div>`;
}

function filterStatus(data, status) {
  if (!status) return data;
  return data.filter(d => {
    const h = parseInt(d.sisa_hari);
    if (status === 'sudah')  return h < 0;
    if (status === 'segera') return h >= 0 && h <= 90;
    if (status === 'dekat')  return h > 90 && h <= 365;
    if (status === 'aman')   return h > 365;
    return true;
  });
}

let allData = [];

async function loadData() {
  const q      = document.getElementById('f-cari').value;
  const status = document.getElementById('f-status').value;

  if (allData.length === 0 || q !== lastQ) {
    lastQ = q;
    const r = await fetch(`${API}?module=pengingat&action=list&q=${encodeURIComponent(q)}`, {
      headers: {'X-Requested-With':'XMLHttpRequest'}
    });
    const res = await r.json();
    allData = res.data || [];
  }

  const filtered = filterStatus(allData, status);
  renderTable(filtered);
}

let lastQ = '';

function renderTable(data) {
  const tbody = document.getElementById('pg-tbody');
  const cols  = canEdit ? 9 : 8;

  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;padding:2rem;color:#9eadc8">Tidak ada data</td></tr>`;
    return;
  }

  tbody.innerHTML = data.map((p, i) => {
    const tgl = new Date(p.tgl_pensiun).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
    const chip = getChip(p.sisa_hari);
    const ageBar = getAgeBar(p.umur_sekarang || '?');
    const hapusBtn = canEdit ? `<td><button onclick="hapus(${p.id},'${(p.nama||'').replace(/'/g,"\\'")}')" style="padding:4px 10px;background:#fce8e8;border:1px solid #f5c0c0;border-radius:6px;font-size:12px;color:#9b2222;cursor:pointer;font-family:inherit">🗑</button></td>` : '';
    return `<tr>
      <td style="color:var(--gray-400);font-size:12px">${i+1}</td>
      <td><strong>${p.nama}</strong></td>
      <td style="font-size:12px;font-family:monospace">${p.nip}</td>
      <td><div>${p.jabatan||'-'}</div><div style="font-size:11px;color:var(--gray-400)">${p.satker||'-'}</div></td>
      <td>${p.golongan||'-'}</td>
      <td>${ageBar}</td>
      <td style="white-space:nowrap">${tgl}${p.catatan&&p.catatan!=='Auto dari data pegawai'?`<div style="font-size:11px;color:var(--gray-400)">${p.catatan}</div>`:''}</td>
      <td>${chip}</td>
      ${hapusBtn}
    </tr>`;
  }).join('');
}

async function syncData() {
  toast('Menyinkronkan data...', 'success');
  allData = [];
  lastQ = '';
  await loadData();
  toast('Data berhasil disinkronkan!', 'success');
}

async function hapus(id, nama) {
  if (!confirm(`Hapus pengingat untuk "${nama}"?\nData akan muncul kembali jika sync ulang dilakukan.`)) return;
  const r = await fetch(`${API}?module=pengingat&action=delete&id=${id}`, {
    headers: {'X-Requested-With':'XMLHttpRequest'}
  });
  const res = await r.json();
  if (res.success) {
    toast(res.message);
    allData = allData.filter(d => d.id !== id);
    renderTable(filterStatus(allData, document.getElementById('f-status').value));
  } else {
    toast(res.message, 'error');
  }
}

// Load saat halaman dibuka
loadData();
</script>
</body>
</html>
