<?php
require_once 'includes/config.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];

$error   = '';
$success = '';

// Ambil data user
$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$uid]);
$userData = $stmt->fetch();

// Ambil data pegawai terhubung (jika ada)
$pegawaiData = null;
if (!empty($userData['pegawai_id'])) {
    $stmt2 = $db->prepare("SELECT p.*, s.nama AS satker_nama FROM pegawai p LEFT JOIN satker s ON s.id=p.satker_id WHERE p.id=?");
    $stmt2->execute([$userData['pegawai_id']]);
    $pegawaiData = $stmt2->fetch();
}

// Jika admin/operator, ambil semua pegawai untuk dropdown link akun
$pegawaiList = [];
if (isAdmin()) {
    $pegawaiList = $db->query("SELECT id, nip, nama FROM pegawai ORDER BY nama")->fetchAll();
}

$act = $_POST['act'] ?? '';

// Update profil user
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

// Link akun ke pegawai (admin only)
if ($act === 'link_pegawai' && isAdmin()) {
    $pid = $_POST['pegawai_id'] ?: null;
    $db->prepare("UPDATE users SET pegawai_id=? WHERE id=?")->execute([$pid, $uid]);
    $userData['pegawai_id'] = $pid;
    if ($pid) {
        $stmt2 = $db->prepare("SELECT p.*, s.nama AS satker_nama FROM pegawai p LEFT JOIN satker s ON s.id=p.satker_id WHERE p.id=?");
        $stmt2->execute([$pid]);
        $pegawaiData = $stmt2->fetch();
    } else {
        $pegawaiData = null;
    }
    $success = 'Akun berhasil dihubungkan ke data pegawai!';
}

// Update data pegawai
if ($act === 'update_pegawai' && $pegawaiData) {
    $pid      = $pegawaiData['id'];
    $telp     = trim($_POST['no_telepon'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $tgl      = $_POST['tanggal_lahir'] ?: null;
    $alamat   = trim($_POST['alamat'] ?? '');
    $db->prepare("UPDATE pegawai SET no_telepon=?,email=?,tanggal_lahir=?,alamat=? WHERE id=?")
       ->execute([$telp,$email,$tgl,$alamat,$pid]);
    // Refresh data
    $stmt2 = $db->prepare("SELECT p.*, s.nama AS satker_nama FROM pegawai p LEFT JOIN satker s ON s.id=p.satker_id WHERE p.id=?");
    $stmt2->execute([$pid]);
    $pegawaiData = $stmt2->fetch();
    $success = 'Data kepegawaian berhasil diupdate!';
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
$roleLabel= ['admin'=>'Administrator','operator'=>'Operator','viewer'=>'Viewer','pegawai'=>'Pegawai'];
$roleBg   = ['admin'=>'#1a2e5a','operator'=>'#1a7a38','viewer'=>'#8a5500','pegawai'=>'#1e6fbf'];
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
  .logo-emblem{width:42px;height:42px;background:#fff;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center}
  .logo-emblem img{width:42px;height:42px;object-fit:contain;border-radius:50%}
  .brand{font-size:20px;font-weight:700;color:#fff;letter-spacing:1px}
  .tagline{font-size:10px;color:rgba(255,255,255,.65)}
  .header-right{display:flex;gap:8px}
  .btn-back{padding:6px 14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;color:#fff;font-size:12px;font-weight:600;text-decoration:none}
  .btn-back:hover{background:rgba(255,255,255,.25)}
  main{flex:1;padding:2rem;max-width:860px;margin:0 auto;width:100%}
  .profile-hero{background:linear-gradient(135deg,var(--navy-dark),var(--blue));border-radius:var(--radius);padding:2rem;display:flex;align-items:center;gap:1.5rem;margin-bottom:1.5rem;color:#fff}
  .avatar{width:80px;height:80px;border-radius:50%;background:#e87d2a;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#fff;border:3px solid rgba(255,255,255,.3);flex-shrink:0}
  .profile-info h2{font-size:22px;font-weight:700;margin-bottom:4px}
  .profile-info .uname{font-size:14px;color:rgba(255,255,255,.75);margin-bottom:8px}
  .role-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3)}
  .card{background:#fff;border-radius:var(--radius);border:1px solid var(--gray-200);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1.25rem}
  .card-title{font-size:15px;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;gap:8px}
  .info-row{display:flex;justify-content:space-between;align-items:flex-start;padding:10px 0;border-bottom:1px solid var(--gray-100);font-size:13px}
  .info-row:last-child{border-bottom:none}
  .info-label{color:var(--gray-600);font-weight:500;min-width:140px;flex-shrink:0}
  .info-value{font-weight:600;color:var(--navy);text-align:right;word-break:break-word}
  .info-value.empty{color:var(--gray-400);font-weight:400;font-style:italic}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  .form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:.5rem}
  .form-group.full{grid-column:1/-1}
  label{font-size:13px;font-weight:600;color:var(--gray-600)}
  input[type=text],input[type=password],input[type=email],input[type=date],select,textarea{width:100%;padding:10px 14px;border:1px solid var(--gray-200);border-radius:8px;font-size:13px;font-family:inherit;color:var(--navy);outline:none;transition:border-color .2s;background:#fff}
  input:focus,select:focus,textarea:focus{border-color:var(--blue-light)}
  input[readonly]{background:var(--gray-100);color:var(--gray-600)}
  textarea{resize:vertical;min-height:80px}
  .form-actions{display:flex;justify-content:flex-end;margin-top:1rem}
  .btn{padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s}
  .btn-primary{background:var(--blue);color:#fff}
  .btn-primary:hover{background:var(--navy)}
  .btn-outline{background:#fff;color:var(--blue);border:1.5px solid var(--blue)}
  .alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:1.25rem;font-weight:500}
  .alert-success{background:#e2f5ea;color:#1a7a38;border:1px solid #b2dfc0}
  .alert-error{background:#fce8e8;color:#9b2222;border:1px solid #f5c0c0}
  .no-pegawai{text-align:center;padding:2rem;color:var(--gray-400)}
  .no-pegawai-icon{font-size:40px;margin-bottom:.75rem}
  select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239eadc8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
  footer{background:#fff;border-top:1px solid var(--gray-200);padding:14px 2rem;display:flex;justify-content:space-between}
  footer span{font-size:12px;color:var(--gray-400)}
  @media(max-width:640px){.form-grid{grid-template-columns:1fr}.profile-hero{flex-direction:column;text-align:center}main{padding:1rem}.info-row{flex-direction:column;gap:4px}.info-value{text-align:left}}
</style>
</head>
<body>
<header>
  <a href="index.php" class="logo">
    <div class="logo-emblem"><img src="logo.png" alt="SIPATEN" onerror="this.parentElement.innerHTML='🏛️'"></div>
    <div><div class="brand">SIPATEN</div><div class="tagline">Sistem Penyimpanan Arsip Kepegawaian</div></div>
  </a>
  <div class="header-right">
    <?php if (isAdmin()): ?>
    <a href="users.php" class="btn-back">👥 Kelola User</a>
    <?php endif; ?>
    <a href="index.php" class="btn-back">← Dashboard</a>
  </div>
</header>

<main>
  <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <!-- Hero -->
  <div class="profile-hero">
    <div class="avatar"><?= $inisial ?></div>
    <div class="profile-info">
      <h2><?= htmlspecialchars($userData['nama']) ?></h2>
      <div class="uname">@<?= htmlspecialchars($userData['username']) ?></div>
      <span class="role-badge"><?= $roleLabel[$userData['role']] ?? $userData['role'] ?></span>
    </div>
  </div>

  <!-- Info Akun -->
  <div class="card">
    <div class="card-title">👤 Informasi Akun</div>
    <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value"><?= htmlspecialchars($userData['nama']) ?></span></div>
    <div class="info-row"><span class="info-label">Username</span><span class="info-value">@<?= htmlspecialchars($userData['username']) ?></span></div>
    <div class="info-row"><span class="info-label">Role</span><span class="info-value" style="color:<?= $roleBg[$userData['role']] ?? '#1a2e5a' ?>"><?= $roleLabel[$userData['role']] ?? $userData['role'] ?></span></div>
    <div class="info-row"><span class="info-label">Bergabung</span><span class="info-value"><?= date('d F Y', strtotime($userData['created_at'])) ?></span></div>
  </div>

  <!-- Data Kepegawaian -->
  <div class="card">
    <div class="card-title">🏛️ Data Kepegawaian</div>

    <?php if ($pegawaiData): ?>
    <!-- Tampilkan data pegawai -->
    <div class="info-row"><span class="info-label">NIP</span><span class="info-value"><?= htmlspecialchars($pegawaiData['nip']) ?></span></div>
    <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value"><?= htmlspecialchars($pegawaiData['nama']) ?></span></div>
    <div class="info-row"><span class="info-label">Jabatan</span><span class="info-value <?= $pegawaiData['jabatan']?'':'empty' ?>"><?= htmlspecialchars($pegawaiData['jabatan'] ?: 'Belum diisi') ?></span></div>
    <div class="info-row"><span class="info-label">Pangkat/Golongan</span><span class="info-value <?= $pegawaiData['golongan']?'':'empty' ?>"><?= htmlspecialchars($pegawaiData['golongan'] ?: 'Belum diisi') ?></span></div>
    <div class="info-row"><span class="info-label">Unit Kerja</span><span class="info-value <?= $pegawaiData['satker_nama']?'':'empty' ?>"><?= htmlspecialchars($pegawaiData['satker_nama'] ?: 'Belum diisi') ?></span></div>
    <div class="info-row"><span class="info-label">Status</span><span class="info-value"><?= htmlspecialchars($pegawaiData['status']) ?></span></div>
    <div class="info-row"><span class="info-label">No. Telepon</span><span class="info-value <?= $pegawaiData['no_telepon']?'':'empty' ?>"><?= htmlspecialchars($pegawaiData['no_telepon'] ?: 'Belum diisi') ?></span></div>
    <div class="info-row"><span class="info-label">Email</span><span class="info-value <?= $pegawaiData['email']?'':'empty' ?>"><?= htmlspecialchars($pegawaiData['email'] ?: 'Belum diisi') ?></span></div>
    <div class="info-row"><span class="info-label">Tanggal Lahir</span><span class="info-value <?= $pegawaiData['tanggal_lahir']?'':'empty' ?>"><?= $pegawaiData['tanggal_lahir'] ? date('d F Y', strtotime($pegawaiData['tanggal_lahir'])) : 'Belum diisi' ?></span></div>
    <div class="info-row"><span class="info-label">Alamat</span><span class="info-value <?= $pegawaiData['alamat']?'':'empty' ?>"><?= htmlspecialchars($pegawaiData['alamat'] ?: 'Belum diisi') ?></span></div>

    <!-- Form update kontak -->
    <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--gray-200)">
      <div style="font-size:13px;font-weight:700;color:var(--navy);margin-bottom:1rem">✏️ Update Data Kontak & Alamat</div>
      <form method="POST">
        <input type="hidden" name="act" value="update_pegawai">
        <div class="form-grid">
          <div class="form-group">
            <label>No. Telepon</label>
            <input type="text" name="no_telepon" value="<?= htmlspecialchars($pegawaiData['no_telepon'] ?? '') ?>" placeholder="08xx-xxxx-xxxx">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($pegawaiData['email'] ?? '') ?>" placeholder="email@domain.com">
          </div>
          <div class="form-group">
            <label>Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="<?= $pegawaiData['tanggal_lahir'] ?? '' ?>">
          </div>
          <div class="form-group">
            <label>&nbsp;</label>
          </div>
          <div class="form-group full">
            <label>Alamat Lengkap</label>
            <textarea name="alamat" placeholder="Alamat lengkap..."><?= htmlspecialchars($pegawaiData['alamat'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>

    <?php else: ?>
    <div class="no-pegawai">
      <div class="no-pegawai-icon">📋</div>
      <div style="font-weight:600;color:var(--navy);margin-bottom:4px">Akun belum terhubung ke data pegawai</div>
      <div style="font-size:13px">Hubungi administrator untuk menghubungkan akun Anda</div>
    </div>
    <?php endif; ?>
  </div>

  <?php if (isAdmin()): ?>
  <!-- Link ke pegawai (admin only) -->
  <div class="card">
    <div class="card-title">🔗 Hubungkan ke Data Pegawai <small style="font-size:11px;color:var(--gray-400);font-weight:400">(Admin Only)</small></div>
    <form method="POST">
      <input type="hidden" name="act" value="link_pegawai">
      <div class="form-group">
        <label>Pilih Pegawai</label>
        <select name="pegawai_id">
          <option value="">-- Tidak dihubungkan --</option>
          <?php foreach ($pegawaiList as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ($userData['pegawai_id']==$p['id'])?'selected':'' ?>>
            <?= htmlspecialchars($p['nama']) ?> (<?= $p['nip'] ?>)
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Hubungkan</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- Edit Profil Akun -->
  <div class="card">
    <div class="card-title">✏️ Edit Profil Akun</div>
    <form method="POST">
      <input type="hidden" name="act" value="update_profil">
      <div class="form-grid">
        <div class="form-group">
          <label>Nama Tampilan</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($userData['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" value="<?= htmlspecialchars($userData['username']) ?>" readonly>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>

  <!-- Ganti Password -->
  <div class="card">
    <div class="card-title">🔒 Ganti Password</div>
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
  <span style="color:var(--blue);font-weight:600">v2.0</span>
</footer>
</body>
</html>
