<?php
// ============================================================
//  SIPATEN — Manajemen User
//  File: users.php
// ============================================================
require_once 'includes/config.php';
requireLogin();
$user = currentUser();
if (!isAdmin()) { header('Location: index.php'); exit; }

$db = getDB();
$error = '';
$success = '';

// Handle actions
$act = $_POST['act'] ?? '';

if ($act === 'tambah') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'operator';

    if (!$nama || !$username || !$password) {
        $error = 'Semua field wajib diisi.';
    } else {
        $cek = $db->prepare("SELECT id FROM users WHERE username=?");
        $cek->execute([$username]);
        if ($cek->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)");
            $stmt->execute([$nama, $username, $hash, $role]);
            $success = "User '$username' berhasil ditambahkan.";
        }
    }
}

if ($act === 'edit') {
    $id       = $_POST['id'] ?? '';
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role     = $_POST['role'] ?? 'operator';
    $password = $_POST['password'] ?? '';

    if (!$nama || !$username) {
        $error = 'Nama dan username wajib diisi.';
    } else {
        $cek = $db->prepare("SELECT id FROM users WHERE username=? AND id!=?");
        $cek->execute([$username, $id]);
        if ($cek->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET nama=?,username=?,password=?,role=? WHERE id=?");
                $stmt->execute([$nama, $username, $hash, $role, $id]);
            } else {
                $stmt = $db->prepare("UPDATE users SET nama=?,username=?,role=? WHERE id=?");
                $stmt->execute([$nama, $username, $role, $id]);
            }
            $success = "User '$username' berhasil diupdate.";
        }
    }
}

if ($act === 'hapus') {
    $id = $_POST['id'] ?? '';
    if ($id == $_SESSION['user_id']) {
        $error = 'Tidak bisa menghapus akun sendiri.';
    } else {
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        $success = 'User berhasil dihapus.';
    }
}

$users = $db->query("SELECT id, nama, username, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
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
  :root{--navy:#1a2e5a;--navy-dark:#0f1d3a;--blue:#1e6fbf;--blue-light:#3a8fd8;--blue-pale:#e8f2fb;--white:#fff;--gray-100:#f4f6fb;--gray-200:#e8edf5;--gray-400:#9eadc8;--gray-600:#5a6a8a;--shadow:0 2px 12px rgba(30,60,120,.10);--radius:12px}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-100);color:var(--navy);min-height:100vh;display:flex;flex-direction:column}
  header{background:linear-gradient(135deg,var(--navy-dark) 0%,#243570 60%,var(--blue) 100%);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 16px rgba(0,0,0,.25)}
  .logo{display:flex;align-items:center;gap:12px;text-decoration:none}
  .logo-emblem{width:42px;height:42px;background:var(--white);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
  .logo-emblem img{width:42px;height:42px;object-fit:contain;border-radius:50%}
  .brand{font-size:20px;font-weight:700;color:var(--white);letter-spacing:1px}
  .tagline{font-size:10px;color:rgba(255,255,255,.65)}
  .header-right{display:flex;align-items:center;gap:10px}
  .btn-back{padding:6px 14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;color:#fff;font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;text-decoration:none}
  .btn-back:hover{background:rgba(255,255,255,.25)}
  main{flex:1;padding:2rem;max-width:960px;margin:0 auto;width:100%}
  .page-title{font-size:22px;font-weight:700;color:var(--navy);margin-bottom:1.5rem;display:flex;align-items:center;gap:10px}
  .alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:1.25rem;font-weight:500}
  .alert-success{background:#e2f5ea;color:#1a7a38;border:1px solid #b2dfc0}
  .alert-error{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  .card{background:var(--white);border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1.5rem}
  .card-title{font-size:15px;font-weight:700;color:var(--navy);margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--gray-200)}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  .form-group{display:flex;flex-direction:column;gap:6px}
  label{font-size:13px;font-weight:600;color:var(--gray-600)}
  input[type=text],input[type=password],select{padding:10px 14px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;transition:border-color .2s;background:#fff}
  input:focus,select:focus{border-color:var(--blue-light)}
  select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
  .form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:1.25rem}
  .btn{padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s}
  .btn-primary{background:var(--blue);color:#fff}
  .btn-primary:hover{background:var(--navy)}
  .btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
  .btn-outline:hover{background:var(--blue-pale)}
  .btn-danger{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0;padding:5px 12px;font-size:12px}
  .btn-danger:hover{background:#f5c0c0}
  .btn-sm{padding:5px 12px;font-size:12px}
  table{width:100%;border-collapse:collapse}
  th{background:var(--gray-100);padding:10px 14px;font-size:12px;font-weight:600;color:var(--gray-600);text-align:left;text-transform:uppercase;letter-spacing:.4px}
  td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--gray-100);color:var(--navy)}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--blue-pale)}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
  .badge-admin{background:#e8f2fb;color:#1a2e5a}
  .badge-operator{background:#e2f5ea;color:#1a7a38}
  .badge-viewer{background:#fef3dd;color:#8a5500}
  .table-wrapper{background:var(--white);border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden}
  .table-toolbar{padding:1rem 1.25rem;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between}
  .modal-overlay{position:fixed;inset:0;background:rgba(10,20,50,.55);display:none;align-items:center;justify-content:center;z-index:200}
  .modal-overlay.open{display:flex}
  .modal{background:#fff;border-radius:16px;padding:2rem;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.25)}
  .modal-title{font-size:17px;font-weight:700;color:var(--navy);margin-bottom:1.25rem}
  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;display:flex;justify-content:space-between}
  footer span{font-size:12px;color:var(--gray-400)}
  @media(max-width:640px){.form-grid{grid-template-columns:1fr}main{padding:1rem}}
</style>
</head>
<body>
<header>
  <a href="index.php" class="logo">
    <div class="logo-emblem">
      <img src="logo.png" alt="SIPATEN" onerror="this.style.display='none';this.parentElement.innerHTML='🏛️'">
    </div>
    <div>
      <div class="brand">SIPATEN</div>
      <div class="tagline">Sistem Penyimpanan Arsip Kepegawaian</div>
    </div>
  </a>
  <div class="header-right">
    <a href="profile.php" class="btn-back">👤 Profil</a>
    <a href="index.php" class="btn-back">← Dashboard</a>
  </div>
</header>

<main>
  <div class="page-title">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Manajemen User
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Form Tambah User -->
  <div class="card">
    <div class="card-title">➕ Tambah User Baru</div>
    <form method="POST">
      <input type="hidden" name="act" value="tambah">
      <div class="form-grid">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" placeholder="Nama lengkap user" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="Username untuk login" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role">
            <option value="operator">Operator</option>
            <option value="viewer">Viewer</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Tambah User</button>
      </div>
    </form>
  </div>

  <!-- Daftar User -->
  <div class="table-wrapper">
    <div class="table-toolbar">
      <span style="font-size:14px;font-weight:700;color:var(--navy)">Daftar User (<?= count($users) ?>)</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Role</th>
          <th>Dibuat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $i => $u): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($u['nama']) ?></td>
          <td><code><?= htmlspecialchars($u['username']) ?></code></td>
          <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td style="white-space:nowrap;display:flex;gap:6px">
            <button class="btn btn-sm btn-outline" onclick="openEdit(<?= htmlspecialchars(json_encode($u)) ?>)">Edit</button>
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
            <form method="POST" onsubmit="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?')">
              <input type="hidden" name="act" value="hapus">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
            <?php else: ?>
            <span style="font-size:12px;color:var(--gray-400);padding:5px">Anda</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- Modal Edit User -->
<div class="modal-overlay" id="modal-edit" onclick="if(event.target===this)closeEdit()">
  <div class="modal">
    <div class="modal-title">✏️ Edit User</div>
    <form method="POST">
      <input type="hidden" name="act" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-grid">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" id="edit-nama" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" id="edit-username" required>
        </div>
        <div class="form-group">
          <label>Password Baru <small style="color:var(--gray-400)">(kosongkan jika tidak diubah)</small></label>
          <input type="password" name="password" placeholder="Password baru...">
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" id="edit-role">
            <option value="operator">Operator</option>
            <option value="viewer">Viewer</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-outline" onclick="closeEdit()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<footer>
  <span>Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau</span>
  <span style="color:var(--blue);font-weight:600">v1.0</span>
</footer>

<script>
function openEdit(u) {
  document.getElementById('edit-id').value       = u.id;
  document.getElementById('edit-nama').value     = u.nama;
  document.getElementById('edit-username').value = u.username;
  document.getElementById('edit-role').value     = u.role;
  document.getElementById('modal-edit').classList.add('open');
}
function closeEdit() {
  document.getElementById('modal-edit').classList.remove('open');
}
</script>
</body>
</html>
