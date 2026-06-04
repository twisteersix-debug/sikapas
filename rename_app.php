<?php
// ============================================================
//  SIKAPAS.RIAU — Script Rename Aplikasi (Jalankan Sekali)
//  Upload file ini ke root folder web, akses lewat browser,
//  lalu HAPUS file ini setelah selesai.
//  URL: https://sipaten-production.up.railway.app/rename_app.php
// ============================================================

// Keamanan sederhana — ganti token ini sebelum upload
define('SECRET', 'sikapasriau2026');

$token = $_GET['token'] ?? '';
if ($token !== SECRET) {
    die('<h2 style="font-family:sans-serif;color:#9b2222;padding:2rem">
    ❌ Akses ditolak.<br>
    <small>Tambahkan <code>?token=sikapasriau2026</code> di URL</small>
    </h2>');
}

// ── Daftar penggantian string ──────────────────────────────
$replacements = [
    // Nama pendek / brand
    'SIPATEN'   => 'SIKAPAS',
    'SIYENIAJA' => 'SIKAPAS',

    // Kepanjangan / deskripsi panjang
    'Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman'
        => 'Sistem Informasi Kepegawaian dan Administrasi Pemasyarakatan Kanwil Riau',

    // Tagline
    'Satu Pintu Arsip Kepegawaian'
        => 'Satu Sikap, Satu Data PAS Riau',

    // Variasi tagline lain yang mungkin ada
    'Arsip Kepegawaian Digital'
        => 'Satu Sikap, Satu Data PAS Riau',

    // Footer / copyright
    'Copyright © 2026 SIPATEN'
        => 'Copyright © 2026 SIKAPAS.RIAU',
    'Copyright © 2026 SIYENIAJA'
        => 'Copyright © 2026 SIKAPAS.RIAU',
    'Copyright © 2026 SIPATEN. Kementerian Imigrasi &amp; Pemasyarakatan.'
        => 'Copyright © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau',
    'Copyright © 2026 SIPATEN. Kementerian Imigrasi & Pemasyarakatan.'
        => 'Copyright © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau',
    'Kanwil Ditjenpas Riau'
        => 'Kanwil Ditjenpas Riau', // sudah benar, biarkan

    // Title halaman browser
    'SIPATEN - Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman'
        => 'SIKAPAS.RIAU - Sistem Informasi Kepegawaian dan Administrasi Pemasyarakatan',
    'Login — SIPATEN'  => 'Login — SIKAPAS.RIAU',
    'Login — SIYENIAJA'=> 'Login — SIKAPAS.RIAU',
    '— SIPATEN'        => '— SIKAPAS.RIAU',
    '— SIYENIAJA'      => '— SIKAPAS.RIAU',

    // alt teks logo
    'alt="SIPATEN"'    => 'alt="SIKAPAS.RIAU"',
    'alt="SIYENIAJA"'  => 'alt="SIKAPAS.RIAU"',

    // Variabel/konstanta PHP jika ada
    "'SIPATEN'"        => "'SIKAPAS'",
    '"SIPATEN"'        => '"SIKAPAS"',
];

// ── Ekstensi file yang akan diproses ──────────────────────
$extensions = ['php', 'html', 'htm', 'js', 'css'];

// ── Folder yang DIKECUALIKAN ──────────────────────────────
$excludeDirs = ['vendor', 'node_modules', '.git', 'uploads'];

// ── Fungsi rekursif scan folder ───────────────────────────
function scanFiles(string $dir, array $extensions, array $excludeDirs): array {
    $result = [];
    $items  = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (!in_array($item, $excludeDirs)) {
                $result = array_merge($result, scanFiles($path, $extensions, $excludeDirs));
            }
        } else {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $extensions)) {
                $result[] = $path;
            }
        }
    }
    return $result;
}

// ── Jalankan penggantian ──────────────────────────────────
$rootDir  = __DIR__;
$files    = scanFiles($rootDir, $extensions, $excludeDirs);
$log      = [];
$totalRep = 0;
$errorFiles = [];

// Kecualikan file ini sendiri
$selfFile = realpath(__FILE__);

foreach ($files as $file) {
    if (realpath($file) === $selfFile) continue;

    $original = file_get_contents($file);
    if ($original === false) {
        $errorFiles[] = $file;
        continue;
    }

    $modified = $original;
    $fileRep  = 0;

    foreach ($replacements as $from => $to) {
        $count    = 0;
        $modified = str_replace($from, $to, $modified, $count);
        $fileRep += $count;
    }

    if ($fileRep > 0) {
        if (file_put_contents($file, $modified) === false) {
            $errorFiles[] = $file . ' (GAGAL TULIS)';
        } else {
            $relPath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file);
            $log[]   = ['file' => $relPath, 'count' => $fileRep];
            $totalRep += $fileRep;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rename Aplikasi — SIKAPAS.RIAU</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',sans-serif;background:#f4f6fb;color:#1a2e5a;padding:2rem}
  .card{background:#fff;border-radius:12px;padding:2rem;max-width:760px;margin:0 auto;box-shadow:0 2px 16px rgba(30,60,120,.12)}
  h1{font-size:22px;margin-bottom:.5rem;color:#1a2e5a}
  .sub{font-size:13px;color:#9eadc8;margin-bottom:1.5rem}
  .summary{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
  .stat{background:#e8f2fb;border-radius:10px;padding:1rem 1.5rem;flex:1;min-width:140px}
  .stat-val{font-size:28px;font-weight:700;color:#1e6fbf}
  .stat-lbl{font-size:12px;color:#5a6a8a;margin-top:2px}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th{background:#f4f6fb;padding:8px 12px;text-align:left;font-size:12px;color:#5a6a8a;text-transform:uppercase;letter-spacing:.4px}
  td{padding:10px 12px;border-bottom:1px solid #f0f2f6}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:#f8fbff}
  .badge-ok{background:#e2f5ea;color:#1a7a38;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
  .badge-err{background:#fce8e8;color:#9b2222;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
  .alert-warning{background:#fef3dd;border:1px solid #f0d080;border-radius:8px;padding:1rem 1.25rem;font-size:13px;color:#7b4f00;margin-top:1.5rem}
  .alert-success{background:#e2f5ea;border:1px solid #b2dfc0;border-radius:8px;padding:1rem 1.25rem;font-size:13px;color:#1a7a38;margin-bottom:1.5rem}
  .empty{padding:2rem;text-align:center;color:#9eadc8;font-size:14px}
</style>
</head>
<body>
<div class="card">
  <h1>✅ Rename Aplikasi Selesai</h1>
  <p class="sub">SIPATEN / SIYENIAJA → <strong>SIKAPAS.RIAU</strong></p>

  <div class="alert-success">
    🎉 Berhasil mengganti <strong><?= $totalRep ?> string</strong> di <strong><?= count($log) ?> file</strong>.
    Nama aplikasi sekarang sudah menjadi <strong>SIKAPAS.RIAU</strong>.
  </div>

  <div class="summary">
    <div class="stat">
      <div class="stat-val"><?= count($files) ?></div>
      <div class="stat-lbl">File Diperiksa</div>
    </div>
    <div class="stat">
      <div class="stat-val"><?= count($log) ?></div>
      <div class="stat-lbl">File Diubah</div>
    </div>
    <div class="stat">
      <div class="stat-val"><?= $totalRep ?></div>
      <div class="stat-lbl">Total Penggantian</div>
    </div>
    <div class="stat" style="background:<?= count($errorFiles) ? '#fce8e8' : '#e2f5ea' ?>">
      <div class="stat-val" style="color:<?= count($errorFiles) ? '#9b2222' : '#1a7a38' ?>"><?= count($errorFiles) ?></div>
      <div class="stat-lbl">Error</div>
    </div>
  </div>

  <?php if (count($log)): ?>
  <table>
    <thead><tr><th>File</th><th>Penggantian</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($log as $row): ?>
      <tr>
        <td><code><?= htmlspecialchars($row['file']) ?></code></td>
        <td><?= $row['count'] ?>×</td>
        <td><span class="badge-ok">✓ OK</span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty">Tidak ada file yang perlu diubah — mungkin sudah diganti sebelumnya.</div>
  <?php endif; ?>

  <?php if (count($errorFiles)): ?>
  <div style="margin-top:1rem">
    <p style="font-size:13px;font-weight:600;color:#9b2222;margin-bottom:.5rem">File Gagal Diproses:</p>
    <?php foreach ($errorFiles as $ef): ?>
      <p style="font-size:12px;color:#9b2222"><code><?= htmlspecialchars($ef) ?></code></p>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="alert-warning" style="margin-top:1.5rem">
    ⚠️ <strong>PENTING:</strong> Setelah halaman ini tampil sukses,
    <strong>hapus file <code>rename_app.php</code></strong> dari server agar tidak bisa diakses orang lain.
    <br><br>
    Cara hapus via Railway terminal:<br>
    <code style="background:#fff8e1;padding:3px 8px;border-radius:4px">rm /var/www/html/rename_app.php</code>
  </div>
</div>
</body>
</html>
