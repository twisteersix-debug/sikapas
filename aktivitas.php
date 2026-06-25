<?php
require_once 'includes/config.php';
requireLogin();
if (!isAdmin()) { header('Location: dashboard.php'); exit; }
 $user = currentUser();
 $db   = getDB();

 $q     = $_GET['q']    ?? '';
 $modul = $_GET['modul']?? '';
 $sql   = "SELECT * FROM activity_log WHERE 1=1";
 $params= [];

// FIX: hanya tambah kondisi pencarian jika ada input
if ($q !== '') {
    $sql .= " AND (user_nama LIKE ? OR aksi LIKE ? OR detail LIKE ?)";
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($modul !== '') {
    $sql .= " AND modul = ?";
    $params[] = $modul;
}
 $sql  .= " ORDER BY created_at DESC LIMIT 200";

 $stmt  = $db->prepare($sql);
 $stmt->execute($params);
 $logs  = $stmt->fetchAll();

 $moduls = $db->query("SELECT DISTINCT modul FROM activity_log ORDER BY modul")
             ->fetchAll(PDO::FETCH_COLUMN);

 $pageTitle  = 'Log Aktivitas';
 $activeMenu = 'aktivitas';
include 'includes/layout.php';
?>

<div class="page-header">
  <div>
    <div class="ph-title">
      <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
      Log Aktivitas
    </div>
    <div class="ph-sub">Riwayat semua aktivitas pengguna di sistem</div>
  </div>
</div>

<!-- Filter -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
             placeholder="Cari aktivitas, user, atau detail..."
             class="form-control" style="flex:1;min-width:200px">
      <select name="modul" class="form-control" style="width:180px">
        <option value="">Semua Modul</option>
        <?php foreach ($moduls as $m): ?>
        <option value="<?= htmlspecialchars($m) ?>" <?= $modul===$m?'selected':'' ?>><?= htmlspecialchars($m) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">
        <svg viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
      </button>
      <a href="aktivitas.php" class="btn btn-outline">Reset</a>
    </form>
  </div>
</div>

<!-- Tabel -->
<div class="card">
  <div class="card-head">
    <span class="card-title">Aktivitas Terbaru (<?= count($logs) ?> data)</span>
  </div>
  <div class="tbl-wrap">
    <table class="sikapas-tbl">
      <thead>
        <tr><th>No</th><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Modul</th><th>Detail</th></tr>
      </thead>
      <tbody>
        <?php if ($logs): ?>
        <?php foreach ($logs as $i => $l): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px"><?= $i+1 ?></td>
          <td style="white-space:nowrap;font-size:12px;color:var(--gray-600)">
            <?php
            // FIX: handle jika created_at kosong atau format salah
            $ts = strtotime($l['created_at']);
            if ($ts) {
                echo date('d M Y', $ts) . '<br><span style="color:var(--gray-400)">' . date('H:i:s', $ts) . '</span>';
            } else {
                echo htmlspecialchars($l['created_at'] ?? '-');
            }
            ?>
          </td>
          <td style="font-weight:600"><?= htmlspecialchars($l['user_nama'] ?? '-') ?></td>
          <td>
            <?php
            $aksiColor = [
                'Tambah'  => 'badge-green',
                'Update'  => 'badge-blue',
                'Hapus'   => 'badge-red',
                'Login'   => 'badge-purple',
                'Upload'  => 'badge-orange',
                'Download'=> 'badge-blue'
            ];
            $cls = $aksiColor[$l['aksi']] ?? 'badge-gray';
            ?>
            <span class="badge <?= $cls ?>"><?= htmlspecialchars($l['aksi']) ?></span>
          </td>
          <td><span class="badge badge-gray"><?= htmlspecialchars($l['modul'] ?? '-') ?></span></td>
          <td style="font-size:12px;color:var(--gray-600);max-width:320px;line-height:1.5"><?= htmlspecialchars($l['detail'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="6" style="text-align:center;padding:48px 20px;color:var(--gray-400)">
          <div style="font-size:32px;margin-bottom:8px;opacity:.3">&#128196;</div>
          Tidak ada data aktivitas
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/layout_close.php'; ?>
