<?php
require_once 'includes/config.php';
requireLogin();
$db = getDB();
$pegawaiList = $db->query("SELECT id, nip, nama, tmt_pns FROM pegawai WHERE status='Aktif' ORDER BY nama")->fetchAll();
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
  main{flex:1;padding:2rem;max-width:960px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px}
  .layout{display:grid;grid-template-columns:300px 1fr;gap:1.5rem}
  .card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem}
  .card-title{font-size:14px;font-weight:700;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--gray-200)}
  .form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:.875rem}
  label{font-size:12px;font-weight:600;color:var(--gray-600)}
  select,input{padding:9px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;width:100%;transition:border-color .2s;background:#fff}
  select:focus,input:focus{border-color:#3a8fd8}
  .btn-primary{background:var(--blue);color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;width:100%}
  .btn-primary:hover{background:var(--navy)}
  table{width:100%;border-collapse:collapse}
  th{background:var(--gray-100);padding:10px 14px;font-size:12px;font-weight:600;color:var(--gray-600);text-align:left;text-transform:uppercase}
  td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--gray-100)}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--blue-pale)}
  .chip{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
  .chip-red{background:#fce8e8;color:#9b2222}
  .chip-yellow{background:#fef3dd;color:#8a5500}
  .chip-green{background:#e2f5ea;color:#1a7a38}
  .loading{text-align:center;padding:2rem;color:var(--gray-400)}
  .toast{position:fixed;bottom:24px;right:24px;background:#1a2e5a;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.2);z-index:999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none}
  .toast.show{opacity:1;transform:translateY(0)}
  .toast.success{background:#1a7a38}
  .toast.error{background:#9b2222}
  .btn-danger{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0;padding:4px 10px;font-size:12px;border-radius:6px;cursor:pointer;font-family:inherit}
  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;display:flex;justify-content:space-between}
  footer span{font-size:12px;color:var(--gray-400)}
  @media(max-width:768px){.layout{grid-template-columns:1fr}main{padding:1rem}}
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
  <div class="layout">
    <?php if (canEdit()): ?>
    <div>
      <div class="card">
        <div class="card-title">➕ Tambah Pengingat</div>
        <div class="form-group">
          <label>Pilih Pegawai</label>
          <select id="pg-pegawai" onchange="autoTglPensiun(this)">
            <option value="">-- Pilih Pegawai --</option>
            <?php foreach ($pegawaiList as $p): ?>
            <option value="<?= $p['id'] ?>" data-tmt="<?= $p['tmt_pns'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Tanggal Pensiun</label>
          <input type="date" id="pg-tgl">
        </div>
        <div class="form-group">
          <label>Catatan</label>
          <input type="text" id="pg-catatan" placeholder="Opsional...">
        </div>
        <button class="btn-primary" onclick="savePengingat()">Simpan Pengingat</button>
      </div>
    </div>
    <?php endif; ?>

    <div>
      <div class="card">
        <div class="card-title">📋 Daftar Pengingat Pensiun</div>
        <table>
          <thead><tr><th>Nama</th><th>NIP</th><th>Tgl Pensiun</th><th>Sisa</th><?php if(canEdit()):?><th>Aksi</th><?php endif;?></tr></thead>
          <tbody id="pg-tbody"><tr><td colspan="5" class="loading">Memuat...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<div class="toast" id="toast"></div>
<footer>
  <span>Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau</span>
  <span style="color:var(--blue);font-weight:600">v2.0</span>
</footer>

<script>
const API = 'api/index.php';
function toast(msg,type='success'){const el=document.getElementById('toast');el.textContent=msg;el.className=`toast ${type} show`;setTimeout(()=>el.classList.remove('show'),3000)}

function autoTglPensiun(sel) {
  const tmt = sel.options[sel.selectedIndex]?.dataset?.tmt;
  if (tmt) {
    const d = new Date(tmt);
    d.setFullYear(d.getFullYear() + 58); // BUP 58 tahun
    document.getElementById('pg-tgl').value = d.toISOString().split('T')[0];
  }
}

async function savePengingat() {
  const pid = document.getElementById('pg-pegawai').value;
  const tgl = document.getElementById('pg-tgl').value;
  const cat = document.getElementById('pg-catatan').value;
  if (!pid || !tgl) { toast('Pilih pegawai dan tanggal pensiun', 'error'); return; }
  const r = await fetch(`${API}?module=pengingat&action=save`, {
    method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({pegawai_id:pid, tgl_pensiun:tgl, catatan:cat})
  });
  const res = await r.json();
  if (res.success) { toast(res.message); loadPengingat(); }
  else toast(res.message, 'error');
}

async function loadPengingat() {
  const r = await fetch(`${API}?module=pengingat&action=list&q=`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
  const res = await r.json();
  const tbody = document.getElementById('pg-tbody');
  if (!res.success || !res.data.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#9eadc8">Belum ada pengingat</td></tr>';
    return;
  }
  tbody.innerHTML = res.data.map(p => {
    const hari = parseInt(p.sisa_hari);
    let chip = '';
    if (hari < 0)         chip = '<span class="chip chip-red">Sudah Pensiun</span>';
    else if (hari < 90)   chip = `<span class="chip chip-red">${hari} hari lagi</span>`;
    else if (hari < 365)  chip = `<span class="chip chip-yellow">${Math.ceil(hari/30)} bulan lagi</span>`;
    else                  chip = `<span class="chip chip-green">${Math.ceil(hari/365)} tahun lagi</span>`;
    const tgl = new Date(p.tgl_pensiun).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
    const canEdit = <?= canEdit() ? 'true' : 'false' ?>;
    return `<tr>
      <td><strong>${p.nama}</strong>${p.catatan?`<br><small style="color:#9eadc8">${p.catatan}</small>`:''}</td>
      <td>${p.nip}</td>
      <td>${tgl}</td>
      <td>${chip}</td>
      ${canEdit ? `<td><button class="btn-danger" onclick="hapusPengingat(${p.id})">🗑 Hapus</button></td>` : ''}
    </tr>`;
  }).join('');
}

async function hapusPengingat(id) {
  if (!confirm('Hapus pengingat ini?')) return;
  const r = await fetch(`${API}?module=pengingat&action=delete&id=${id}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
  const res = await r.json();
  if (res.success) { toast(res.message); loadPengingat(); }
  else toast(res.message, 'error');
}

loadPengingat();
</script>
</body>
</html>
