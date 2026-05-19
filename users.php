<?php
// ============================================================
//  SIPATEN — Manajemen User (hanya admin)
//  File: users.php
// ============================================================
require_once 'includes/config.php';
requireAdmin();
$user = currentUser();
$db   = getDB();

// ── Proses POST ─────────────────────────────────────────────
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $nama     = trim($_POST['nama']     ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']      ?? '';
        $role     = $_POST['role']          ?? 'operator';

        if (!$nama || !$username || !$password)
            $err = 'Semua field wajib diisi.';
        elseif (strlen($password) < 6)
            $err = 'Password minimal 6 karakter.';
        else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $db->prepare("INSERT INTO users (nama,username,password,role) VALUES (?,?,?,?)")
                   ->execute([$nama, $username, $hash, $role]);
                $msg = "User <b>$username</b> berhasil ditambahkan.";
            } catch (PDOException $e) {
                $err = strpos($e->getMessage(), 'Duplicate') !== false
                     ? "Username <b>$username</b> sudah dipakai."
                     : 'Gagal menambah user: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $nama     = trim($_POST['nama']     ?? '');
        $username = trim($_POST['username'] ?? '');
        $role     = $_POST['role']          ?? 'operator';
        $password = $_POST['password']      ?? '';

        if ($id === ($user['id'] ?? 0) && $role !== 'admin')
            $err = 'Tidak bisa mengubah role akun sendiri.';
        elseif (!$nama || !$username)
            $err = 'Nama dan username wajib diisi.';
        else {
            try {
                if ($password) {
                    if (strlen($password) < 6) { $err = 'Password minimal 6 karakter.'; goto done; }
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $db->prepare("UPDATE users SET nama=?,username=?,password=?,role=? WHERE id=?")
                       ->execute([$nama,$username,$hash,$role,$id]);
                } else {
                    $db->prepare("UPDATE users SET nama=?,username=?,role=? WHERE id=?")
                       ->execute([$nama,$username,$role,$id]);
                }
                $msg = "User <b>$username</b> berhasil diperbarui.";
            } catch (PDOException $e) {
                $err = strpos($e->getMessage(),'Duplicate') !== false
                     ? "Username sudah dipakai."
                     : 'Gagal: '.$e->getMessage();
            }
        }
    }

    elseif ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === ($user['id'] ?? 0))
            $err = 'Tidak bisa menghapus akun sendiri.';
        else {
            $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            $msg = 'User berhasil dihapus.';
        }
    }
}
done:

$users = $db->query("SELECT id,nama,username,role,created_at FROM users ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen User — SIPATEN</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--navy:#1a2e5a;--blue:#1e6fbf;--blue-pale:#e8f2fb;--white:#fff;--gray-100:#f4f6fb;--gray-200:#e8edf5;--gray-400:#9eadc8;--gray-600:#5a6a8a;--radius:12px;--shadow:0 2px 12px rgba(30,60,120,.1)}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-100);color:var(--navy);min-height:100vh;display:flex;flex-direction:column}
  header{background:linear-gradient(135deg,#0f1d3a,#243570,#1e6fbf);height:64px;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 16px rgba(0,0,0,.25)}
  .brand{font-size:20px;font-weight:700;color:#fff;letter-spacing:1px}
  .header-right{display:flex;align-items:center;gap:10px}
  .btn-back{padding:6px 14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;font-family:inherit}
  .btn-back:hover{background:rgba(255,255,255,.25)}
  main{flex:1;padding:2rem;max-width:960px;margin:0 auto;width:100%}
  .page-title{font-size:20px;font-weight:700;color:var(--navy);margin-bottom:1.25rem}
  .card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1.5rem}
  .card-header{padding:1rem 1.25rem;border-bottom:1px solid var(--gray-200);font-size:14px;font-weight:700;color:var(--navy);display:flex;justify-content:space-between;align-items:center}
  .card-body{padding:1.25rem}
  table{width:100%;border-collapse:collapse}
  th{background:var(--gray-100);padding:10px 14px;font-size:12px;font-weight:600;color:var(--gray-600);text-align:left;text-transform:uppercase;letter-spacing:.4px}
  td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--gray-100)}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--blue-pale)}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
  .badge-admin{background:#e8edf5;color:#1a2e5a}
  .badge-operator{background:#e2f5ea;color:#1a7a38}
  .badge-viewer{background:#fef3dd;color:#8a5500}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  .form-group{display:flex;flex-direction:column;gap:6px}
  label{font-size:13px;font-weight:600;color:var(--gray-600)}
  input,select{padding:10px 14px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;transition:border-color .2s;background:#fff}
  input:focus,select:focus{border-color:#3a8fd8}
  select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
  .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:1.25rem}
  .btn{padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s}
  .btn-primary{background:var(--blue);color:#fff}
  .btn-primary:hover{background:var(--navy)}
  .btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
  .btn-outline:hover{background:var(--blue-pale)}
  .btn-danger{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  .btn-danger:hover{background:#f5c0c0}
  .btn-sm{padding:4px 10px;font-size:12px}
  .alert{padding:10px 16px;border-radius:8px;font-size:13px;margin-bottom:1rem}
  .alert-success{background:#e2f5ea;color:#1a7a38;border:1px solid #b7e4c7}
  .alert-error{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  /* Modal */
  .modal-overlay{position:fixed;inset:0;background:rgba(10,20,50,.55);display:none;align-items:center;justify-content:center;z-index:200}
  .modal-overlay.open{display:flex}
  .modal{background:#fff;border-radius:16px;padding:2rem;width:100%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.25)}
  .modal-title{font-size:18px;font-weight:700;color:var(--navy);margin-bottom:1.25rem}
  .hint{font-size:11px;color:var(--gray-400);margin-top:3px}
  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;text-align:center;font-size:12px;color:var(--gray-400)}
</style>
</head>
<body>
<header>
  <span class="brand">SIPATEN — Manajemen User</span>
  <div class="header-right">
    <a href="index.php" class="btn-back">← Kembali ke Dashboard</a>
  </div>
</header>

<main>
  <p class="page-title">Kelola Akun Pengguna</p>

  <?php if ($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="alert alert-error"><?= $err ?></div>
  <?php endif; ?>

  <!-- Daftar User -->
  <div class="card">
    <div class="card-header">
      <span>Daftar Pengguna (<?= count($users) ?>)</span>
      <button class="btn btn-primary btn-sm" onclick="openModalTambah()">+ Tambah User</button>
    </div>
    <table>
      <thead>
        <tr><th>Nama</th><th>Username</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= sanitize($u['nama']) ?></td>
          <td><code style="font-size:12px;background:#f4f6fb;padding:2px 6px;border-radius:4px"><?= sanitize($u['username']) ?></code></td>
          <td>
            <span class="badge badge-<?= $u['role'] ?>">
              <?= ucfirst($u['role']) ?>
            </span>
          </td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td style="white-space:nowrap">
            <button class="btn btn-sm btn-outline"
              onclick="openModalEdit(<?= $u['id'] ?>, '<?= addslashes($u['nama']) ?>', '<?= addslashes($u['username']) ?>', '<?= $u['role'] ?>')">
              Edit
            </button>
            <?php if ($u['id'] !== ($user['id'] ?? 0)): ?>
            <button class="btn btn-sm btn-danger"
              onclick="hapusUser(<?= $u['id'] ?>, '<?= addslashes($u['username']) ?>')">
              Hapus
            </button>
            <?php else: ?>
            <span style="font-size:11px;color:var(--gray-400)">(akun Anda)</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Info Role -->
  <div class="card">
    <div class="card-header">Keterangan Role</div>
    <div class="card-body">
      <table>
        <thead><tr><th>Role</th><th>Hak Akses</th></tr></thead>
        <tbody>
          <tr><td><span class="badge badge-admin">Admin</span></td><td>Akses penuh: kelola semua data + kelola user</td></tr>
          <tr><td><span class="badge badge-operator">Operator</span></td><td>Input & edit data (pegawai, KGB, tunjangan, SLKS, arsip)</td></tr>
          <tr><td><span class="badge badge-viewer">Viewer</span></td><td>Hanya lihat data, tidak bisa edit atau hapus</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- MODAL TAMBAH -->
<div class="modal-overlay" id="modal-tambah" onclick="if(event.target===this) this.classList.remove('open')">
  <div class="modal">
    <p class="modal-title">Tambah User Baru</p>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="form-grid">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" placeholder="Nama lengkap" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="username (tanpa spasi)" required pattern="[a-zA-Z0-9_]+">
          <span class="hint">Hanya huruf, angka, underscore</span>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min. 6 karakter" required minlength="6">
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role">
            <option value="operator">Operator</option>
            <option value="admin">Admin</option>
            <option value="viewer">Viewer</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-tambah').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Tambah User</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal-overlay" id="modal-edit" onclick="if(event.target===this) this.classList.remove('open')">
  <div class="modal">
    <p class="modal-title">Edit User</p>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-grid">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" id="edit-nama" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" id="edit-username" required pattern="[a-zA-Z0-9_]+">
        </div>
        <div class="form-group">
          <label>Password Baru</label>
          <input type="password" name="password" placeholder="Kosongkan jika tidak diganti" minlength="6">
          <span class="hint">Biarkan kosong jika tidak ingin mengganti password</span>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" id="edit-role">
            <option value="operator">Operator</option>
            <option value="admin">Admin</option>
            <option value="viewer">Viewer</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-edit').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- FORM HAPUS (tersembunyi) -->
<form method="POST" id="form-hapus">
  <input type="hidden" name="action" value="hapus">
  <input type="hidden" name="id" id="hapus-id">
</form>

<footer>SIPATEN v2.0 — Sistem Informasi Pegawai Tenaga Negeri</footer>

<script>
function openModalTambah() {
  document.getElementById('modal-tambah').classList.add('open');
}
function openModalEdit(id, nama, username, role) {
  document.getElementById('edit-id').value       = id;
  document.getElementById('edit-nama').value     = nama;
  document.getElementById('edit-username').value = username;
  document.getElementById('edit-role').value     = role;
  document.getElementById('modal-edit').classList.add('open');
}
function hapusUser(id, username) {
  if (!confirm(`Hapus user "${username}"? Tindakan ini tidak bisa dibatalkan.`)) return;
  document.getElementById('hapus-id').value = id;
  document.getElementById('form-hapus').submit();
}
</script>
</body>
</html>
