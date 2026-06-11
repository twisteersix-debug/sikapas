<?php
// ============================================================
//  SIKAPAS.RIAU — Kelola Satker
//  File: satker.php
// ============================================================
require_once 'includes/config.php';
requireLogin();
if (!canEdit()) { header('Location: dashboard.php'); exit; }

$user = currentUser();
$db   = getDB();

$error   = '';
$success = '';
$act     = $_POST['act'] ?? '';

if ($act === 'tambah') {
    $nama   = trim($_POST['nama']   ?? '');
    $kode   = trim($_POST['kode']   ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if (!$nama) {
        $error = 'Nama satker wajib diisi.';
    } else {
        $cek = $db->prepare("SELECT id FROM satker WHERE nama=?");
        $cek->execute([$nama]);
        if ($cek->fetch()) {
            $error = "Satker dengan nama '$nama' sudah ada.";
        } else {
            $db->prepare("INSERT INTO satker (nama, kode, alamat) VALUES (?,?,?)")
               ->execute([$nama, $kode ?: null, $alamat ?: null]);
            $success = "Satker '$nama' berhasil ditambahkan.";
        }
    }
}

if ($act === 'edit') {
    $id     = $_POST['id']     ?? '';
    $nama   = trim($_POST['nama']   ?? '');
    $kode   = trim($_POST['kode']   ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if (!$nama) {
        $error = 'Nama satker wajib diisi.';
    } else {
        $db->prepare("UPDATE satker SET nama=?, kode=?, alamat=? WHERE id=?")
           ->execute([$nama, $kode ?: null, $alamat ?: null, $id]);
        $success = "Satker '$nama' berhasil diupdate.";
    }
}

if ($act === 'hapus') {
    $id  = $_POST['id'] ?? '';
    $cek = $db->prepare("SELECT COUNT(*) FROM pegawai WHERE satker_id=?");
    $cek->execute([$id]);
    $jml = $cek->fetchColumn();
    if ($jml > 0) {
        $error = "Tidak bisa menghapus — satker ini masih memiliki $jml pegawai.";
    } else {
        $db->prepare("DELETE FROM satker WHERE id=?")->execute([$id]);
        $success = 'Satker berhasil dihapus.';
    }
}

// Cek kolom yang tersedia di tabel satker
$cols = $db->query("SHOW COLUMNS FROM satker")->fetchAll(PDO::FETCH_COLUMN);
$hasKode   = in_array('kode',   $cols);
$hasAlamat = in_array('alamat', $cols);

// Tambah kolom kode/alamat jika belum ada
if (!$hasKode)   $db->exec("ALTER TABLE satker ADD COLUMN kode VARCHAR(30) NULL");
if (!$hasAlamat) $db->exec("ALTER TABLE satker ADD COLUMN alamat VARCHAR(255) NULL");

$satkers = $db->query("
    SELECT s.id, s.nama,
           IFNULL(s.kode,'')   AS kode,
           IFNULL(s.alamat,'') AS alamat,
           COUNT(p.id)              AS jml_pegawai,
           SUM(p.status='Aktif')   AS jml_aktif
    FROM satker s
    LEFT JOIN pegawai p ON p.satker_id = s.id
    GROUP BY s.id, s.nama, s.kode, s.alamat
    ORDER BY s.nama
")->fetchAll();

$totalPegawai = array_sum(array_column($satkers, 'jml_pegawai'));

// ── Render ──────────────────────────────────────────────────
$pageTitle  = 'Kelola Satker';
$activeMenu = 'satker';
require_once 'includes/layout.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div>
    <div class="ph-title">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Kelola Satker
    </div>
    <div class="ph-sub">Manajemen Satuan Kerja</div>
  </div>
  <div class="ph-actions">
    <button class="btn btn-primary" onclick="openModal('modal-tambah')">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Satker
    </button>
  </div>
</div>

<!-- Alert -->
<?php if ($error): ?>
<div class="alert alert-error">
  <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success">
  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<!-- Stat Mini -->
<div class="stat-mini-row">
  <div class="stat-mini">
    <div class="stat-mini-icon si-blue">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </div>
    <div>
      <div class="stat-mini-val"><?= count($satkers) ?></div>
      <div class="stat-mini-lbl">Total Satker</div>
    </div>
  </div>
  <div class="stat-mini">
    <div class="stat-mini-icon si-green">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div>
      <div class="stat-mini-val"><?= $totalPegawai ?></div>
      <div class="stat-mini-lbl">Total Pegawai</div>
    </div>
  </div>
  <div class="stat-mini">
    <div class="stat-mini-icon si-orange">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div>
      <div class="stat-mini-val"><?= array_sum(array_column($satkers,'jml_aktif')) ?></div>
      <div class="stat-mini-lbl">Pegawai Aktif</div>
    </div>
  </div>
</div>

<!-- Tabel Satker -->
<div class="card">
  <div class="card-head">
    <span class="card-title">Daftar Satuan Kerja (<?= count($satkers) ?>)</span>
    <a href="pegawai_satker.php" class="btn btn-sm btn-outline">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Lihat per Satker
    </a>
  </div>
  <div class="tbl-wrap">
    <table class="sikapas-tbl">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Satker</th>
          <th>Kode</th>
          <th>Alamat</th>
          <th>Jml Pegawai</th>
          <th>Aktif</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($satkers): ?>
        <?php foreach ($satkers as $i => $s): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px"><?= $i + 1 ?></td>
          <td style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($s['nama']) ?></td>
          <td>
            <?php if ($s['kode']): ?>
              <span class="badge badge-gray"><?= htmlspecialchars($s['kode']) ?></span>
            <?php else: ?>
              <span style="color:var(--gray-400);font-size:12px">—</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--gray-600)">
            <?= $s['alamat'] ? htmlspecialchars($s['alamat']) : '<span style="color:var(--gray-400)">—</span>' ?>
          </td>
          <td>
            <span class="badge badge-blue"><?= $s['jml_pegawai'] ?> pegawai</span>
          </td>
          <td>
            <span class="badge badge-green"><?= (int)$s['jml_aktif'] ?> aktif</span>
          </td>
          <td style="white-space:nowrap">
            <button class="btn btn-sm btn-outline"
                    onclick='openEditSatker(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)'>
              Edit
            </button>
            <?php if ((int)$s['jml_pegawai'] === 0): ?>
            <form method="POST" style="display:inline"
                  onsubmit="return confirm('Hapus satker \"<?= htmlspecialchars(addslashes($s['nama'])) ?>\"?')">
              <input type="hidden" name="act" value="hapus">
              <input type="hidden" name="id"  value="<?= $s['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
            <?php else: ?>
            <button class="btn btn-sm btn-danger" disabled
                    title="Tidak bisa hapus — masih ada pegawai"
                    style="opacity:.45;cursor:not-allowed">
              Hapus
            </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="7" class="loading">Belum ada data satker. Klik "Tambah Satker" untuk menambahkan.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ══ Modal Tambah ══ -->
<div class="modal-overlay" id="modal-tambah" onclick="closeModalOutside(event,'modal-tambah')">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title">➕ Tambah Satker Baru</span>
      <button class="modal-close" onclick="closeModal('modal-tambah')">✕</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="act" value="tambah">
        <div class="form-grid">
          <div class="form-group full">
            <label class="form-label">Nama Satker <span style="color:var(--red)">*</span></label>
            <input type="text" name="nama" class="form-control"
                   placeholder="Contoh: Lapas Kelas IIA Pekanbaru" required>
          </div>
          <div class="form-group">
            <label class="form-label">Kode Satker</label>
            <input type="text" name="kode" class="form-control"
                   placeholder="Contoh: LAPAS-PKU">
          </div>
          <div class="form-group">
            <label class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control"
                   placeholder="Alamat singkat satker">
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-tambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ Modal Edit ══ -->
<div class="modal-overlay" id="modal-edit" onclick="closeModalOutside(event,'modal-edit')">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title">✏️ Edit Satker</span>
      <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="act" value="edit">
        <input type="hidden" name="id"  id="edit-satker-id">
        <div class="form-grid">
          <div class="form-group full">
            <label class="form-label">Nama Satker <span style="color:var(--red)">*</span></label>
            <input type="text" name="nama" id="edit-satker-nama" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Kode Satker</label>
            <input type="text" name="kode" id="edit-satker-kode" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Alamat</label>
            <input type="text" name="alamat" id="edit-satker-alamat" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditSatker(s) {
  document.getElementById('edit-satker-id').value    = s.id;
  document.getElementById('edit-satker-nama').value  = s.nama   || '';
  document.getElementById('edit-satker-kode').value  = s.kode   || '';
  document.getElementById('edit-satker-alamat').value= s.alamat || '';
  openModal('modal-edit');
}
</script>

<?php require_once 'includes/layout_close.php'; ?>
