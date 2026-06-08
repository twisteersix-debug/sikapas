<?php
// ============================================================
//  SIKAPAS.RIAU — Script Rename Semua File Sekaligus
//  Upload ke root web, akses dengan token, lalu HAPUS.
//  URL: https://sikapasriau.up.railway.app/rename_app.php?token=sikapasriau2026
// ============================================================
define('SECRET', 'sikapasriau2026');
if (($_GET['token'] ?? '') !== SECRET) {
    die('<h2 style="font-family:sans-serif;color:red;padding:2rem">❌ Akses ditolak. Tambahkan ?token=sikapasriau2026</h2>');
}

// ── TABEL PENGGANTIAN ─────────────────────────────────────
$find = [
    'SIPATEN',
    'SIYENIAJA',
    'Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman',
    'Sistem Penyimpanan Arsip Kepegawaian',
    'Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau',
    'Copyright © 2026 SIPATEN. Kementerian Imigrasi &amp; Pemasyarakatan.',
    'Copyright © 2026 SIPATEN. Kementerian Imigrasi & Pemasyarakatan.',
    'Login — SIPATEN',
    'Login — SIYENIAJA',
    '— SIPATEN',
    '— SIYENIAJA',
    'alt="SIPATEN"',
    'alt="SIYENIAJA"',
];

$replace = [
    'SIKAPAS',
    'SIKAPAS',
    'Sistem Informasi Kepegawaian dan Administrasi Pemasyarakatan Kanwil Riau',
    'Sistem Informasi Kepegawaian dan Administrasi Pemasyarakatan',
    'Copyright © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau',
    'Copyright © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau',
    'Copyright © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau',
    'Login — SIKAPAS.RIAU',
    'Login — SIKAPAS.RIAU',
    '— SIKAPAS.RIAU',
    '— SIKAPAS.RIAU',
    'alt="SIKAPAS.RIAU"',
    'alt="SIKAPAS.RIAU"',
];

// ── SCAN & GANTI ──────────────────────────────────────────
$root    = __DIR__;
$skip    = ['vendor','node_modules','.git','uploads','.'];
$exts    = ['php','html','js','css'];
$log     = [];
$errors  = [];
$self    = realpath(__FILE__);

function scanDir($dir, $skip, $exts) {
    $out = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        if (in_array($item, $skip))          continue;
        $path = $dir . '/' . $item;
        if (is_dir($path))  { $out = array_merge($out, scanDir($path, $skip, $exts)); continue; }
        if (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), $exts)) $out[] = $path;
    }
    return $out;
}

$files = scanDir($root, $skip, $exts);
$total = 0;

foreach ($files as $file) {
    if (realpath($file) === $self) continue;
    $src = file_get_contents($file);
    if ($src === false) { $errors[] = $file; continue; }
    $new = str_replace($find, $replace, $src, $count);
    if ($count > 0) {
        if (file_put_contents($file, $new) !== false) {
            $rel = str_replace($root . '/', '', $file);
            $log[] = ['file' => $rel, 'n' => $count];
            $total += $count;
        } else {
            $errors[] = $file . ' (GAGAL TULIS)';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rename Selesai — SIKAPAS.RIAU</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',sans-serif;background:#f4f6fb;padding:2rem;color:#1a2e5a}
  .wrap{max-width:720px;margin:auto;background:#fff;border-radius:14px;padding:2rem;box-shadow:0 4px 24px rgba(30,60,120,.12)}
  h1{font-size:22px;margin-bottom:.25rem}
  .sub{color:#9eadc8;font-size:13px;margin-bottom:1.5rem}
  .ok{background:#e2f5ea;border:1px solid #b2dfc0;border-radius:8px;padding:12px 16px;font-size:13px;color:#1a7a38;margin-bottom:1.25rem}
  .cards{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
  .card{flex:1;min-width:120px;background:#e8f2fb;border-radius:10px;padding:1rem;text-align:center}
  .card b{display:block;font-size:26px;color:#1e6fbf}
  .card span{font-size:12px;color:#5a6a8a}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th{background:#f4f6fb;padding:8px 12px;text-align:left;font-size:11px;color:#5a6a8a;text-transform:uppercase}
  td{padding:9px 12px;border-bottom:1px solid #f0f2f6}
  tr:last-child td{border-bottom:none}
  .badge{background:#e2f5ea;color:#1a7a38;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
  .warn{background:#fef3dd;border:1px solid #f0d080;border-radius:8px;padding:12px 16px;font-size:13px;color:#7b4f00;margin-top:1.25rem}
  code{background:#f4f6fb;padding:2px 6px;border-radius:4px;font-size:12px}
</style>
</head>
<body>
<div class="wrap">
  <h1>✅ Rename Selesai!</h1>
  <p class="sub">SIPATEN / SIYENIAJA → <strong>SIKAPAS.RIAU</strong></p>

  <div class="ok">🎉 Berhasil mengganti <strong><?= $total ?> string</strong> di <strong><?= count($log) ?> file</strong>. Nama aplikasi sekarang sudah menjadi <strong>SIKAPAS.RIAU</strong>.</div>

  <div class="cards">
    <div class="card"><b><?= count($files) ?></b><span>File Diperiksa</span></div>
    <div class="card"><b><?= count($log) ?></b><span>File Diubah</span></div>
    <div class="card"><b><?= $total ?></b><span>Total Penggantian</span></div>
    <div class="card" style="background:<?= count($errors)?'#fce8e8':'#e2f5ea'?>">
      <b style="color:<?= count($errors)?'#9b2222':'#1a7a38'?>"><?= count($errors) ?></b>
      <span>Error</span>
    </div>
  </div>

  <?php if ($log): ?>
  <table>
    <thead><tr><th>File</th><th>Jumlah</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($log as $r): ?>
      <tr>
        <td><code><?= htmlspecialchars($r['file']) ?></code></td>
        <td><?= $r['n'] ?>×</td>
        <td><span class="badge">✓ OK</span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="text-align:center;color:#9eadc8;padding:1.5rem">Tidak ada file yang perlu diubah — mungkin sudah diganti sebelumnya.</p>
  <?php endif; ?>

  <?php if ($errors): ?>
  <div style="margin-top:1rem;color:#9b2222;font-size:13px">
    <strong>Gagal:</strong><br>
    <?php foreach($errors as $e): ?><code><?= htmlspecialchars($e) ?></code><br><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="warn">
    ⚠️ <strong>PENTING:</strong> Setelah ini berhasil, <strong>hapus file <code>rename_app.php</code> dari GitHub</strong> agar tidak bisa diakses orang lain, lalu push ulang ke Railway.
  </div>
</div>
</body>
</html>
