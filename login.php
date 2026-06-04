<?php
// ============================================================
//  SIKAPAS.RIAU — Halaman Login
//  File: login.php
// ============================================================
require_once 'includes/config.php';
startSession();

// Jika sudah login, langsung ke dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = [
                'id'      => $user['id'],
                'nama'    => $user['nama'],
                'role'    => $user['role'],
                'inisial' => strtoupper(substr($user['nama'], 0, 1)),
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Isi username dan password.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — SIKAPAS.RIAU</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:linear-gradient(135deg,#0f1d3a 0%,#243570 60%,#1e6fbf 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
  .card{background:#fff;border-radius:16px;padding:2.5rem 2rem;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
  .logo{display:flex;align-items:center;gap:12px;margin-bottom:1.75rem}
  .emblem{width:54px;height:54px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .brand{font-size:22px;font-weight:700;color:#1a2e5a;letter-spacing:.5px}
  .tagline{font-size:10px;color:#9eadc8;margin-top:2px;line-height:1.4}
  .divider{height:1px;background:#e8edf5;margin-bottom:1.5rem}
  h2{font-size:17px;font-weight:700;color:#1a2e5a;margin-bottom:1.5rem}
  .form-group{margin-bottom:1rem}
  label{font-size:13px;font-weight:600;color:#5a6a8a;display:block;margin-bottom:6px}
  input{width:100%;padding:11px 14px;border:1.5px solid #e8edf5;border-radius:8px;font-size:14px;font-family:inherit;outline:none;transition:border-color .2s;color:#1a2e5a}
  input:focus{border-color:#1e6fbf}
  .btn{width:100%;padding:12px;background:#1e6fbf;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;margin-top:.5rem;transition:background .2s}
  .btn:hover{background:#1a2e5a}
  .error{background:#fce8e8;color:#9b2222;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:1rem}
  .footer-tag{font-size:11px;color:#c0c8d8;text-align:center;margin-top:1.5rem;padding-top:1rem;border-top:1px solid #f0f2f6}
  .tagline-hero{background:linear-gradient(135deg,#e8f2fb,#f4f6fb);border-radius:10px;padding:10px 14px;margin-bottom:1.5rem;text-align:center}
  .tagline-hero span{font-size:13px;font-weight:600;color:#1e6fbf;font-style:italic}
</style>
</head>
<body>
<div class="card">

  <!-- Logo & Nama -->
  <div class="logo">
    <div class="emblem" style="background:none;padding:0">
      <img src="logo.png" alt="SIKAPAS.RIAU"
           style="width:54px;height:54px;object-fit:contain;"
           onerror="this.style.display='none';this.parentElement.innerHTML='🏛️'">
    </div>
    <div>
      <div class="brand">SIKAPAS.RIAU</div>
      <div class="tagline">Sistem Informasi Kepegawaian dan Administrasi<br>Pemasyarakatan Kanwil Riau</div>
    </div>
  </div>

  <!-- Tagline -->
  <div class="tagline-hero">
    <span>✦ Satu Sikap, Satu Data PAS Riau ✦</span>
  </div>

  <div class="divider"></div>

  <h2>Masuk ke Sistem</h2>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" placeholder="Masukkan username"
             value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Masukkan password" required>
    </div>
    <button type="submit" class="btn">Masuk</button>
  </form>

  <div class="footer-tag">
    © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau<br>
    Kementerian Imigrasi &amp; Pemasyarakatan
  </div>

</div>
</body>
</html>
