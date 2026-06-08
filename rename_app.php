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
    'SIYENIAJA',
    'SIPATEN',
    'Sistem Penyimpanan Arsip Kepegawaian Terintegrasi dan Nyaman',
    'Sistem Penyimpanan Arsip Kepegawaian',
    'Copyright © 2026 SIPATEN — Kanwil Ditjenpas Riau',
    'Copyright © 2026 SIPATEN. Kementerian Imigrasi &amp; Pemasyarakatan.',
    'Copyright © 2026 SIPATEN. Kementerian Imigrasi & Pemasyarakatan.',
    'Login — SIPATEN',
    'Login — SIYENIAJA',
    'Riwayat Aktivitas — SIPATEN',
    'Arsip File — SIPATEN',
    'Pengingat Pensiun — SIPATEN',
    '— SIPATEN',
    '— SIYENIAJA',
    'alt="SIPATEN"',
    'alt="SIYENIAJA"',
    'Default: Harus Ganteng / Cantik',
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
    'Riwayat Aktivitas — SIKAPAS.RIAU',
    'Arsip File — SIKAPAS.RIAU',
    'Pengingat Pensiun — SIKAPAS.RIAU',
    '— SIKAPAS.RIAU',
    '— SIKAPAS.RIAU',
    'alt="SIKAPAS.RIAU"',
    'alt="SIKAPAS.RIAU"',
    '',
];

// ── FUNGSI SCAN FOLDER (nama unik agar tidak bentrok PHP) ─
function sikapas_scan_files($dir, $skipDirs, $exts) {
    $hasil = [];
    $items = @scandir($dir);
    if (!$items) return $hasil;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (in_array($item, $skipDirs))      continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $hasil = array_merge($hasil, sikapas_scan_files($path, $skipDirs, $exts));
        } elseif (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), $exts)) {
            $hasil[] = $path;
        }
    }
    return $hasil;
}

// ── KONFIGURASI ───────────────────────────────────────────
$root     = __DIR__;
$skipDirs = ['vendor', 'node_modules', '.git', 'uploads'];
$exts     = ['php', 'html', 'js', 'css'];
$self     = realpath(__FILE__);

// ── PROSES ────────────────────────────────────────────────
$files  = sikapas_scan_files($root, $skipDirs, $exts);
$log    = [];
$errors = [];
$total  = 0;

foreach ($files as $file) {
    if (realpath($file) === $self) continue; // lewati script ini sendiri

    $src = @file_get_contents($file);
    if ($src === false) {
        $errors[] = str_replace($root . DIRECTORY_SEPARATOR, '', $file) . ' (gagal baca)';
        continue;
    }

    $count = 0;
    $new   = str_replace($find, $replace, $src, $count);

    if ($count > 0) {
        if (@file_put_contents($file, $new) !== false) {
            $rel    = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
            $log[]  = ['file' => $rel, 'n' => $count];
            $total += $count;
        } else {
            $errors[] = str_replace($root . DIRECTORY_SEPARATOR, '', $file) . ' (gagal tulis — cek permission)';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rename Selesai — SIKAPAS.RIAU</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',sans-serif;background:#f4f6fb;padding:2rem;color:#1a2e5a}
  .wrap{max-width:760px;margin:auto;background:#fff;border-radius:14px;padding:2rem;box-shadow:0 4px 24px rgba(30,60,120,.12)}
  h1{font-size:22px;margin-bottom:.25rem}
  .sub{color:#9eadc8;font-size:13px;margin-bottom:1.5rem}
  .ok{background:#e2f5ea;border:1px solid #b2dfc0;border-radius:8px;padding:12px 16px;font-size:13px;color:#1a7a38;margin-bottom:1.25rem}
  .err-box{background:#fce8e8;border:1px solid #f5c0c0;border-radius:8px;padding:12px 16px;font-size:13px;color:#9b2222;margin-bottom:1.25rem}
  .cards{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
  .card{flex:1;min-width:130px;background:#e8f2fb;border-radius:10px;padding:1rem;text-align:center}
  .card b{display:block;font-size:26px;color:#1e6fbf;font-weight:700}
  .card span{font-size:12px;color:#5a6a8a}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th{background:#f4f6fb;padding:8px 12px;text-align:left;font-size:11px;color:#5a6a8a;text-transform:uppercase;letter-spacing:.4px}
  td{padding:9px 12px;border-bottom:1px solid #f0f2f6;vertical-align:middle}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:#f8fbff}
  .badge{background:#e2f5ea;color:#1a7a38;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
  .warn{background:#fef3dd;border:1px solid #f0d080;border-radius:8px;padding:14px 16px;font-size:13px;color:#7b4f00;margin-top:1.5rem;line-height:1.7}
  code{background:#f4f6fb;padding:2px 6px;border-radius:4px;font-size:12px;font-family:monospace}
  .empty{text-align:center;padding:2rem;color:#9eadc8;font-size:14px}
</style>
</head>
<body>
<div class="wrap">
  <h1>✅ Rename Aplikasi Selesai</h1>
  <p class="sub">SIPATEN / SIYENIAJA → <strong>SIKAPAS.RIAU</strong></p>

  <?php if ($total > 0): ?>
  <div class="ok">
    🎉 Berhasil mengganti <strong><?= $total ?> string</strong> di <strong><?= count($log) ?> file</strong>.
    Nama aplikasi sekarang sudah menjadi <strong>SIKAPAS.RIAU</strong>.
  </div>
  <?php else: ?>
  <div class="ok">
    ℹ️ Tidak ada perubahan yang perlu dilakukan — semua file kemungkinan sudah diupdate sebelumnya.
  </div>
  <?php endif; ?>

  <?php if ($errors): ?>
  <div class="err-box">
    ⚠️ <strong><?= count($errors) ?> file gagal diproses:</strong><br>
    <?php foreach($errors as $e): ?>
      <code><?= htmlspecialchars($e) ?></code><br>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Statistik -->
  <div class="cards">
    <div class="card"><b><?= count($files) ?></b><span>File Diperiksa</span></div>
    <div class="card"><b><?= count($log) ?></b><span>File Diubah</span></div>
    <div class="card"><b><?= $total ?></b><span>Total String Diganti</span></div>
    <div class="card" style="background:<?= count($errors) ? '#fce8e8' : '#e2f5ea' ?>">
      <b style="color:<?= count($errors) ? '#9b2222' : '#1a7a38' ?>"><?= count($errors) ?></b>
      <span>Error</span>
    </div>
  </div>

  <!-- Daftar file yang diubah -->
  <?php if ($log): ?>
  <table>
    <thead>
      <tr><th>File yang Diubah</th><th>Jumlah Penggantian</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php foreach ($log as $r): ?>
      <tr>
        <td><code><?= htmlspecialchars($r['file']) ?></code></td>
        <td style="text-align:center"><?= $r['n'] ?>×</td>
        <td><span class="badge">✓ Berhasil</span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty">📂 Tidak ada file yang diubah</div>
  <?php endif; ?>

  <!-- Peringatan hapus file -->
  <div class="warn">
    ⚠️ <strong>LANGKAH SELANJUTNYA — WAJIB DILAKUKAN:</strong><br>
    Setelah halaman ini muncul sukses, segera <strong>hapus file <code>rename_app.php</code> dari GitHub</strong>
    agar tidak bisa diakses oleh orang lain.<br><br>
    Caranya: Buka GitHub → klik <code>rename_app.php</code> → klik ikon 🗑️ <strong>Delete file</strong> → Commit changes → Railway akan otomatis deploy ulang.
  </div>
</div>
</body>
</html>
