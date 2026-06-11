<?php
require_once 'includes/config.php';
requireLogin();
if (!isAdmin()) { header('Location: dashboard.php'); exit; }
$user = currentUser();
$db   = getDB();
$satkerList = $db->query("SELECT id,nama FROM satker ORDER BY nama")->fetchAll();
$error=''; $success='';
$act = $_POST['act']??'';

if ($act==='tambah') {
    $nama=trim($_POST['nama']??''); $uname=trim($_POST['username']??''); $pass=$_POST['password']??''; $role=$_POST['role']??'operator'; $sid=$_POST['satker_id']?:null;
    if (!$nama||!$uname||!$pass) { $error='Semua field wajib diisi.'; }
    else {
        $cek=$db->prepare("SELECT id FROM users WHERE username=?"); $cek->execute([$uname]);
        if ($cek->fetch()) { $error='Username sudah digunakan.'; }
        else { $db->prepare("INSERT INTO users (nama,username,password,role,satker_id) VALUES (?,?,?,?,?)")->execute([$nama,$uname,password_hash($pass,PASSWORD_BCRYPT),$role,$sid]); $success="User '$uname' berhasil ditambahkan."; }
    }
}
if ($act==='edit') {
    $id=$_POST['id']??''; $nama=trim($_POST['nama']??''); $uname=trim($_POST['username']??''); $role=$_POST['role']??'operator'; $pass=$_POST['password']??''; $sid=$_POST['satker_id']?:null;
    if (!$nama||!$uname) { $error='Nama dan username wajib diisi.'; }
    else {
        $cek=$db->prepare("SELECT id FROM users WHERE username=? AND id!=?"); $cek->execute([$uname,$id]);
        if ($cek->fetch()) { $error='Username sudah digunakan.'; }
        else {
            if ($pass) $db->prepare("UPDATE users SET nama=?,username=?,password=?,role=?,satker_id=? WHERE id=?")->execute([$nama,$uname,password_hash($pass,PASSWORD_BCRYPT),$role,$sid,$id]);
            else $db->prepare("UPDATE users SET nama=?,username=?,role=?,satker_id=? WHERE id=?")->execute([$nama,$uname,$role,$sid,$id]);
            $success="User '$uname' berhasil diupdate.";
        }
    }
}
if ($act==='hapus') {
    $id=$_POST['id']??'';
    if ($id==$_SESSION['user_id']) { $error='Tidak bisa menghapus akun sendiri.'; }
    else { $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]); $success='User berhasil dihapus.'; }
}
if ($act==='reset_pass') {
    $id=$_POST['id']??'';
    $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash('sikapas123',PASSWORD_BCRYPT),$id]);
    $success='Password direset ke: sikapas123';
}
$users = $db->query("SELECT u.*,s.nama AS satker_nama FROM users u LEFT JOIN satker s ON s.id=u.satker_id ORDER BY u.created_at DESC")->fetchAll();

$pageTitle='Kelola User'; $activeMenu='users';
include 'includes/layout.php';
?>
<div class="page-header">
  <div class="page-header-left">
    <div class="ph-title"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Kelola User</div>
    <div class="ph-sub">Manajemen pengguna dan hak akses</div>
  </div>
  <div class="ph-actions">
    <button class="btn btn-primary" onclick="openModal('modal-tambah')">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah User
    </button>
  </div>
</div>

<?php if($error): ?><div class="alert alert-error"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="alert alert-info" style="margin-bottom:16px">
  <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
  <strong>Info:</strong> Operator/Viewer hanya dapat mengakses data pegawai di satker yang ditentukan. Admin dapat mengakses semua satker.
</div>

<div class="card">
  <div class="card-head"><span class="card-title">Daftar User (<?= count($users) ?>)</span></div>
  <div class="tbl-wrap">
    <table class="sikapas-tbl">
      <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th><th>Satker</th><th>Dibuat</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($users as $i=>$u): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px"><?= $i+1 ?></td>
          <td style="font-weight:600"><?= htmlspecialchars($u['nama']) ?></td>
          <td><code style="background:var(--gray-100);padding:2px 8px;border-radius:5px;font-size:12px"><?= htmlspecialchars($u['username']) ?></code></td>
          <td>
            <?php $rc=['admin'=>'badge-purple','operator'=>'badge-green','viewer'=>'badge-orange']; ?>
            <span class="badge <?= $rc[$u['role']]??'badge-gray' ?>"><?= ucfirst($u['role']) ?></span>
          </td>
          <td>
            <?php if($u['role']==='admin'): ?><span class="badge badge-blue">🔓 Semua Satker</span>
            <?php elseif($u['satker_nama']): ?><span class="badge badge-gray">🏢 <?= htmlspecialchars($u['satker_nama']) ?></span>
            <?php else: ?><span style="font-size:11px;color:var(--gray-400)">Belum ditentukan</span><?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--gray-600)"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
          <td style="white-space:nowrap;display:flex;gap:6px;flex-wrap:wrap">
            <button class="btn btn-sm btn-outline" onclick='openEditUser(<?= json_encode($u) ?>)'>Edit</button>
            <?php if($u['id']!=$_SESSION['user_id']): ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('Reset password?')">
              <input type="hidden" name="act" value="reset_pass"><input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-sm btn-warning">Reset Pass</button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus user ini?')">
              <input type="hidden" name="act" value="hapus"><input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
            <?php else: ?><span style="font-size:11px;color:var(--gray-400);padding:4px 6px">Anda</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modal-tambah" onclick="closeModalOutside(event,'modal-tambah')">
  <div class="modal">
    <div class="modal-head"><span class="modal-title">➕ Tambah User</span><button class="modal-close" onclick="closeModal('modal-tambah')">✕</button></div>
    <form method="POST"><div class="modal-body">
      <input type="hidden" name="act" value="tambah">
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Nama Lengkap *</label><input type="text" name="nama" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Role</label>
          <select name="role" class="form-control"><option value="operator">Operator</option><option value="viewer">Viewer</option><option value="admin">Admin</option></select></div>
        <div class="form-group full"><label class="form-label">Satker (Wajib untuk Operator/Viewer)</label>
          <select name="satker_id" class="form-control"><option value="">— Semua Satker (Admin) —</option><?php foreach($satkerList as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option><?php endforeach; ?></select></div>
      </div>
    </div>
    <div class="modal-foot"><button type="button" class="btn btn-outline" onclick="closeModal('modal-tambah')">Batal</button><button type="submit" class="btn btn-primary">Tambah</button></div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modal-edit" onclick="closeModalOutside(event,'modal-edit')">
  <div class="modal">
    <div class="modal-head"><span class="modal-title">✏️ Edit User</span><button class="modal-close" onclick="closeModal('modal-edit')">✕</button></div>
    <form method="POST"><div class="modal-body">
      <input type="hidden" name="act" value="edit">
      <input type="hidden" name="id" id="eu-id">
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Nama Lengkap *</label><input type="text" name="nama" id="eu-nama" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" id="eu-username" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Password Baru <small style="color:var(--gray-400)">(kosongkan jika tidak diubah)</small></label><input type="password" name="password" class="form-control" placeholder="Password baru..."></div>
        <div class="form-group"><label class="form-label">Role</label><select name="role" id="eu-role" class="form-control"><option value="operator">Operator</option><option value="viewer">Viewer</option><option value="admin">Admin</option></select></div>
        <div class="form-group full"><label class="form-label">Satker</label>
          <select name="satker_id" id="eu-satker" class="form-control"><option value="">— Semua Satker (Admin) —</option><?php foreach($satkerList as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option><?php endforeach; ?></select></div>
      </div>
    </div>
    <div class="modal-foot"><button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>
<script>
function openEditUser(u) {
  document.getElementById('eu-id').value      = u.id;
  document.getElementById('eu-nama').value    = u.nama;
  document.getElementById('eu-username').value= u.username;
  document.getElementById('eu-role').value    = u.role;
  document.getElementById('eu-satker').value  = u.satker_id||'';
  openModal('modal-edit');
}
</script>
<?php include 'includes/layout_close.php'; ?>
