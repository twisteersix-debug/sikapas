<?php
require_once 'includes/config.php';
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$db = getDB();
$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$hash]);
echo "Password berhasil direset! Hash: " . $hash;
echo "<br>Silakan login dengan admin / admin123";
echo "<br><a href='login.php'>Ke halaman login</a>";
?>
