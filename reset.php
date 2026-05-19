<?php
// ============================================================
//  SIPATEN — Reset Password Admin
//  Letakkan file ini di: C:\xampp\htdocs\sipaten\reset.php
//  Buka: http://localhost/sipaten/reset.php
//  HAPUS file ini setelah selesai!
// ============================================================

require_once 'includes/config.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username'] ?? '');
    $password    = $_POST['password'] ?? '';
    $password2   = $_POST['password2'] ?? '';

    if (!$username || !$password) {
        $err = 'Username dan password wajib diisi.';
    } elseif ($password !== $password2) {
        $err = 'Password dan konfirmasi tidak sama.';
    } elseif (strlen($password) < 4) {
        $err = 'Password minimal 4 karakter.';
    } else {
        try {
            $db   = getDB();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->execute([$hash, $username]);

            if ($stmt->rowCount() > 0) {
                $msg = "✅ Password user <b>$username</b> berhasil direset ke: <b>$password</b>";
            } else {
                $err = "Username <b>$username</b> tidak ditemukan di database.";
            }
        } catch (PDOException $e) {
            $err = 'Database error: ' . $e->getMessage();
        }
    }
}

// Ambil daftar user
$users = [];
try {
    $db    = getDB();
    $users = $db->query("SELECT id, username, nama, role FROM users ORDER BY id")->fetchAll();
} catch (Exception $e) {
    $err = 'Gagal konek database: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Password — SIPATEN</title>
<style>
  body{font-family:sans-serif;max-width:500px;margin:3rem auto;padding:1rem;background:#f4f6fb}
  .card{background:#fff;border-radius:12px;padding:2rem;box-shadow:0 2px 12px rgba(0,0,0,.1)}
  h2{color:#1a2e5a;margin-bottom:1.5rem}
  label{font-size:13px;font-weight:600;color:#5a6a8a;display:block;margin-bottom:5px}
  input,select{width:100%;padding:10px 14px;border:1px solid #e8edf5;border-radius:8px;font-size:14px;font-family:inherit;margin-bottom:1rem;outline:none;box-sizing:border-box}
  input:focus{border-color:#1e6fbf}
  .btn{width:100%;padding:12px;background:#1e6fbf;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer}
  .btn:hover{background:#1a2e5a}
  .ok{background:#e2f5ea;color:#1a7a38;border:1px solid #b7e4c7;padding:12px;border-radius:8px;margin-bottom:1rem}
  .err{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0;padding:12px;border-radius:8px;margin-bottom:1rem}
  table{width:100%;border-collapse:collapse;margin-top:1.5rem;font-size:13px}
  th{background:#f4f6fb;padding:8px 12px;text-align:left;color:#5a6a8a}
  td{padding:8px 12px;border-bottom:1px solid #f0f2f6}
  .warn{background:#fff3cd;border:1px solid #ffc107;color:#856404;padding:10px 14px;border-radius:8px;font-size:13px;margin-top:1.5rem}
</style>
</head>
<body>
<div class="card">
  <h2>🔑 Reset Password SIPATEN</h2>

  <?php if ($msg): ?>
    <div class="ok"><?= $msg ?><br><br>
      Silakan <a href="login.php">Login Sekarang →</a>
    </div>
  <?php endif; ?>

  <?php if ($err): ?>
    <div class="err"><?= $err ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Pilih Username</label>
    <select name="username" required>
      <option value="">-- Pilih user --</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= htmlspecialchars($u['username']) ?>">
          <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['nama']) ?> — <?= $u['role'] ?>)
        </option>
      <?php endforeach; ?>
    </select>

    <label>Password Baru</label>
    <input type="text" name="password" placeholder="Masukkan password baru" required>

    <label>Konfirmasi Password</label>
    <input type="text" name="password2" placeholder="Ulangi password baru" required>

    <button type="submit" class="btn">Reset Password</button>
  </form>

  <?php if ($users): ?>
  <table>
    <thead><tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><b><?= htmlspecialchars($u['username']) ?></b></td>
        <td><?= htmlspecialchars($u['nama']) ?></td>
        <td><?= $u['role'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <div class="warn">
    ⚠️ <b>Hapus file reset.php</b> setelah selesai reset password!
  </div>
</div>
</body>
</html>
