<?php
require_once 'includes/config.php';
requireLogin();
$user = currentUser();
$db   = getDB();

// Auto-insert & auto-update
$db->query("INSERT IGNORE INTO pengingat (pegawai_id,tgl_pensiun,catatan) SELECT id,DATE_ADD(tanggal_lahir,INTERVAL 58 YEAR),'Auto' FROM pegawai WHERE tanggal_lahir IS NOT NULL AND status='Aktif' AND id NOT IN (SELECT pegawai_id FROM pengingat)");
$db->query("UPDATE pengingat pg JOIN pegawai p ON p.id=pg.pegawai_id SET pg.tgl_pensiun=DATE_ADD(p.tanggal_lahir,INTERVAL 58 YEAR) WHERE p.tanggal_lahir IS NOT NULL");

// Satker filter
$sid = null;
if (!isAdmin()) {
    $s=$db->prepare("SELECT satker_id FROM users WHERE id=?"); $s->execute([$_SESSION['user_id']]); $r=$s->fetch();
    $sid = $r['satker_id'] ?? null;
}

$q      = $_GET['q'] ?? '';
$filter = $_GET['filter'] ?? ''; // soon, year, all
$satKl  = $sid ? " AND p.satker_id=$sid" : '';

$sql = "SELECT pg.*, p.nama, p.nip, p.jabatan, p.tanggal_lahir, s.nama AS satker,
               DATEDIFF(pg.tgl_pensiun,NOW()) AS sisa_hari,
               TIMESTAMPDIFF(YEAR,p.tanggal_lahir,NOW()) AS umur
        FROM pengingat pg
        JOIN pegawai p ON p.id=pg.pegawai_id
        LEFT JOIN satker s ON s.id=p.satker_id
        WHERE (p.nama LIKE ? OR p.nip LIKE ?)$satKl";
$params = ['%'.$q.'%','%'.$q.'%'];
if ($filter==='soon') { $sql .= " AND DATEDIFF(pg.tgl_pensiun,NOW()) <= 90 AND pg.tgl_pensiun >= NOW()"; }
elseif ($filter==='year') { $sql .= " AND DATEDIFF(pg.tgl_pensiun,NOW()) BETWEEN 0 AND 365"; }
$sql .= " ORDER BY pg.tgl_pensiun ASC LIMIT 200";
$stmt = $db->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();

$soon  = array_filter($rows, fn($r)=>$r['sisa_hari']<=90  && $r['sisa_hari']>=0);
$year  = array_filter($rows, fn($r)=>$r['sisa_hari']<=365 && $r['sisa_hari']>=0);

$pageTitle='Pengingat Pensiun'; $activeMenu='pengingat';
include 'includes/layout.php';
?>
<div class="page-header">
  <div class="page-header-left">
    <div class="ph-title"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>Pengingat Pensiun</div>
    <div class="ph-sub">Monitoring jadwal pensiun pegawai</div>
  </div>
</div>

<!-- Stat mini -->
<div class="stat-mini-row">
  <div class="stat-mini"><div class="stat-mini-icon si-red"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div><div class="stat-mini-val"><?= count($soon) ?></div><div class="stat-mini-lbl">Pensiun ≤ 90 Hari</div></div></div>
  <div class="stat-mini"><div class="stat-mini-icon si-orange"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div><div><div class="stat-mini-val"><?= count($year) ?></div><div class="stat-mini-lbl">Pensiun dalam 1 Tahun</div></div></div>
  <div class="stat-mini"><div class="stat-mini-icon si-blue"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div><div class="stat-mini-val"><?= count($rows) ?></div><div class="stat-mini-lbl">Total Termonitor</div></div></div>
</div>

<!-- Filter -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:12px 20px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="🔍 Cari nama / NIP..." class="form-control" style="flex:1;min-width:180px">
      <select name="filter" class="form-control" style="width:200px">
        <option value="">Semua</option>
        <option value="soon" <?= $filter==='soon'?'selected':'' ?>>⚠️ Pensiun ≤ 90 Hari</option>
        <option value="year" <?= $filter==='year'?'selected':'' ?>>📅 Dalam 1 Tahun</option>
      </select>
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="pengingat.php" class="btn btn-outline">Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-head"><span class="card-title">Daftar Pengingat Pensiun (<?= count($rows) ?>)</span></div>
  <div class="tbl-wrap">
    <table class="sikapas-tbl">
      <thead><tr><th>No</th><th>Nama Pegawai</th><th>NIP</th><th>Jabatan</th><th>Satker</th><th>Tgl Pensiun</th><th>Umur Skrg</th><th>Sisa Hari</th><th>Status</th></tr></thead>
      <tbody>
        <?php if ($rows): ?>
        <?php foreach ($rows as $i=>$r):
          $sisa=(int)$r['sisa_hari'];
          if ($sisa<0)      { $bc='badge-gray';   $bt='Sudah Pensiun'; }
          elseif ($sisa<=90){ $bc='badge-red';    $bt='⚠️ Sangat Dekat'; }
          elseif ($sisa<=180){ $bc='badge-orange';$bt='⚡ Dekat'; }
          elseif ($sisa<=365){ $bc='badge-blue';  $bt='📅 Dalam 1 Tahun'; }
          else              { $bc='badge-green';  $bt='✓ Masih Lama'; }
        ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px"><?= $i+1 ?></td>
          <td style="font-weight:600"><?= htmlspecialchars($r['nama']) ?></td>
          <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($r['nip']) ?></td>
          <td style="font-size:12px"><?= htmlspecialchars($r['jabatan']??'-') ?></td>
          <td style="font-size:12px"><?= htmlspecialchars($r['satker']??'-') ?></td>
          <td style="white-space:nowrap;font-weight:600"><?= date('d M Y',strtotime($r['tgl_pensiun'])) ?></td>
          <td><?= $r['umur'] ?> tahun</td>
          <td style="font-weight:700;color:<?= $sisa<=90?'var(--red)':($sisa<=365?'var(--orange)':'var(--gray-600)') ?>">
            <?= $sisa>=0 ? $sisa.' hari' : abs($sisa).' hari lalu' ?>
          </td>
          <td><span class="badge <?= $bc ?>"><?= $bt ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?><tr><td colspan="9" class="loading">Tidak ada data pengingat pensiun</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'includes/layout_close.php'; ?>
