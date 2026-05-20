<?php
// ============================================================
//  SIPATEN — Halaman Profil User
//  File: profile.php
// ============================================================
require_once 'includes/config.php';
requireLogin();

$db   = getDB();
$uid  = $_SESSION['user_id'];
$user = currentUser();

$error   = '';
$success = '';

// Ambil data user terbaru dari DB
$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$uid]);
$userData = $stmt->fetch();

$act = $_POST['act'] ?? '';

// Update profil
if ($act === 'update_profil') {
    $nama = trim($_POST['nama'] ?? '');
    if (!$nama) {
        $error = 'Nama tidak boleh kosong.';
    } else {
        $db->prepare("UPDATE users SET nama=? WHERE id=?")->execute([$nama, $uid]);
        $_SESSION['user']['nama'] = $nama;
        $userData['nama'] = $nama;
        $success = 'Profil berhasil diupdate!';
    }
}

// Ganti password
if ($act === 'ganti_password') {
    $lama  = $_POST['password_lama'] ?? '';
    $baru  = $_POST['password_baru'] ?? '';
    $ulang = $_POST['password_ulang'] ?? '';

    if (!$lama || !$baru || !$ulang) {
        $error = 'Semua field password wajib diisi.';
    } elseif (!password_verify($lama, $userData['password'])) {
        $error = 'Password lama tidak sesuai.';
    } elseif (strlen($baru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($baru !== $ulang) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $hash = password_hash($baru, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $uid]);
        $success = 'Password berhasil diubah!';
    }
}

$inisial  = strtoupper(substr($userData['nama'], 0, 1));
$roleLabel = ['admin'=>'Administrator','operator'=>'Operator','viewer'=>'Viewer'];
$roleBadge = ['admin'=>'#1a2e5a','operator'=>'#1a7a38','viewer'=>'#8a5500'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya — SIPATEN</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--navy:#1a2e5a;--navy-dark:#0f1d3a;--blue:#1e6fbf;--blue-light:#3a8fd8;--blue-pale:#e8f2fb;--white:#fff;--gray-100:#f4f6fb;--gray-200:#e8edf5;--gray-400:#9eadc8;--gray-600:#5a6a8a;--shadow:0 2px 12px rgba(30,60,120,.10);--radius:12px}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-100);color:var(--navy);min-height:100vh;display:flex;flex-direction:column}
  header{background:linear-gradient(135deg,var(--navy-dark) 0%,#243570 60%,var(--blue) 100%);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 16px rgba(0,0,0,.25)}
  .logo{display:flex;align-items:center;gap:12px;text-decoration:none}
  .logo-emblem{width:42px;height:42px;background:var(--white);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .logo-emblem img{width:42px;height:42px;object-fit:contain;border-radius:50%}
  .brand{font-size:20px;font-weight:700;color:var(--white);letter-spacing:1px}
  .tagline{font-size:10px;color:rgba(255,255,255,.65)}
  .btn-back{padding:6px 14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;color:#fff;font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;text-decoration:none}
  .btn-back:hover{background:rgba(255,255,255,.25)}
  main{flex:1;padding:2rem;max-width:760px;margin:0 auto;width:100%}
  .profile-hero{background:linear-gradient(135deg,var(--navy-dark),var(--blue));border-radius:var(--radius);padding:2rem;display:flex;align-items:center;gap:1.5rem;margin-bottom:1.5rem;color:#fff}
  .avatar-big{width:80px;height:80px;border-radius:50%;background:#e87d2a;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#fff;border:3px solid rgba(255,255,255,.3);flex-shrink:0}
  .profile-info h2{font-size:22px;font-weight:700;margin-bottom:4px}
  .profile-info .username{font-size:14px;color:rgba(255,255,255,.75);margin-bottom:8px}
  .role-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3)}
  .card{background:var(--white);border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1.25rem}
  .card-title{font-size:15px;font-weight:700;color:var(--navy);margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;gap:8px}
  .form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:1rem}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  label{font-size:13px;font-weight:600;color:var(--gray-600)}
  input[type=text],input[type=password]{width:100%;padding:10px 14px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;transition:border-color .2s;background:#fff}
  input:focus{border-color:var(--blue-light)}
  input[readonly]{background:var(--gray-100);color:var(--gray-600)}
  .form-actions{display:flex;justify-content:flex-end;margin-top:1rem}
  .btn{padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s}
  .btn-primary{background:var(--blue);color:#fff}
  .btn-primary:hover{background:var(--navy)}
  .alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:1.25rem;font-weight:500}
  .alert-success{background:#e2f5ea;color:#1a7a38;border:1px solid #b2dfc0}
  .alert-error{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  .info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--gray-100);font-size:13px}
  .info-row:last-child{border-bottom:none}
  .info-label{color:var(--gray-600);font-weight:500}
  .info-value{font-weight:600;color:var(--navy)}
  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;display:flex;justify-content:space-between}
  footer span{font-size:12px;color:var(--gray-400)}
  @media(max-width:640px){.form-grid{grid-template-columns:1fr}.profile-hero{flex-direction:column;text-align:center}main{padding:1rem}}
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
  <div style="display:flex;gap:10px">
    <?php if (isAdmin()): ?>
    <a href="users.php" class="btn-back">👥 Kelola User</a>
    <?php endif; ?>
    <a href="index.php" class="btn-back">← Dashboard</a>
  </div>
</header>

<main>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Profile Hero -->
  <div class="profile-hero">
    <div class="avatar-big"><?= $inisial ?></div>
    <div class="profile-info">
      <h2><?= htmlspecialchars($userData['nama']) ?></h2>
      <div class="username">@<?= htmlspecialchars($userData['username']) ?></div>
      <span class="role-badge"><?= $roleLabel[$userData['role']] ?? $userData['role'] ?></span>
    </div>
  </div>

  <!-- Info Akun -->
  <div class="card">
    <div class="card-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Informasi Akun
    </div>
    <div class="info-row">
      <span class="info-label">Nama Lengkap</span>
      <span class="info-value"><?= htmlspecialchars($userData['nama']) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Username</span>
      <span class="info-value">@<?= htmlspecialchars($userData['username']) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Role</span>
      <span class="info-value" style="color:<?= $roleBadge[$userData['role']] ?? '#1a2e5a' ?>"><?= $roleLabel[$userData['role']] ?? $userData['role'] ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Bergabung Sejak</span>
      <span class="info-value"><?= date('d F Y', strtotime($userData['created_at'])) ?></span>
    </div>
  </div>

  <!-- Edit Profil -->
  <div class="card">
    <div class="card-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Profil
    </div>
    <form method="POST">
      <input type="hidden" name="act" value="update_profil">
      <div class="form-grid">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($userData['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" value="<?= htmlspecialchars($userData['username']) ?>" readonly>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>

  <!-- Ganti Password -->
  <div class="card">
    <div class="card-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Ganti Password
    </div>
    <form method="POST">
      <input type="hidden" name="act" value="ganti_password">
      <div class="form-group">
        <label>Password Lama</label>
        <input type="password" name="password_lama" placeholder="Masukkan password lama" required>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label>Password Baru</label>
          <input type="password" name="password_baru" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="form-group">
          <label>Konfirmasi Password Baru</label>
          <input type="password" name="password_ulang" placeholder="Ulangi password baru" required>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Ganti Password</button>
      </div>
    </form>
  </div>
</main>

<footer>
  <span>Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau</span>
  <span style="color:var(--blue);font-weight:600">v1.0</span>
</footer>
</body>
</html>
