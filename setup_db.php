<?php
// ============================================================
//  SIPATEN — Auto Setup Database (jalankan sekali saja)
//  URL: https://your-app.railway.app/setup_db.php
//  HAPUS file ini setelah setup berhasil!
// ============================================================
require_once 'includes/config.php';

// Proteksi: hanya bisa dijalankan jika belum ada tabel users
$db = getDB();
try {
    $check = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($check > 0) {
        die('<h2 style="font-family:sans-serif;color:green">✅ Database sudah ter-setup. Silakan <a href="login.php">Login</a>.<br><br><b style="color:red">Hapus file setup_db.php dari server!</b></h2>');
    }
} catch (PDOException $e) {
    // Tabel belum ada, lanjut setup
}

$sql = file_get_contents(__DIR__ . '/database.sql');

// Jalankan per statement
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => !empty($s) && !str_starts_with(ltrim($s), '--')
);

$errors  = [];
$success = 0;

foreach ($statements as $stmt) {
    if (empty(trim($stmt))) continue;
    try {
        $db->exec($stmt);
        $success++;
    } catch (PDOException $e) {
        // Abaikan error "already exists"
        if (!str_contains($e->getMessage(), 'already exists')) {
            $errors[] = htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup SIPATEN</title>
<style>
  body{font-family:sans-serif;max-width:600px;margin:3rem auto;padding:1rem}
  .ok{color:green} .err{color:red} .warn{color:orange}
  pre{background:#f4f6fb;padding:1rem;border-radius:8px;font-size:12px;overflow:auto}
  a.btn{display:inline-block;margin-top:1.5rem;padding:12px 28px;background:#1e6fbf;color:#fff;text-decoration:none;border-radius:8px;font-weight:700}
</style>
</head>
<body>
<h2>Setup Database SIPATEN</h2>
<p>✅ Berhasil: <b><?= $success ?></b> statement dijalankan</p>
<?php if ($errors): ?>
  <p class="warn">⚠️ <?= count($errors) ?> error (mungkin tidak kritis):</p>
  <pre><?= implode("\n", $errors) ?></pre>
<?php else: ?>
  <p class="ok">✅ Tidak ada error!</p>
<?php endif; ?>
<p class="ok"><b>Database berhasil di-setup!</b></p>
<p>Login dengan: <b>admin</b> / <b>Admin123!</b></p>
<a class="btn" href="login.php">Buka Halaman Login →</a>
<hr style="margin:2rem 0">
<p class="err"><b>⚠️ PENTING:</b> Hapus file <code>setup_db.php</code> dari server setelah ini!</p>
</body>
</html>
