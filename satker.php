<?php
require_once 'includes/config.php';
requireLogin();
if (!canEdit()) { header('Location: index.php'); exit; }
$db = getDB();

$error = '';
$success = '';
$act = $_POST['act'] ?? '';

if ($act === 'tambah') {
    $nama = trim($_POST['nama'] ?? '');
    if (!$nama) {
        $error = 'Nama satker wajib diisi.';
    } else {
        $cek = $db->prepare("SELECT id FROM satker WHERE nama=?");
        $cek->execute([$nama]);
        if ($cek->fetch()) {
            $error = 'Nama satker sudah ada.';
        } else {
            $db->prepare("INSERT INTO satker (nama) VALUES (?)")->execute([$nama]);
            $success = "Satker '$nama' berhasil ditambahkan.";
        }
    }
}

if ($act === 'edit') {
    $id   = $_POST['id'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    if (!$nama) {
        $error = 'Nama satker wajib diisi.';
    } else {
        $cek = $db->prepare("SELECT id FROM satker WHERE nama=? AND id!=?");
        $cek->execute([$nama, $id]);
        if ($cek->fetch()) {
            $error = 'Nama satker sudah digunakan.';
        } else {
            $db->prepare("UPDATE satker SET nama=? WHERE id=?")->execute([$nama, $id]);
            $success = "Satker berhasil diupdate.";
        }
    }
}

if ($act === 'hapus') {
    $id = $_POST['id'] ?? '';
    // Cek apakah ada pegawai di satker ini
    $cek = $db->prepare("SELECT COUNT(*) FROM pegawai WHERE satker_id=?");
    $cek->execute([$id]);
    if ($cek->fetchColumn() > 0) {
        $error = 'Tidak bisa hapus satker yang masih memiliki pegawai.';
    } else {
        $db->prepare("DELETE FROM satker WHERE id=?")->execute([$id]);
        $success = 'Satker berhasil dihapus.';
    }
}

$satkers = $db->query("SELECT s.*, COUNT(p.id) AS total_pegawai, SUM(p.status='Aktif') AS aktif FROM satker s LEFT JOIN pegawai p ON p.satker_id=s.id GROUP BY s.id, s.nama ORDER BY s.nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Satker — SIPATEN</title>
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
  main{flex:1;padding:2rem;max-width:960px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px}
  .layout{display:grid;grid-template-columns:300px 1fr;gap:1.5rem}
  .card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1rem}
  .card-title{font-size:14px;font-weight:700;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--gray-200)}
  .form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:.875rem}
  label{font-size:12px;font-weight:600;color:var(--gray-600)}
  input[type=text]{padding:9px 12px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;width:100%;transition:border-color .2s}
  input:focus{border-color:#3a8fd8}
  .btn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s}
  .btn-primary{background:var(--blue);color:#fff;width:100%}
  .btn-primary:hover{background:var(--navy)}
  .btn-sm{padding:5px 12px;font-size:12px}
  .btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
  .btn-outline:hover{background:var(--blue-pale)}
  .btn-danger{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  .btn-danger:hover{background:#f5c0c0}
  .alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:1.25rem;font-weight:500}
  .alert-success{background:#e2f5ea;color:#1a7a38;border:1px solid #b2dfc0}
  .alert-error{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  .table-wrapper{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden}
  .table-header{padding:1rem 1.25rem;border-bottom:1px solid var(--gray-200);font-size:14px;font-weight:700;color:var(--navy)}
  table{width:100%;border-collapse:collapse}
  th{background:var(--gray-100);padding:10px 14px;font-size:12px;font-weight:600;color:var(--gray-600);text-align:left;text-transform:uppercase}
  td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--gray-100);vertical-align:middle}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--blue-pale)}
  .satker-name{font-weight:600;color:var(--navy)}
  .count-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
  .count-blue{background:var(--blue-pale);color:var(--blue)}
  .count-green{background:#e2f5ea;color:#1a7a38}
  .modal-overlay{position:fixed;inset:0;background:rgba(10,20,50,.55);display:none;align-items:center;justify-content:center;z-index:200}
  .modal-overlay.open{display:flex}
  .modal{background:#fff;border-radius:16px;padding:2rem;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.25)}
  .modal-title{font-size:17px;font-weight:700;margin-bottom:1.25rem}
  .modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:1.25rem}
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
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Manajemen Satuan Kerja
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="layout">
    <!-- Form Tambah -->
    <div>
      <div class="card">
        <div class="card-title">➕ Tambah Satker Baru</div>
        <form method="POST">
          <input type="hidden" name="act" value="tambah">
          <div class="form-group">
            <label>Nama Satuan Kerja</label>
            <input type="text" name="nama" placeholder="Contoh: Lapas Kelas I Pekanbaru" required>
          </div>
          <button type="submit" class="btn btn-primary">Tambah Satker</button>
        </form>
      </div>

      <!-- Info -->
      <div class="card" style="background:var(--blue-pale);border-color:#c5dff5">
        <div style="font-size:13px;color:var(--navy)">
          <strong>📊 Statistik</strong><br><br>
          Total Satker: <strong><?= count($satkers) ?></strong><br>
          Total Pegawai: <strong><?= array_sum(array_column($satkers, 'total_pegawai')) ?></strong>
        </div>
      </div>
    </div>

    <!-- Tabel Satker -->
    <div class="table-wrapper">
      <div class="table-header">Daftar Satuan Kerja (<?= count($satkers) ?>)</div>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Satker</th>
            <th>Total</th>
            <th>Aktif</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($satkers as $i => $s): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td class="satker-name"><?= htmlspecialchars($s['nama']) ?></td>
            <td><span class="count-badge count-blue">👥 <?= $s['total_pegawai'] ?></span></td>
            <td><span class="count-badge count-green">✓ <?= $s['aktif'] ?? 0 ?></span></td>
            <td style="white-space:nowrap;display:flex;gap:6px">
              <button class="btn btn-sm btn-outline" onclick="openEdit(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['nama'])) ?>')">✏️ Edit</button>
              <?php if ($s['total_pegawai'] == 0): ?>
              <form method="POST" onsubmit="return confirm('Hapus satker ini?')" style="display:inline">
                <input type="hidden" name="act" value="hapus">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
              </form>
              <?php else: ?>
              <button class="btn btn-sm btn-danger" disabled title="Tidak bisa hapus, masih ada pegawai" style="opacity:.4;cursor:not-allowed">🗑️</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($satkers)): ?>
          <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--gray-400)">Belum ada satker</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal Edit -->
<div class="modal-overlay" id="modal-edit" onclick="if(event.target===this)closeEdit()">
  <div class="modal">
    <div class="modal-title">✏️ Edit Satuan Kerja</div>
    <form method="POST">
      <input type="hidden" name="act" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-group">
        <label>Nama Satuan Kerja</label>
        <input type="text" name="nama" id="edit-nama" required>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-outline" onclick="closeEdit()">Batal</button>
        <button type="submit" class="btn btn-primary" style="width:auto">Simpan</button>
      </div>
    </form>
  </div>
</div>

<footer>
  <span>Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau</span>
  <span style="color:var(--blue);font-weight:600">v2.0</span>
</footer>

<script>
function openEdit(id, nama) {
  document.getElementById('edit-id').value   = id;
  document.getElementById('edit-nama').value = nama;
  document.getElementById('modal-edit').classList.add('open');
}
function closeEdit() {
  document.getElementById('modal-edit').classList.remove('open');
}
</script>
</body>
</html>
