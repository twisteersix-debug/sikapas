<?php
require_once 'includes/config.php';
requireLogin();
$db = getDB();

$jenisDokumen = [
    'Arsip Dokumen Pegawai',
    'SK CPNS',
    'SK PNS', 
    'SK Kenaikan Pangkat',
    'SK Jabatan',
    'Ijazah',
    'KTP',
    'KK',
    'NPWP',
    'Sertifikat',
    'Dokumen Lain'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Arsip File — SIPATEN</title>
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
  main{flex:1;padding:2rem;max-width:1000px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px}
  .layout{display:grid;grid-template-columns:300px 1fr;gap:1.5rem}
  .card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1rem}
  .card-title{font-size:14px;font-weight:700;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--gray-200)}
  .form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:.875rem}
  label{font-size:12px;font-weight:600;color:var(--gray-600)}
  select,input[type=text]{padding:9px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;width:100%;transition:border-color .2s;background:#fff}
  select:focus,input:focus{border-color:#3a8fd8}
  select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
  .upload-area{border:2px dashed var(--gray-200);border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;transition:all .2s;margin-bottom:.875rem}
  .upload-area:hover{border-color:#3a8fd8;background:var(--blue-pale)}
  .upload-area.dragover{border-color:#3a8fd8;background:var(--blue-pale)}
  .upload-icon{font-size:32px;margin-bottom:8px}
  .upload-text{font-size:13px;color:var(--gray-600)}
  .upload-text span{color:var(--blue);font-weight:600}
  .btn-primary{background:var(--blue);color:#fff;border:none;border-radius:8px;padding:10px 18px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;width:100%;transition:all .18s}
  .btn-primary:hover{background:var(--navy)}
  .filter-bar{display:flex;gap:10px;margin-bottom:1rem;flex-wrap:wrap}
  .filter-bar select,.filter-bar input{flex:1;min-width:150px;padding:8px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none;background:#fff}
  .filter-bar select:focus,.filter-bar input:focus{border-color:#3a8fd8}
  .filter-bar select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
  .file-list{display:flex;flex-direction:column;gap:10px}
  .file-item{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:1rem 1.25rem;display:flex;align-items:center;gap:14px;box-shadow:var(--shadow);transition:border-color .2s}
  .file-item:hover{border-color:#3a8fd8}
  .file-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
  .fi-pdf{background:#fce8e8}
  .fi-img{background:#e8f2fb}
  .fi-doc{background:#e8f5e9}
  .fi-xls{background:#e8f5e9}
  .fi-other{background:#fef3dd}
  .file-info{flex:1;min-width:0}
  .file-name{font-size:14px;font-weight:600;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .file-meta{font-size:12px;color:var(--gray-400);margin-top:3px;display:flex;gap:8px;flex-wrap:wrap}
  .jenis-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;background:var(--blue-pale);color:var(--blue)}
  .file-actions{display:flex;gap:6px;flex-shrink:0}
  .btn-dl{padding:5px 12px;background:var(--blue-pale);border:none;border-radius:6px;font-size:12px;font-weight:600;color:var(--blue);cursor:pointer;text-decoration:none;white-space:nowrap}
  .btn-dl:hover{background:#d0e7f7}
  .btn-del{padding:5px 10px;background:#fce8e8;border:1px solid #f5c0c0;border-radius:6px;font-size:12px;color:#9b2222;cursor:pointer;font-family:inherit}
  .btn-del:hover{background:#f5c0c0}
  .loading{text-align:center;padding:2rem;color:var(--gray-400)}
  .empty{text-align:center;padding:3rem;color:var(--gray-400)}
  .empty-icon{font-size:48px;margin-bottom:1rem}
  .toast{position:fixed;bottom:24px;right:24px;background:#1a2e5a;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.2);z-index:999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none}
  .toast.show{opacity:1;transform:translateY(0)}
  .toast.success{background:#1a7a38}
  .toast.error{background:#9b2222}
  .progress-bar{height:4px;background:var(--gray-200);border-radius:2px;margin-top:8px;overflow:hidden;display:none}
  .progress-fill{height:100%;background:var(--blue);width:0%;transition:width .3s;border-radius:2px}
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
  <div class="page-title">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
    Arsip File
  </div>

  <div class="layout">
    <!-- Panel Upload -->
    <?php if (canEdit()): ?>
    <div>
      <div class="card">
        <div class="card-title">📤 Upload File</div>
        <div class="form-group">
          <label>Kategori Dokumen</label>
          <select id="up-jenis">
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($jenisDokumen as $j): ?>
            <option value="<?= $j ?>"><?= $j ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Keterangan (opsional)</label>
          <input type="text" id="up-ket" placeholder="Tambahkan keterangan...">
        </div>
        <div class="upload-area" id="upload-area"
             onclick="document.getElementById('up-file').click()"
             ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="dropFile(event)">
          <input type="file" id="up-file" style="display:none"
                 accept=".pdf,.xlsx,.xls,.docx,.doc,.png,.jpg,.jpeg"
                 onchange="previewFile(this)">
          <div class="upload-icon">📁</div>
          <div class="upload-text" id="up-label">
            Klik atau drag file ke sini<br>
            <span>PDF, Excel, Word, JPG, PNG</span><br>
            <small style="color:var(--gray-400)">Maksimal 10MB</small>
          </div>
          <div class="progress-bar" id="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
          </div>
        </div>
        <button class="btn-primary" onclick="uploadFile()">⬆️ Upload File</button>
      </div>

      <!-- Statistik -->
      <div class="card" style="background:var(--blue-pale);border-color:#c5dff5">
        <div style="font-size:13px;color:var(--navy)">
          <strong>📊 Info Kategori</strong><br><br>
          <?php foreach ($jenisDokumen as $j): ?>
          <?php
            $cnt = $db->prepare("SELECT COUNT(*) FROM arsip WHERE jenis_dokumen=?");
            $cnt->execute([$j]);
            $c = $cnt->fetchColumn();
            if ($c > 0):
          ?>
          <div style="display:flex;justify-content:space-between;margin-bottom:4px">
            <span><?= $j ?></span>
            <strong><?= $c ?></strong>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Panel List -->
    <div>
      <div class="filter-bar">
        <input type="text" id="f-cari" placeholder="🔍 Cari nama file..." oninput="loadArsip()">
        <select id="f-jenis" onchange="loadArsip()">
          <option value="">Semua Kategori</option>
          <?php foreach ($jenisDokumen as $j): ?>
          <option value="<?= $j ?>"><?= $j ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="file-list" id="file-list">
        <div class="loading">Memuat arsip...</div>
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
const canEdit = <?= canEdit() ? 'true' : 'false' ?>;

function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3000);
}

function previewFile(input) {
  if (input.files.length) {
    const f = input.files[0];
    document.getElementById('up-label').innerHTML =
      `<span>${f.name}</span><br><small style="color:var(--gray-400)">${(f.size/1024).toFixed(0)} KB</small>`;
  }
}

function dragOver(e) { e.preventDefault(); document.getElementById('upload-area').classList.add('dragover'); }
function dragLeave(e) { document.getElementById('upload-area').classList.remove('dragover'); }
function dropFile(e) {
  e.preventDefault();
  dragLeave(e);
  const files = e.dataTransfer.files;
  if (files.length) {
    const input = document.getElementById('up-file');
    const dt = new DataTransfer();
    dt.items.add(files[0]);
    input.files = dt.files;
    previewFile(input);
  }
}

async function uploadFile() {
  const jenis = document.getElementById('up-jenis').value;
  const ket   = document.getElementById('up-ket').value;
  const file  = document.getElementById('up-file').files[0];

  if (!jenis) { toast('Pilih kategori dokumen dulu', 'error'); return; }
  if (!file)  { toast('Pilih file dulu', 'error'); return; }

  const fd = new FormData();
  fd.append('file', file);
  fd.append('jenis_dokumen', jenis);
  fd.append('keterangan', ket);

  // Show progress
  const bar = document.getElementById('progress-bar');
  const fill= document.getElementById('progress-fill');
  bar.style.display = 'block';
  fill.style.width  = '30%';
  toast('Mengupload file...', 'success');

  try {
    const r = await fetch(`${API}?module=arsip&action=upload`, {
      method: 'POST',
      headers: {'X-Requested-With': 'XMLHttpRequest'},
      body: fd
    });
    fill.style.width = '100%';
    const res = await r.json();
    setTimeout(() => { bar.style.display='none'; fill.style.width='0%'; }, 500);

    if (res.success) {
      toast(res.message);
      document.getElementById('up-file').value = '';
      document.getElementById('up-ket').value  = '';
      document.getElementById('up-jenis').value= '';
      document.getElementById('up-label').innerHTML = 'Klik atau drag file ke sini<br><span>PDF, Excel, Word, JPG, PNG</span><br><small style="color:var(--gray-400)">Maksimal 10MB</small>';
      loadArsip();
    } else {
      toast(res.message, 'error');
    }
  } catch(e) {
    toast('Gagal upload', 'error');
    bar.style.display = 'none';
  }
}

async function loadArsip() {
  const q     = document.getElementById('f-cari')?.value || '';
  const jenis = document.getElementById('f-jenis')?.value || '';
  let url = `${API}?module=arsip&action=list&q=${encodeURIComponent(q)}`;
  if (jenis) url += `&jenis=${encodeURIComponent(jenis)}`;

  const r = await fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}});
  const res = await r.json();
  const list = document.getElementById('file-list');

  if (!res.success || !res.data.length) {
    list.innerHTML = '<div class="empty"><div class="empty-icon">📂</div><div>Belum ada file tersimpan</div></div>';
    return;
  }

  const extIcon = {pdf:'📄',xlsx:'📊',xls:'📊',docx:'📝',doc:'📝',png:'🖼️',jpg:'🖼️',jpeg:'🖼️'};
  const extCls  = {pdf:'fi-pdf',xlsx:'fi-xls',xls:'fi-xls',docx:'fi-doc',doc:'fi-doc',png:'fi-img',jpg:'fi-img',jpeg:'fi-img'};

  list.innerHTML = res.data.map(f => {
    const ext  = f.nama_file.split('.').pop().toLowerCase();
    const icon = extIcon[ext] || '📎';
    const cls  = extCls[ext]  || 'fi-other';
    const tgl  = f.created_at ? new Date(f.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}) : '-';
    const jenisTag = f.jenis_dokumen ? `<span class="jenis-badge">${f.jenis_dokumen}</span>` : '';
    return `
      <div class="file-item">
        <div class="file-icon ${cls}">${icon}</div>
        <div class="file-info">
          <div class="file-name">${f.nama_file}</div>
          <div class="file-meta">
            ${jenisTag}
            <span>📅 ${tgl}</span>
            <span>💾 ${f.ukuran_kb} KB</span>
            ${f.keterangan ? `<span>📌 ${f.keterangan}</span>` : ''}
          </div>
        </div>
        <div class="file-actions">
          <a class="btn-dl" href="${f.path_file}" download="${f.nama_file}">⬇ Unduh</a>
          ${canEdit ? `<button class="btn-del" onclick="hapusFile(${f.id},'${f.nama_file.replace(/'/g,"\\'")}')">🗑</button>` : ''}
        </div>
      </div>`;
  }).join('');
}

async function hapusFile(id, nama) {
  if (!confirm(`Hapus file "${nama}"?`)) return;
  const r = await fetch(`${API}?module=arsip&action=delete&id=${id}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
  const res = await r.json();
  if (res.success) { toast(res.message); loadArsip(); }
  else toast(res.message, 'error');
}

loadArsip();
</script>
</body>
</html>
