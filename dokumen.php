<?php
require_once 'includes/config.php';
requireLogin();
$user = currentUser();
$db   = getDB();

// Ambil daftar pegawai untuk dropdown
$pegawaiList = $db->query("SELECT id, nip, nama FROM pegawai ORDER BY nama")->fetchAll();

$jenisDokumen = ['SK CPNS','SK PNS','SK Kenaikan Pangkat','SK Jabatan','Ijazah','KTP','KK','NPWP','Sertifikat','Dokumen Lain'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dokumen Pegawai — SIPATEN</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--navy:#1a2e5a;--navy-dark:#0f1d3a;--blue:#1e6fbf;--blue-light:#3a8fd8;--blue-pale:#e8f2fb;--white:#fff;--gray-100:#f4f6fb;--gray-200:#e8edf5;--gray-400:#9eadc8;--gray-600:#5a6a8a;--shadow:0 2px 12px rgba(30,60,120,.10);--radius:12px}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-100);color:var(--navy);min-height:100vh;display:flex;flex-direction:column}
  header{background:linear-gradient(135deg,var(--navy-dark) 0%,#243570 60%,var(--blue) 100%);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 16px rgba(0,0,0,.25)}
  .logo{display:flex;align-items:center;gap:12px;text-decoration:none}
  .logo-emblem{width:42px;height:42px;background:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .logo-emblem img{width:42px;height:42px;object-fit:contain;border-radius:50%}
  .brand{font-size:20px;font-weight:700;color:#fff;letter-spacing:1px}
  .tagline{font-size:10px;color:rgba(255,255,255,.65)}
  .btn-back{padding:6px 14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;color:#fff;font-size:12px;font-weight:600;text-decoration:none}
  .btn-back:hover{background:rgba(255,255,255,.25)}
  main{flex:1;padding:2rem;max-width:1100px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;color:var(--navy);margin-bottom:1.5rem;display:flex;align-items:center;gap:10px}
  .layout{display:grid;grid-template-columns:320px 1fr;gap:1.5rem}
  .card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1rem}
  .card-title{font-size:14px;font-weight:700;color:var(--navy);margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--gray-200)}
  .form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:.875rem}
  label{font-size:12px;font-weight:600;color:var(--gray-600)}
  select,input[type=text],input[type=date]{padding:9px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;width:100%;transition:border-color .2s;background:#fff}
  select:focus,input:focus{border-color:var(--blue-light)}
  .btn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s}
  .btn-primary{background:var(--blue);color:#fff;width:100%}
  .btn-primary:hover{background:var(--navy)}
  .btn-danger{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0;padding:5px 10px;font-size:12px}
  .upload-area{border:2px dashed var(--gray-200);border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;transition:all .2s;margin-bottom:.875rem}
  .upload-area:hover{border-color:var(--blue-light);background:var(--blue-pale)}
  .upload-area input{display:none}
  .upload-icon{font-size:32px;margin-bottom:8px}
  .upload-text{font-size:13px;color:var(--gray-600)}
  .upload-text span{color:var(--blue);font-weight:600}
  .filter-bar{display:flex;gap:10px;margin-bottom:1rem;flex-wrap:wrap}
  .filter-bar select,.filter-bar input{flex:1;min-width:150px;padding:8px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none}
  .filter-bar select:focus,.filter-bar input:focus{border-color:var(--blue-light)}
  .doc-list{display:flex;flex-direction:column;gap:10px}
  .doc-item{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:1rem 1.25rem;display:flex;align-items:center;gap:14px;box-shadow:var(--shadow)}
  .doc-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
  .doc-icon.pdf{background:#fce8e8}
  .doc-icon.img{background:#e8f2fb}
  .doc-icon.doc{background:#e8f5e9}
  .doc-icon.other{background:#fef3dd}
  .doc-info{flex:1;min-width:0}
  .doc-name{font-size:14px;font-weight:600;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .doc-meta{font-size:12px;color:var(--gray-400);margin-top:3px}
  .badge-jenis{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;background:var(--blue-pale);color:var(--blue);margin-left:6px}
  .doc-actions{display:flex;gap:6px;flex-shrink:0}
  .btn-dl{padding:5px 12px;background:var(--blue-pale);border:none;border-radius:6px;font-size:12px;font-weight:600;color:var(--blue);cursor:pointer;text-decoration:none}
  .btn-dl:hover{background:#d0e7f7}
  .loading{text-align:center;padding:2rem;color:var(--gray-400);font-size:14px}
  .toast{position:fixed;bottom:24px;right:24px;background:#1a2e5a;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.2);z-index:999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none}
  .toast.show{opacity:1;transform:translateY(0)}
  .toast.success{background:#1a7a38}
  .toast.error{background:#9b2222}
  .empty{text-align:center;padding:3rem;color:var(--gray-400)}
  .empty-icon{font-size:48px;margin-bottom:1rem}
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
  <div style="display:flex;gap:8px">
    <a href="profile.php" class="btn-back">👤 Profil</a>
    <a href="index.php" class="btn-back">← Dashboard</a>
  </div>
</header>

<main>
  <div class="page-title">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    Arsip Dokumen Pegawai
  </div>

  <div class="layout">
    <!-- Panel Upload -->
    <?php if (canEdit()): ?>
    <div>
      <div class="card">
        <div class="card-title">📤 Upload Dokumen</div>
        <div class="form-group">
          <label>Pilih Pegawai</label>
          <select id="up-pegawai">
            <option value="">-- Pilih Pegawai --</option>
            <?php foreach ($pegawaiList as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?> (<?= $p['nip'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Jenis Dokumen</label>
          <select id="up-jenis">
            <option value="">-- Pilih Jenis --</option>
            <?php foreach ($jenisDokumen as $j): ?>
            <option value="<?= $j ?>"><?= $j ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Keterangan (opsional)</label>
          <input type="text" id="up-ket" placeholder="Contoh: Ijazah S1 Universitas Riau">
        </div>
        <div class="form-group">
          <label>Masa Berlaku (opsional)</label>
          <input type="date" id="up-tgl">
        </div>
        <div class="upload-area" onclick="document.getElementById('up-file').click()">
          <input type="file" id="up-file" accept=".pdf,.jpg,.jpeg,.png,.docx,.doc" onchange="previewFile(this)">
          <div class="upload-icon">📎</div>
          <div class="upload-text" id="up-label">Klik untuk pilih file<br><span>PDF, JPG, PNG, DOCX</span><br><small style="color:var(--gray-400)">Maks. 10MB</small></div>
        </div>
        <button class="btn btn-primary" onclick="uploadDokumen()">Upload Dokumen</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Panel Daftar Dokumen -->
    <div>
      <div class="filter-bar">
        <input type="text" id="filter-nama" placeholder="🔍 Cari nama pegawai..." oninput="loadDokumen()">
        <select id="filter-jenis" onchange="loadDokumen()">
          <option value="">Semua Jenis</option>
          <?php foreach ($jenisDokumen as $j): ?>
          <option value="<?= $j ?>"><?= $j ?></option>
          <?php endforeach; ?>
        </select>
        <select id="filter-pegawai" onchange="loadDokumen()">
          <option value="">Semua Pegawai</option>
          <?php foreach ($pegawaiList as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="doc-list" id="doc-list">
        <div class="loading">Memuat dokumen...</div>
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

function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3000);
}

function previewFile(input) {
  if (input.files.length) {
    document.getElementById('up-label').innerHTML = `<span>${input.files[0].name}</span><br><small style="color:var(--gray-400)">${(input.files[0].size/1024).toFixed(0)} KB</small>`;
  }
}

async function uploadDokumen() {
  const pid   = document.getElementById('up-pegawai').value;
  const jenis = document.getElementById('up-jenis').value;
  const ket   = document.getElementById('up-ket').value;
  const tgl   = document.getElementById('up-tgl').value;
  const file  = document.getElementById('up-file').files[0];

  if (!pid)   { toast('Pilih pegawai dulu', 'error'); return; }
  if (!jenis) { toast('Pilih jenis dokumen', 'error'); return; }
  if (!file)  { toast('Pilih file dulu', 'error'); return; }

  const fd = new FormData();
  fd.append('file', file);
  fd.append('pegawai_id', pid);
  fd.append('jenis_dokumen', jenis);
  fd.append('keterangan', ket);
  fd.append('tgl_berlaku', tgl);

  toast('Mengupload...', 'success');
  const r = await fetch(`${API}?module=dokumen&action=upload`, {
    method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: fd
  });
  const res = await r.json();
  if (res.success) {
    toast(res.message);
    document.getElementById('up-file').value = '';
    document.getElementById('up-label').innerHTML = 'Klik untuk pilih file<br><span>PDF, JPG, PNG, DOCX</span><br><small style="color:var(--gray-400)">Maks. 10MB</small>';
    document.getElementById('up-ket').value = '';
    document.getElementById('up-tgl').value = '';
    loadDokumen();
  } else {
    toast(res.message, 'error');
  }
}

async function loadDokumen() {
  const q     = document.getElementById('filter-nama')?.value || '';
  const jenis = document.getElementById('filter-jenis')?.value || '';
  const pid   = document.getElementById('filter-pegawai')?.value || '';
  let url = `${API}?module=dokumen&action=list&q=${encodeURIComponent(q)}`;
  if (jenis) url += `&jenis=${encodeURIComponent(jenis)}`;
  if (pid)   url += `&pegawai_id=${pid}`;

  const r = await fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}});
  const res = await r.json();
  const list = document.getElementById('doc-list');

  if (!res.success || !res.data.length) {
    list.innerHTML = '<div class="empty"><div class="empty-icon">📂</div><div>Belum ada dokumen tersimpan</div></div>';
    return;
  }

  const iconMap = {pdf:'📄',jpg:'🖼️',jpeg:'🖼️',png:'🖼️',docx:'📝',doc:'📝'};
  const clsMap  = {pdf:'pdf',jpg:'img',jpeg:'img',png:'img',docx:'doc',doc:'doc'};

  list.innerHTML = res.data.map(d => {
    const ext = d.nama_file.split('.').pop().toLowerCase();
    const icon= iconMap[ext] || '📎';
    const cls = clsMap[ext] || 'other';
    const tgl = d.created_at ? new Date(d.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}) : '-';
    const berlaku = d.tgl_berlaku ? `• Berlaku s/d: ${new Date(d.tgl_berlaku).toLocaleDateString('id-ID')}` : '';
    return `
      <div class="doc-item">
        <div class="doc-icon ${cls}">${icon}</div>
        <div class="doc-info">
          <div class="doc-name">${d.nama_file}<span class="badge-jenis">${d.jenis_dokumen}</span></div>
          <div class="doc-meta">👤 ${d.nama_pegawai} • ${tgl} ${berlaku}</div>
          ${d.keterangan ? `<div class="doc-meta" style="color:var(--navy)">${d.keterangan}</div>` : ''}
        </div>
        <div class="doc-actions">
          <a class="btn-dl" href="${d.path_file}" download="${d.nama_file}">⬇ Unduh</a>
          ${<?= canEdit() ? 'true' : 'false' ?> ? `<button class="btn btn-danger" onclick="hapusDokumen(${d.id})">🗑</button>` : ''}
        </div>
      </div>`;
  }).join('');
}

async function hapusDokumen(id) {
  if (!confirm('Hapus dokumen ini?')) return;
  const r = await fetch(`${API}?module=dokumen&action=delete&id=${id}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
  const res = await r.json();
  if (res.success) { toast(res.message); loadDokumen(); }
  else toast(res.message, 'error');
}

loadDokumen();
</script>
</body>
</html>
