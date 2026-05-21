<?php
require_once 'includes/config.php';
requireLogin();
requireAdmin();
$db = getDB();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Aktivitas — SIPATEN</title>
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
  main{flex:1;padding:2rem;max-width:1000px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px}
  .filter-bar{display:flex;gap:10px;margin-bottom:1rem;flex-wrap:wrap}
  .filter-bar input,.filter-bar select{flex:1;min-width:150px;padding:9px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none;background:#fff}
  .filter-bar input:focus,.filter-bar select:focus{border-color:#3a8fd8}
  .card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden}
  table{width:100%;border-collapse:collapse}
  th{background:var(--gray-100);padding:10px 14px;font-size:12px;font-weight:600;color:var(--gray-600);text-align:left;text-transform:uppercase}
  td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--gray-100)}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--blue-pale)}
  .chip{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600}
  .chip-tambah{background:#e2f5ea;color:#1a7a38}
  .chip-update{background:#e8f2fb;color:#1a2e5a}
  .chip-hapus{background:#fce8e8;color:#9b2222}
  .chip-upload{background:#fef3dd;color:#8a5500}
  .chip-login{background:#f0e8fb;color:#6a1a9b}
  .loading{text-align:center;padding:2rem;color:var(--gray-400)}
  .ip{font-size:11px;color:var(--gray-400);font-family:monospace}
  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;display:flex;justify-content:space-between}
  footer span{font-size:12px;color:var(--gray-400)}
  @media(max-width:640px){main{padding:1rem}}
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
  <div class="page-title">📋 Riwayat Aktivitas</div>

  <div class="filter-bar">
    <input type="text" id="f-q" placeholder="🔍 Cari nama / aksi / detail..." oninput="loadLog()">
    <select id="f-modul" onchange="loadLog()">
      <option value="">Semua Modul</option>
      <option>Pegawai</option>
      <option>Dokumen</option>
      <option>KGB</option>
      <option>Tunjangan</option>
      <option>SLKS</option>
      <option>Arsip</option>
      <option>Pengingat</option>
    </select>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Waktu</th>
          <th>User</th>
          <th>Aksi</th>
          <th>Modul</th>
          <th>Detail</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody id="log-tbody">
        <tr><td colspan="6" class="loading">Memuat log...</td></tr>
      </tbody>
    </table>
  </div>
</main>

<footer>
  <span>Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau</span>
  <span style="color:var(--blue);font-weight:600">v2.0</span>
</footer>

<script>
const API = 'api/index.php';
const chipMap = {
  'Tambah':'chip-tambah','tambah':'chip-tambah',
  'Update':'chip-update','update':'chip-update','Edit':'chip-update',
  'Hapus':'chip-hapus','hapus':'chip-hapus','Delete':'chip-hapus',
  'Upload':'chip-upload','upload':'chip-upload',
  'Login':'chip-login','Simpan':'chip-update'
};

async function loadLog() {
  const q = document.getElementById('f-q').value;
  const modul = document.getElementById('f-modul').value;
  const r = await fetch(`${API}?module=log&action=list&q=${encodeURIComponent(q)}&modul=${encodeURIComponent(modul)}`, {
    headers:{'X-Requested-With':'XMLHttpRequest'}
  });
  const res = await r.json();
  const tbody = document.getElementById('log-tbody');
  if (!res.success || !res.data.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#9eadc8">Belum ada aktivitas tercatat</td></tr>';
    return;
  }
  tbody.innerHTML = res.data.map(l => {
    const tgl = new Date(l.created_at).toLocaleString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
    const chipCls = chipMap[l.aksi] || 'chip-update';
    return `<tr>
      <td style="white-space:nowrap;font-size:12px">${tgl}</td>
      <td><strong>${l.user_nama || '-'}</strong></td>
      <td><span class="chip ${chipCls}">${l.aksi}</span></td>
      <td>${l.modul}</td>
      <td style="max-width:250px;word-break:break-word;font-size:12px">${l.detail || '-'}</td>
      <td class="ip">${l.ip_address || '-'}</td>
    </tr>`;
  }).join('');
}

loadLog();
setInterval(loadLog, 30000); // auto refresh 30 detik
</script>
</body>
</html>
