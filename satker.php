<?php
require_once 'includes/config.php';
requireLogin();
if (!canEdit()) { header('Location: dashboard.php'); exit; }
$user = currentUser();
$db   = getDB();

$error=''; $success='';
$act = $_POST['act'] ?? '';

if ($act==='tambah') {
    $nama = trim($_POST['nama']??''); $kode = trim($_POST['kode']??''); $alamat = trim($_POST['alamat']??'');
    if (!$nama) { $error='Nama satker wajib diisi.'; }
    else {
        $db->prepare("INSERT INTO satker (nama,kode,alamat) VALUES (?,?,?)")->execute([$nama,$kode,$alamat]);
        $success="Satker '$nama' berhasil ditambahkan.";
    }
}
if ($act==='edit') {
    $id=$_POST['id']??''; $nama=trim($_POST['nama']??''); $kode=trim($_POST['kode']??''); $alamat=trim($_POST['alamat']??'');
    if (!$nama) { $error='Nama wajib diisi.'; }
    else { $db->prepare("UPDATE satker SET nama=?,kode=?,alamat=? WHERE id=?")->execute([$nama,$kode,$alamat,$id]); $success="Satker berhasil diupdate."; }
}
if ($act==='hapus') {
    $id=$_POST['id']??'';
    $jml=$db->prepare("SELECT COUNT(*) FROM pegawai WHERE satker_id=?"); $jml->execute([$id]); $jml=$jml->fetchColumn();
    if ($jml>0) { $error="Tidak bisa hapus — satker ini masih memiliki $jml pegawai."; }
    else { $db->prepare("DELETE FROM satker WHERE id=?")->execute([$id]); $success='Satker berhasil dihapus.'; }
}

$satkers = $db->query("SELECT s.*, COUNT(p.id) AS jml_pegawai FROM satker s LEFT JOIN pegawai p ON p.satker_id=s.id GROUP BY s.id ORDER BY s.nama")->fetchAll();

$pageTitle='Kelola Satker'; $activeMenu='satker';
include 'includes/layout.php';
?>

<!-- Page Header -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <div>
    <h1>
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Kelola Satker
    </h1>
    <p>Manajemen Satuan Kerja</p>
  </div>
  <div>
    <button class="btn btn-primary" onclick="openModal('modal-tambah')">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Satker
    </button>
  </div>
</div>

<?php if($error): ?><div class="alert alert-error"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><?= htmlspecialchars($success) ?></div><?php endif; ?>

<!-- Stats mini -->
<div style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap">
  <div class="card" style="display:flex;align-items:center;gap:14px;padding:16px 20px;flex:1;min-width:160px">
    <div style="width:44px;height:44px;border-radius:10px;background:var(--blue-pale);display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg viewBox="0 0 24 24" style="width:22px;height:22px;stroke:var(--blue);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </div>
    <div>
      <div style="font-size:26px;font-weight:800;color:var(--navy);line-height:1"><?= count($satkers) ?></div>
      <div style="font-size:12px;color:var(--gray-400);margin-top:2px">Total Satker</div>
    </div>
  </div>
  <div class="card" style="display:flex;align-items:center;gap:14px;padding:16px 20px;flex:1;min-width:160px">
    <div style="width:44px;height:44px;border-radius:10px;background:var(--green-pale);display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg viewBox="0 0 24 24" style="width:22px;height:22px;stroke:var(--green);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div>
      <div style="font-size:26px;font-weight:800;color:var(--navy);line-height:1"><?= array_sum(array_column($satkers,'jml_pegawai')) ?></div>
      <div style="font-size:12px;color:var(--gray-400);margin-top:2px">Total Pegawai</div>
    </div>
  </div>
</div>

<!-- Tabel Satker -->
<div class="card">
  <div class="card-head">
    <span class="card-title">Daftar Satuan Kerja</span>
  </div>
  <div class="table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Satker</th>
          <th>Kode</th>
          <th>Alamat</th>
          <th>Jml Pegawai</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($satkers as $i=>$s): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px"><?= $i+1 ?></td>
          <td style="font-weight:600"><?= htmlspecialchars($s['nama']) ?></td>
          <td><span class="badge badge-gray"><?= htmlspecialchars($s['kode']??'-') ?></span></td>
          <td style="font-size:12px;color:var(--gray-600)"><?= htmlspecialchars($s['alamat']??'-') ?></td>
          <td><span class="badge badge-blue"><?= $s['jml_pegawai'] ?> pegawai</span></td>
          <td style="white-space:nowrap">
            <button class="btn btn-sm btn-outline" onclick='openEditSatker(<?= json_encode($s) ?>)'>Edit</button>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus satker <?= htmlspecialchars(addslashes($s['nama'])) ?>?')">
              <input type="hidden" name="act" value="hapus">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($satkers)): ?>
        <tr><td colspan="6" class="loading">Belum ada data satker</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modal-tambah" onclick="closeModalOutside(event,'modal-tambah')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">➕ Tambah Satker</span>
      <button class="modal-close" onclick="closeModal('modal-tambah')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="act" value="tambah">
      <div class="form-grid">
        <div class="form-group full">
          <label class="form-label">Nama Satker *</label>
          <input type="text" name="nama" class="form-control" placeholder="Nama satuan kerja" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kode Satker</label>
          <input type="text" name="kode" placeholder="Kode (opsional)">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat</label>
          <input type="text" name="alamat" placeholder="Alamat satker">
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-tambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modal-edit" onclick="closeModalOutside(event,'modal-edit')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✏️ Edit Satker</span>
      <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="act" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-grid">
        <div class="form-group full">
          <label class="form-label">Nama Satker *</label>
          <input type="text" name="nama" id="edit-nama" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kode</label>
          <input type="text" name="kode" id="edit-kode">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat</label>
          <input type="text" name="alamat" id="edit-alamat">
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditSatker(s) {
  document.getElementById('edit-id').value     = s.id;
  document.getElementById('edit-nama').value   = s.nama;
  document.getElementById('edit-kode').value   = s.kode  || '';
  document.getElementById('edit-alamat').value = s.alamat || '';
  openModal('modal-edit');
}
</script>

<?php layoutFooter(); ?>
