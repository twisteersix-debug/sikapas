<?php
// ============================================================
//  SIKAPAS.RIAU — Data Pegawai Per Satker
//  File: pegawai_satker.php
// ============================================================
require_once 'includes/config.php';
requireLogin();
$user = currentUser();
$db   = getDB();

// Helper: satker_id user yang login (null = admin, akses semua)
function getUserSatkerId(): ?int {
    global $db;
    $uid = $_SESSION['user_id'];
    if (isAdmin()) return null;
    $s = $db->prepare("SELECT satker_id FROM users WHERE id=?");
    $s->execute([$uid]);
    $r = $s->fetch();
    return ($r && $r['satker_id']) ? (int)$r['satker_id'] : null;
}

$sid = getUserSatkerId();

// Ambil semua satker beserta jumlah pegawai
if ($sid !== null) {
    // Non-admin: hanya satker miliknya
    $satkers = $db->prepare("
        SELECT s.id, s.nama,
               COUNT(p.id)              AS total,
               SUM(p.status='Aktif')   AS aktif,
               SUM(p.status='Pensiun') AS pensiun
        FROM satker s
        LEFT JOIN pegawai p ON p.satker_id = s.id
        WHERE s.id = ?
        GROUP BY s.id, s.nama
        ORDER BY s.nama
    ");
    $satkers->execute([$sid]);
} else {
    $satkers = $db->query("
        SELECT s.id, s.nama,
               COUNT(p.id)              AS total,
               SUM(p.status='Aktif')   AS aktif,
               SUM(p.status='Pensiun') AS pensiun
        FROM satker s
        LEFT JOIN pegawai p ON p.satker_id = s.id
        GROUP BY s.id, s.nama
        ORDER BY s.nama
    ");
}
$satkers = $satkers->fetchAll();

// Total keseluruhan
$grandTotal  = array_sum(array_column($satkers, 'total'));
$grandAktif  = array_sum(array_column($satkers, 'aktif'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pegawai Per Satker — SIKAPAS.RIAU</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{
    --navy:#1a2e5a;--navy-dark:#0f1d3a;--navy-mid:#243570;
    --blue:#1e6fbf;--blue-light:#3a8fd8;--blue-pale:#e8f2fb;
    --white:#fff;--gray-100:#f4f6fb;--gray-200:#e8edf5;
    --gray-400:#9eadc8;--gray-600:#5a6a8a;
    --shadow:0 2px 12px rgba(30,60,120,.10);
    --shadow-hover:0 6px 24px rgba(30,60,120,.18);
    --radius:12px;
  }
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-100);color:var(--navy);min-height:100vh;display:flex;flex-direction:column}

  /* ── Header ── */
  header{background:linear-gradient(135deg,var(--navy-dark) 0%,var(--navy-mid) 60%,var(--blue) 100%);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 16px rgba(0,0,0,.25)}
  .logo{display:flex;align-items:center;gap:12px;text-decoration:none}
  .logo-emblem{width:42px;height:42px;background:#fff;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .logo-emblem img{width:42px;height:42px;object-fit:contain;border-radius:50%}
  .brand{font-size:20px;font-weight:700;color:#fff;letter-spacing:1px}
  .tagline{font-size:10px;color:rgba(255,255,255,.65)}
  .btn-back{padding:6px 14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;color:#fff;font-size:12px;font-weight:600;text-decoration:none;transition:background .2s}
  .btn-back:hover{background:rgba(255,255,255,.25)}

  /* ── Main ── */
  main{flex:1;padding:2rem;max-width:1100px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;margin-bottom:.35rem;display:flex;align-items:center;gap:10px}
  .page-sub{font-size:13px;color:var(--gray-600);margin-bottom:1.5rem}

  /* ── Stat bar ── */
  .stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.75rem}
  .stat-card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.25rem}
  .stat-label{font-size:12px;color:var(--gray-600);font-weight:500;margin-bottom:4px}
  .stat-value{font-size:28px;font-weight:700;color:var(--navy)}

  /* ── Filter ── */
  .toolbar{display:flex;gap:10px;margin-bottom:1.25rem;flex-wrap:wrap;align-items:center}
  .search-input{flex:1;min-width:200px;padding:10px 14px 10px 38px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none;transition:border-color .2s;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='M21 21l-4.35-4.35'/%3E%3C/svg%3E") no-repeat 12px center}
  .search-input:focus{border-color:var(--blue-light)}

  /* ── Satker Cards Grid ── */
  .satker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;margin-bottom:2rem}
  .satker-card{background:#fff;border-radius:var(--radius);border:1.5px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem;cursor:pointer;transition:all .2s;position:relative;overflow:hidden}
  .satker-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--blue),var(--blue-light));transform:scaleX(0);transition:transform .2s}
  .satker-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-hover);border-color:var(--blue-light)}
  .satker-card:hover::after{transform:scaleX(1)}
  .satker-card.active{border-color:var(--blue);background:var(--blue-pale)}
  .satker-card.active::after{transform:scaleX(1)}
  .satker-icon{width:48px;height:48px;border-radius:12px;background:var(--blue-pale);display:flex;align-items:center;justify-content:center;margin-bottom:1rem}
  .satker-icon svg{width:26px;height:26px;color:var(--blue);fill:none;stroke:currentColor;stroke-width:1.8}
  .satker-name{font-size:14px;font-weight:700;color:var(--navy);margin-bottom:.75rem;line-height:1.4}
  .satker-stats{display:flex;gap:.75rem}
  .satker-stat{flex:1;background:var(--gray-100);border-radius:8px;padding:8px 10px;text-align:center}
  .satker-stat-val{font-size:20px;font-weight:700;color:var(--navy)}
  .satker-stat-lbl{font-size:10px;color:var(--gray-600);margin-top:1px}
  .satker-stat.aktif .satker-stat-val{color:#1a7a38}
  .satker-stat.pensiun .satker-stat-val{color:#9b2222}

  /* ── Panel Pegawai ── */
  .pegawai-panel{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden;display:none}
  .pegawai-panel.visible{display:block}
  .panel-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;background:linear-gradient(135deg,var(--navy-dark),var(--navy-mid))}
  .panel-title{font-size:16px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px}
  .panel-meta{font-size:12px;color:rgba(255,255,255,.7)}
  .panel-toolbar{padding:.875rem 1.25rem;border-bottom:1px solid var(--gray-200);display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .panel-search{flex:1;min-width:180px;padding:8px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none;transition:border-color .2s}
  .panel-search:focus{border-color:var(--blue-light)}
  .btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;display:inline-flex;align-items:center;gap:6px}
  .btn-primary{background:var(--blue);color:#fff}
  .btn-primary:hover{background:var(--navy)}
  .btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
  .btn-outline:hover{background:var(--blue-pale)}
  .btn-danger{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  .btn-danger:hover{background:#f5c0c0}
  .btn-sm{padding:4px 10px;font-size:12px}
  table{width:100%;border-collapse:collapse}
  th{background:var(--gray-100);padding:10px 14px;font-size:11px;font-weight:600;color:var(--gray-600);text-align:left;text-transform:uppercase;letter-spacing:.4px}
  td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--gray-100);color:var(--navy)}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--blue-pale)}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
  .badge-active{background:#e2f5ea;color:#1a7a38}
  .badge-inactive{background:#fce8e8;color:#9b2222}
  .badge-pending{background:#fef3dd;color:#8a5500}
  .loading{text-align:center;padding:2.5rem;color:var(--gray-400);font-size:14px}

  /* ── Filter tags ── */
  .active-filter{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:.75rem 1.25rem;background:#e8f2fb;border-bottom:1px solid #c5dff5;font-size:13px;color:var(--navy)}
  .filter-tag{background:var(--blue);color:#fff;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px}
  .filter-tag button{background:none;border:none;color:rgba(255,255,255,.8);cursor:pointer;font-size:14px;line-height:1;padding:0}
  .filter-tag button:hover{color:#fff}

  /* ── Modal ── */
  .modal-overlay{position:fixed;inset:0;background:rgba(10,20,50,.55);display:none;z-index:200;overflow-y:auto}
  .modal-overlay.open{display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem}
  .modal{background:#fff;border-radius:16px;padding:2rem;width:100%;max-width:640px;box-shadow:0 20px 60px rgba(0,0,0,.25);margin:2rem auto}
  .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}
  .modal-title{font-size:18px;font-weight:700;color:var(--navy)}
  .modal-close{background:none;border:none;font-size:22px;color:var(--gray-400);cursor:pointer;line-height:1}
  .modal-section{font-size:11px;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:.5px;grid-column:1/-1;padding:8px 0 4px;border-bottom:1px solid var(--gray-200);margin-top:4px}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  .form-group{display:flex;flex-direction:column;gap:6px}
  .form-group.full{grid-column:1/-1}
  label{font-size:13px;font-weight:600;color:var(--gray-600)}
  input[type=text],input[type=date],input[type=number],select,textarea{padding:10px 14px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;transition:border-color .2s;background:#fff;width:100%}
  input:focus,select:focus,textarea:focus{border-color:var(--blue-light)}
  select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
  .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:1.25rem}

  /* ── Toast ── */
  .toast{position:fixed;bottom:24px;right:24px;background:#1a2e5a;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.2);z-index:999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none}
  .toast.show{opacity:1;transform:translateY(0)}
  .toast.success{background:#1a7a38}
  .toast.error{background:#9b2222}

  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;display:flex;justify-content:space-between}
  footer span{font-size:12px;color:var(--gray-400)}

  @media(max-width:640px){
    .stat-row{grid-template-columns:1fr 1fr}
    .form-grid{grid-template-columns:1fr}
    main{padding:1rem}
    .satker-grid{grid-template-columns:1fr}
  }
</style>
</head>
<body>

<header>
  <a href="index.php" class="logo">
    <div class="logo-emblem">
      <img src="logo.png" alt="SIKAPAS.RIAU" onerror="this.parentElement.innerHTML='🏛️'">
    </div>
    <div>
      <div class="brand">SIKAPAS</div>
      <div class="tagline">Sistem Informasi Kepegawaian dan Administrasi Pemasyarakatan Kanwil Riau</div>
    </div>
  </a>
  <a href="index.php" class="btn-back">← Dashboard</a>
</header>

<main>
  <div class="page-title">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Data Pegawai Per Satker
  </div>
  <p class="page-sub">Klik kartu satker untuk melihat daftar pegawai di dalamnya.</p>

  <!-- Statistik global -->
  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-label">Total Satker</div>
      <div class="stat-value"><?= count($satkers) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Pegawai</div>
      <div class="stat-value"><?= $grandTotal ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Pegawai Aktif</div>
      <div class="stat-value" style="color:#1a7a38"><?= $grandAktif ?></div>
    </div>
  </div>

  <!-- Cari satker -->
  <div class="toolbar">
    <input type="text" class="search-input" id="cari-satker"
           placeholder="Cari nama satker..." oninput="filterSatker(this.value)">
    <?php if (canEdit()): ?>
    <button class="btn btn-primary" onclick="openModalPegawai()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Pegawai
    </button>
    <?php endif; ?>
  </div>

  <!-- Kartu Satker -->
  <div class="satker-grid" id="satker-grid">
    <?php foreach ($satkers as $s): ?>
    <div class="satker-card" id="card-<?= $s['id'] ?>"
         onclick="selectSatker(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['nama'])) ?>', <?= $s['total'] ?>)">
      <div class="satker-icon">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <div class="satker-name"><?= htmlspecialchars($s['nama']) ?></div>
      <div class="satker-stats">
        <div class="satker-stat">
          <div class="satker-stat-val"><?= $s['total'] ?></div>
          <div class="satker-stat-lbl">Total</div>
        </div>
        <div class="satker-stat aktif">
          <div class="satker-stat-val"><?= $s['aktif'] ?? 0 ?></div>
          <div class="satker-stat-lbl">Aktif</div>
        </div>
        <div class="satker-stat pensiun">
          <div class="satker-stat-val"><?= $s['pensiun'] ?? 0 ?></div>
          <div class="satker-stat-lbl">Pensiun</div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($satkers)): ?>
    <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--gray-400)">
      Belum ada data satker.
    </div>
    <?php endif; ?>
  </div>

  <!-- Panel Daftar Pegawai -->
  <div class="pegawai-panel" id="pegawai-panel">
    <div class="panel-header">
      <div>
        <div class="panel-title" id="panel-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Daftar Pegawai
        </div>
        <div class="panel-meta" id="panel-meta"></div>
      </div>
      <button class="btn-back" onclick="closePanel()" style="background:rgba(255,255,255,.2)">✕ Tutup</button>
    </div>

    <!-- Filter aktif -->
    <div class="active-filter" id="active-filter">
      🏢 Menampilkan pegawai:
      <span class="filter-tag" id="filter-tag-satker">
        <span id="filter-satker-label">—</span>
        <button onclick="closePanel()">×</button>
      </span>
    </div>

    <div class="panel-toolbar">
      <input type="text" class="panel-search" id="panel-search"
             placeholder="🔍 Cari NIP / Nama / Jabatan..."
             oninput="filterPegawai(this.value)">
      <select id="filter-status" onchange="filterPegawai(document.getElementById('panel-search').value)" style="padding:8px 32px 8px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;outline:none;appearance:none;background:#fff url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%2712%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%239eadc8%27 stroke-width=%272%27%3E%3Cpath d=%27M6 9l6 6 6-6%27/%3E%3C/svg%3E') no-repeat right 10px center">
        <option value="">Semua Status</option>
        <option value="Aktif">Aktif</option>
        <option value="Pensiun">Pensiun</option>
        <option value="Dalam Proses">Dalam Proses</option>
      </select>
      <?php if (canEdit()): ?>
      <button class="btn btn-primary btn-sm" onclick="openModalPegawai()">+ Tambah</button>
      <?php endif; ?>
    </div>

    <div style="overflow-x:auto">
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>NIP</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Gol.</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="pegawai-tbody">
          <tr><td colspan="7" class="loading">Pilih satker untuk melihat pegawai</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- ══ MODAL TAMBAH/EDIT PEGAWAI ══ -->
<div class="modal-overlay" id="modal-pegawai">
  <div class="modal">
    <div class="modal-header">
      <p class="modal-title" id="modal-pegawai-title">Tambah Pegawai Baru</p>
      <button class="modal-close" onclick="closeModal('modal-pegawai')">✕</button>
    </div>
    <input type="hidden" id="peg-id">
    <div class="form-grid">

      <div class="modal-section">📋 Data Pokok</div>
      <div class="form-group">
        <label>NIP <span style="color:red">*</span></label>
        <input type="text" id="peg-nip" placeholder="18 digit NIP">
      </div>
      <div class="form-group">
        <label>Nama Lengkap <span style="color:red">*</span></label>
        <input type="text" id="peg-nama" placeholder="Nama dan gelar">
      </div>
      <div class="form-group">
        <label>Jabatan</label>
        <input type="text" id="peg-jabatan" placeholder="Jabatan struktural/fungsional">
      </div>
      <div class="form-group">
        <label>Pangkat/Golongan</label>
        <select id="peg-golongan">
          <option value="">Pilih golongan</option>
          <?php
          $gol = ['I/a','I/b','I/c','I/d','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d'];
          foreach ($gol as $g) echo "<option value=\"$g\">$g</option>";
          ?>
        </select>
      </div>
      <div class="form-group">
        <label>Unit Kerja / Satker</label>
        <select id="peg-satker">
          <option value="">Pilih Satker</option>
          <?php foreach ($satkers as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>TMT PNS</label>
        <input type="date" id="peg-tmt">
      </div>
      <div class="form-group">
        <label>Status Pegawai</label>
        <select id="peg-status">
          <option value="Aktif">Aktif</option>
          <option value="Pensiun">Pensiun</option>
          <option value="Dalam Proses">Dalam Proses</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tanggal Lahir</label>
        <input type="date" id="peg-lahir">
      </div>

      <div class="modal-section">🪪 Identitas & Nomor Kepesertaan</div>
      <div class="form-group">
        <label>No. KTP</label>
        <input type="text" id="peg-ktp" placeholder="16 digit NIK KTP" maxlength="16">
      </div>
      <div class="form-group">
        <label>No. NPWP</label>
        <input type="text" id="peg-npwp" placeholder="Nomor NPWP" maxlength="30">
      </div>
      <div class="form-group">
        <label>No. TASPEN</label>
        <input type="text" id="peg-taspen" placeholder="Nomor TASPEN" maxlength="30">
      </div>
      <div class="form-group">
        <label>No. Telepon</label>
        <input type="text" id="peg-telp" placeholder="08xx-xxxx-xxxx">
      </div>

      <div class="modal-section">📍 Kontak & Alamat</div>
      <div class="form-group">
        <label>Email</label>
        <input type="text" id="peg-email" placeholder="email@domain.com">
      </div>
      <div class="form-group"><!-- spacer --></div>
      <div class="form-group full">
        <label>Alamat Lengkap</label>
        <textarea id="peg-alamat" rows="3" placeholder="Alamat lengkap pegawai..."></textarea>
      </div>

    </div>
    <div class="form-actions">
      <button class="btn btn-outline" onclick="closeModal('modal-pegawai')">Batal</button>
      <button class="btn btn-primary" onclick="savePegawai()">Simpan Data</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<footer>
  <span>Copyright © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau</span>
  <span style="color:var(--blue);font-weight:600">v2.0</span>
</footer>

<script>
const API          = 'api/index.php';
const canEdit      = <?= canEdit() ? 'true' : 'false' ?>;
const userSatkerId = <?= $sid !== null ? $sid : 'null' ?>;

let activeSatkerId   = null;
let activeSatkerName = '';
let allPegawai       = []; // cache semua pegawai satker aktif

// ── Toast ──────────────────────────────────────────────
function toast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 3000);
}

// ── Filter kartu satker ────────────────────────────────
function filterSatker(q) {
  q = q.toLowerCase();
  document.querySelectorAll('.satker-card').forEach(card => {
    const nama = card.querySelector('.satker-name').textContent.toLowerCase();
    card.style.display = nama.includes(q) ? '' : 'none';
  });
}

// ── Pilih satker → tampilkan panel ────────────────────
async function selectSatker(id, nama, total) {
  // Toggle: klik kartu yang sama → tutup
  if (activeSatkerId === id) { closePanel(); return; }

  // Reset highlight kartu
  document.querySelectorAll('.satker-card').forEach(c => c.classList.remove('active'));
  document.getElementById('card-' + id)?.classList.add('active');

  activeSatkerId   = id;
  activeSatkerName = nama;

  // Update header panel
  document.getElementById('panel-title').innerHTML =
    `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
     ${nama}`;
  document.getElementById('panel-meta').textContent = `${total} pegawai terdaftar`;
  document.getElementById('filter-satker-label').textContent = nama;

  // Reset search
  document.getElementById('panel-search').value  = '';
  document.getElementById('filter-status').value = '';

  // Tampilkan panel & scroll ke sana
  const panel = document.getElementById('pegawai-panel');
  panel.classList.add('visible');
  setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);

  // Preset satker di modal tambah
  const selSatker = document.getElementById('peg-satker');
  if (selSatker) selSatker.value = id;

  // Load data
  await loadPegawai(id);
}

// ── Load pegawai dari API ──────────────────────────────
async function loadPegawai(satkerId) {
  const tbody = document.getElementById('pegawai-tbody');
  tbody.innerHTML = '<tr><td colspan="7" class="loading">⏳ Memuat data pegawai...</td></tr>';

  try {
    const r   = await fetch(`${API}?module=pegawai&action=list&q=&satker_id=${satkerId}`,
                            { headers: {'X-Requested-With':'XMLHttpRequest'} });
    const res = await r.json();

    if (!res.success) { throw new Error(res.message || 'Gagal memuat'); }

    // Filter client-side berdasarkan satker_id
    allPegawai = (res.data || []).filter(p => String(p.satker_id) === String(satkerId));
    renderTable(allPegawai);

  } catch(e) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:#9b2222">❌ ${e.message}</td></tr>`;
  }
}

// ── Render tabel ───────────────────────────────────────
function renderTable(data) {
  const tbody = document.getElementById('pegawai-tbody');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2.5rem;color:#9eadc8">📂 Tidak ada pegawai di satker ini</td></tr>';
    return;
  }
  tbody.innerHTML = data.map((p, i) => `
    <tr>
      <td style="color:var(--gray-400);font-size:12px">${i+1}</td>
      <td>
        <a href="profil_pegawai.php?id=${p.id}"
           style="color:var(--blue);font-weight:600;text-decoration:none"
           onmouseover="this.style.textDecoration='underline'"
           onmouseout="this.style.textDecoration='none'">${p.nip}</a>
      </td>
      <td>
        <a href="profil_pegawai.php?id=${p.id}"
           style="color:var(--navy);font-weight:600;text-decoration:none"
           onmouseover="this.style.textDecoration='underline'"
           onmouseout="this.style.textDecoration='none'">${p.nama}</a>
      </td>
      <td>${p.jabatan || '-'}</td>
      <td>${p.golongan || '-'}</td>
      <td>${badgeStatus(p.status)}</td>
      <td style="white-space:nowrap">
        ${canEdit ? `
          <button class="btn btn-sm btn-outline" onclick="editPegawai(${p.id})">Edit</button>
          <button class="btn btn-sm btn-danger"  onclick="deletePegawai(${p.id},'${(p.nama||'').replace(/'/g,"\\'")}')">Hapus</button>
        ` : `<a href="profil_pegawai.php?id=${p.id}" class="btn btn-sm btn-outline">Lihat</a>`}
      </td>
    </tr>
  `).join('');
}

// ── Filter pegawai (client-side) ───────────────────────
function filterPegawai(q) {
  const status = document.getElementById('filter-status').value;
  q = q.toLowerCase();
  const filtered = allPegawai.filter(p => {
    const matchQ = !q ||
      (p.nip    || '').toLowerCase().includes(q) ||
      (p.nama   || '').toLowerCase().includes(q) ||
      (p.jabatan|| '').toLowerCase().includes(q);
    const matchS = !status || p.status === status;
    return matchQ && matchS;
  });
  renderTable(filtered);
}

// ── Tutup panel ────────────────────────────────────────
function closePanel() {
  activeSatkerId = null;
  allPegawai     = [];
  document.querySelectorAll('.satker-card').forEach(c => c.classList.remove('active'));
  document.getElementById('pegawai-panel').classList.remove('visible');
}

// ── Badge status ───────────────────────────────────────
function badgeStatus(s) {
  const map = { 'Aktif':'badge-active', 'Pensiun':'badge-inactive', 'Dalam Proses':'badge-pending' };
  return `<span class="badge ${map[s]||'badge-pending'}">${s}</span>`;
}

// ── Modal Pegawai ──────────────────────────────────────
function openModalPegawai() {
  document.getElementById('modal-pegawai-title').textContent = 'Tambah Pegawai Baru';
  document.getElementById('peg-id').value = '';
  ['peg-nip','peg-nama','peg-jabatan','peg-tmt','peg-telp','peg-email',
   'peg-lahir','peg-alamat','peg-ktp','peg-npwp','peg-taspen'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.getElementById('peg-golongan').value = '';
  document.getElementById('peg-status').value   = 'Aktif';
  // Preset satker jika ada yang aktif
  if (activeSatkerId) document.getElementById('peg-satker').value = activeSatkerId;
  openModal('modal-pegawai');
}

async function editPegawai(id) {
  const r   = await fetch(`${API}?module=pegawai&action=get&id=${id}`,
                          { headers:{'X-Requested-With':'XMLHttpRequest'} });
  const res = await r.json();
  if (!res.success) { toast('Gagal memuat data','error'); return; }
  const p = res.data;
  document.getElementById('modal-pegawai-title').textContent = 'Edit Data Pegawai';
  document.getElementById('peg-id').value       = p.id;
  document.getElementById('peg-nip').value      = p.nip || '';
  document.getElementById('peg-nama').value     = p.nama || '';
  document.getElementById('peg-jabatan').value  = p.jabatan || '';
  document.getElementById('peg-golongan').value = p.golongan || '';
  document.getElementById('peg-satker').value   = p.satker_id || '';
  document.getElementById('peg-tmt').value      = p.tmt_pns || '';
  document.getElementById('peg-status').value   = p.status || 'Aktif';
  document.getElementById('peg-telp').value     = p.no_telepon || '';
  document.getElementById('peg-email').value    = p.email || '';
  document.getElementById('peg-lahir').value    = p.tanggal_lahir || '';
  document.getElementById('peg-alamat').value   = p.alamat || '';
  document.getElementById('peg-ktp').value      = p.no_ktp || '';
  document.getElementById('peg-npwp').value     = p.no_npwp || '';
  document.getElementById('peg-taspen').value   = p.no_taspen || '';
  openModal('modal-pegawai');
}

async function savePegawai() {
  const body = {
    id:            document.getElementById('peg-id').value,
    nip:           document.getElementById('peg-nip').value,
    nama:          document.getElementById('peg-nama').value,
    jabatan:       document.getElementById('peg-jabatan').value,
    golongan:      document.getElementById('peg-golongan').value,
    satker_id:     document.getElementById('peg-satker').value,
    tmt_pns:       document.getElementById('peg-tmt').value,
    status:        document.getElementById('peg-status').value,
    no_telepon:    document.getElementById('peg-telp').value,
    email:         document.getElementById('peg-email').value,
    tanggal_lahir: document.getElementById('peg-lahir').value,
    alamat:        document.getElementById('peg-alamat').value,
    no_ktp:        document.getElementById('peg-ktp').value,
    no_npwp:       document.getElementById('peg-npwp').value,
    no_taspen:     document.getElementById('peg-taspen').value,
  };
  const r   = await fetch(`${API}?module=pegawai&action=save`, {
    method : 'POST',
    headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body   : JSON.stringify(body)
  });
  const res = await r.json();
  if (res.success) {
    closeModal('modal-pegawai');
    toast(res.message);
    // Reload panel jika satker yang aktif sama
    const savedSatker = body.satker_id || activeSatkerId;
    if (activeSatkerId && String(savedSatker) === String(activeSatkerId)) {
      await loadPegawai(activeSatkerId);
    }
    // Reload halaman untuk update jumlah di kartu
    setTimeout(() => location.reload(), 1200);
  } else {
    toast(res.message || 'Gagal menyimpan', 'error');
  }
}

async function deletePegawai(id, nama) {
  if (!confirm(`Hapus pegawai "${nama}"?\nData terkait juga akan ikut terhapus.`)) return;
  const r   = await fetch(`${API}?module=pegawai&action=delete&id=${id}`,
                          { headers:{'X-Requested-With':'XMLHttpRequest'} });
  const res = await r.json();
  if (res.success) {
    toast(res.message);
    await loadPegawai(activeSatkerId);
    setTimeout(() => location.reload(), 1200);
  } else {
    toast(res.message || 'Gagal menghapus', 'error');
  }
}

// ── Modal utils ────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Jika user non-admin, langsung buka satker miliknya
<?php if ($sid !== null && !empty($satkers)): ?>
window.addEventListener('DOMContentLoaded', () => {
  selectSatker(<?= $satkers[0]['id'] ?>, '<?= htmlspecialchars(addslashes($satkers[0]['nama'])) ?>', <?= $satkers[0]['total'] ?>);
});
<?php endif; ?>
</script>

</body>
</html>
