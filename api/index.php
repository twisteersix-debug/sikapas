<?php
// ============================================================
//  SIPATEN — API Backend v2.1
//  File: api/index.php
//  Update: Filter satker per user, tambah KTP/NPWP/TASPEN
// ============================================================
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';

$body = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES)) {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $_POST;
}

$db  = getDB();
$uid = $_SESSION['user_id'];

// ── Helper: ambil satker_id user yang login (null = akses semua / admin) ──
function getUserSatkerId(): ?int {
    global $db, $uid;
    if (isAdmin()) return null; // admin = akses semua
    $stmt = $db->prepare("SELECT satker_id FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    return $row && $row['satker_id'] ? (int)$row['satker_id'] : null;
}

// ── Helper: tambahkan klausa filter satker ke query ──
function satkerClause(string $tableAlias = 'p'): string {
    $sid = getUserSatkerId();
    if ($sid === null) return ''; // admin: tidak dibatasi
    return " AND {$tableAlias}.satker_id = " . (int)$sid;
}

// ── Helper: cek apakah user boleh akses pegawai tertentu ──
function canAccessPegawai(int $pegawaiId): bool {
    global $db;
    $sid = getUserSatkerId();
    if ($sid === null) return true; // admin
    $stmt = $db->prepare("SELECT id FROM pegawai WHERE id=? AND satker_id=?");
    $stmt->execute([$pegawaiId, $sid]);
    return (bool)$stmt->fetch();
}

// ══════════════════════════════════════════════════════
//  RINGKASAN
// ══════════════════════════════════════════════════════
if ($module === 'ringkasan') {
    $sid = getUserSatkerId();

    if ($sid !== null) {
        // Non-admin: hanya data satker sendiri
        $total   = $db->prepare("SELECT COUNT(*) FROM pegawai WHERE satker_id=?"); $total->execute([$sid]); $total=$total->fetchColumn();
        $aktif   = $db->prepare("SELECT COUNT(*) FROM pegawai WHERE status='Aktif' AND satker_id=?"); $aktif->execute([$sid]); $aktif=$aktif->fetchColumn();
        $pensiun = $db->prepare("SELECT COUNT(*) FROM pegawai WHERE status='Pensiun' AND satker_id=?"); $pensiun->execute([$sid]); $pensiun=$pensiun->fetchColumn();
        $kgb     = $db->prepare("SELECT COUNT(*) FROM kenaikan_gaji k JOIN pegawai p ON p.id=k.pegawai_id WHERE MONTH(k.tmt)=MONTH(NOW()) AND YEAR(k.tmt)=YEAR(NOW()) AND p.satker_id=?"); $kgb->execute([$sid]); $kgb=$kgb->fetchColumn();
        $tunj    = $db->prepare("SELECT COUNT(*) FROM tunjangan t JOIN pegawai p ON p.id=t.pegawai_id WHERE t.status='Aktif' AND p.satker_id=?"); $tunj->execute([$sid]); $tunj=$tunj->fetchColumn();
        $arsip   = $db->query("SELECT COUNT(*) FROM arsip")->fetchColumn();
        $satker  = $db->prepare("SELECT s.nama AS satker, COUNT(p.id) AS total, SUM(p.status='Aktif') AS aktif FROM satker s LEFT JOIN pegawai p ON p.satker_id=s.id WHERE s.id=? GROUP BY s.id,s.nama"); $satker->execute([$sid]); $satker=$satker->fetchAll();
    } else {
        // Admin: semua data
        $total   = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
        $aktif   = $db->query("SELECT COUNT(*) FROM pegawai WHERE status='Aktif'")->fetchColumn();
        $pensiun = $db->query("SELECT COUNT(*) FROM pegawai WHERE status='Pensiun'")->fetchColumn();
        $kgb     = $db->query("SELECT COUNT(*) FROM kenaikan_gaji WHERE MONTH(tmt)=MONTH(NOW()) AND YEAR(tmt)=YEAR(NOW())")->fetchColumn();
        $tunj    = $db->query("SELECT COUNT(*) FROM tunjangan WHERE status='Aktif'")->fetchColumn();
        $arsip   = $db->query("SELECT COUNT(*) FROM arsip")->fetchColumn();
        $satker  = $db->query("SELECT s.nama AS satker, COUNT(p.id) AS total, SUM(p.status='Aktif') AS aktif FROM satker s LEFT JOIN pegawai p ON p.satker_id=s.id GROUP BY s.id,s.nama")->fetchAll();
    }

    // Pengingat pensiun dalam 1 tahun (sesuai satker)
    $ps_sql = "SELECT p.nama, p.nip, pg.tgl_pensiun, DATEDIFF(pg.tgl_pensiun, NOW()) AS hari FROM pengingat pg JOIN pegawai p ON p.id=pg.pegawai_id WHERE pg.tgl_pensiun BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 YEAR)";
    if ($sid !== null) $ps_sql .= " AND p.satker_id=$sid";
    $ps_sql .= " ORDER BY pg.tgl_pensiun LIMIT 5";
    $pensiun_soon = $db->query($ps_sql)->fetchAll();

    echo json_encode(['success'=>true,'data'=>[
        'total_pegawai'  => $total,
        'pegawai_aktif'  => $aktif,
        'pensiun'        => $pensiun,
        'kgb_bulan_ini'  => $kgb,
        'tunjangan_aktif'=> $tunj,
        'arsip'          => $arsip,
        'satker'         => $satker,
        'pensiun_soon'   => $pensiun_soon,
    ]]);
    exit;
}

// ══════════════════════════════════════════════════════
//  SATKER
// ══════════════════════════════════════════════════════
if ($module === 'satker') {
    $rows = $db->query("SELECT id, nama FROM satker ORDER BY nama")->fetchAll();
    echo json_encode(['success'=>true,'data'=>$rows]);
    exit;
}

// ══════════════════════════════════════════════════════
//  PEGAWAI
// ══════════════════════════════════════════════════════
if ($module === 'pegawai') {
    if ($action === 'list') {
        $q      = '%'.($_GET['q'] ?? '').'%';
        $satKl  = satkerClause('p');
        $sql    = "SELECT p.*, s.nama AS satker FROM pegawai p LEFT JOIN satker s ON s.id=p.satker_id WHERE (p.nama LIKE ? OR p.nip LIKE ? OR p.jabatan LIKE ?)$satKl ORDER BY p.nama LIMIT 200";
        $stmt   = $db->prepare($sql);
        $stmt->execute([$q, $q, $q]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
        exit;
    }

    if ($action === 'get') {
        $id = (int)$_GET['id'];
        if (!canAccessPegawai($id)) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $stmt = $db->prepare("SELECT p.*, s.nama AS satker_nama FROM pegawai p LEFT JOIN satker s ON s.id=p.satker_id WHERE p.id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        echo json_encode(['success'=>(bool)$row,'data'=>$row]);
        exit;
    }

    if ($action === 'save') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }

        $id        = $body['id'] ?? '';
        $nip       = trim($body['nip'] ?? '');
        $nama      = trim($body['nama'] ?? '');
        $jabatan   = trim($body['jabatan'] ?? '');
        $gol       = trim($body['golongan'] ?? '');
        $satker_id = $body['satker_id'] ?: null;
        $tmt       = $body['tmt_pns'] ?: null;
        $status    = $body['status'] ?? 'Aktif';
        $telp      = trim($body['no_telepon'] ?? '');
        $email     = trim($body['email'] ?? '');
        $tgl_lahir = $body['tanggal_lahir'] ?: null;
        $alamat    = trim($body['alamat'] ?? '');
        $no_ktp    = trim($body['no_ktp'] ?? '');
        $no_npwp   = trim($body['no_npwp'] ?? '');
        $no_taspen = trim($body['no_taspen'] ?? '');

        if (!$nip || !$nama) { echo json_encode(['success'=>false,'message'=>'NIP dan Nama wajib diisi']); exit; }

        // Non-admin: paksa satker_id ke satker milik user
        $sid = getUserSatkerId();
        if ($sid !== null) {
            $satker_id = $sid;
        }

        // Jika edit, cek akses
        if ($id && !canAccessPegawai((int)$id)) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }

        if ($id) {
            $stmt = $db->prepare("UPDATE pegawai SET nip=?,nama=?,jabatan=?,golongan=?,satker_id=?,tmt_pns=?,status=?,no_telepon=?,email=?,tanggal_lahir=?,alamat=?,no_ktp=?,no_npwp=?,no_taspen=? WHERE id=?");
            $stmt->execute([$nip,$nama,$jabatan,$gol,$satker_id,$tmt,$status,$telp,$email,$tgl_lahir,$alamat,$no_ktp,$no_npwp,$no_taspen,$id]);
            logActivity('Update', 'Pegawai', "Update data pegawai: $nama (NIP: $nip)");
            echo json_encode(['success'=>true,'message'=>'Data pegawai berhasil diupdate']);
        } else {
            $stmt = $db->prepare("INSERT INTO pegawai (nip,nama,jabatan,golongan,satker_id,tmt_pns,status,no_telepon,email,tanggal_lahir,alamat,no_ktp,no_npwp,no_taspen) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$nip,$nama,$jabatan,$gol,$satker_id,$tmt,$status,$telp,$email,$tgl_lahir,$alamat,$no_ktp,$no_npwp,$no_taspen]);
            logActivity('Tambah', 'Pegawai', "Tambah pegawai baru: $nama (NIP: $nip)");
            echo json_encode(['success'=>true,'message'=>'Pegawai berhasil ditambahkan']);
        }
        exit;
    }

    if ($action === 'delete') {
        if (!isAdmin()) { echo json_encode(['success'=>false,'message'=>'Hanya admin']); exit; }
        $id = (int)$_GET['id'];
        if (!canAccessPegawai($id)) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $stmt = $db->prepare("SELECT nama FROM pegawai WHERE id=?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        $db->prepare("DELETE FROM pegawai WHERE id=?")->execute([$id]);
        logActivity('Hapus', 'Pegawai', "Hapus pegawai: ".($p['nama']??''));
        echo json_encode(['success'=>true,'message'=>'Pegawai berhasil dihapus']);
        exit;
    }

    if ($action === 'upload_foto') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $file = $_FILES['foto'] ?? null;
        $pid  = $_POST['pegawai_id'] ?? '';
        if (!$file || $file['error'] !== 0) { echo json_encode(['success'=>false,'message'=>'File tidak valid']); exit; }
        if (!$pid) { echo json_encode(['success'=>false,'message'=>'ID pegawai tidak valid']); exit; }
        if (!canAccessPegawai((int)$pid)) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }

        if ($file['size'] > 2 * 1024 * 1024) { echo json_encode(['success'=>false,'message'=>'Ukuran foto maksimal 2MB']); exit; }
        $finfo  = new finfo(FILEINFO_MIME_TYPE);
        $mime   = $finfo->file($file['tmp_name']);
        $extMap = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($extMap[$mime])) { echo json_encode(['success'=>false,'message'=>'Format harus JPG, PNG, atau WEBP']); exit; }

        $cek = $db->prepare("SELECT foto FROM pegawai WHERE id=?");
        $cek->execute([$pid]);
        $lama = $cek->fetchColumn();
        if ($lama && file_exists('../'.$lama)) unlink('../'.$lama);

        $dir   = '../uploads/foto/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = 'pegawai_'.$pid.'_'.time().'.'.$extMap[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir.$fname)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan foto']); exit;
        }
        $path = 'uploads/foto/'.$fname;
        $db->prepare("UPDATE pegawai SET foto=? WHERE id=?")->execute([$path, $pid]);
        logActivity('Upload', 'Foto', "Upload foto pegawai ID: $pid");
        echo json_encode(['success'=>true,'message'=>'Foto berhasil diupload','foto'=>$path]);
        exit;
    }
}

// ══════════════════════════════════════════════════════
//  DOKUMEN PEGAWAI
// ══════════════════════════════════════════════════════
if ($module === 'dokumen') {
    if ($action === 'list') {
        $pid   = $_GET['pegawai_id'] ?? '';
        $jenis = $_GET['jenis'] ?? '';
        $satKl = satkerClause('p');
        $sql   = "SELECT d.*, p.nama AS nama_pegawai FROM dokumen_pegawai d JOIN pegawai p ON p.id=d.pegawai_id WHERE 1=1$satKl";
        $params= [];
        if ($pid)   { $sql .= " AND d.pegawai_id=?"; $params[] = $pid; }
        if ($jenis) { $sql .= " AND d.jenis_dokumen=?"; $params[] = $jenis; }
        $sql .= " ORDER BY d.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
        exit;
    }

    if ($action === 'upload') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $file  = $_FILES['file'] ?? null;
        $pid   = $_POST['pegawai_id'] ?? '';
        $jenis = $_POST['jenis_dokumen'] ?? '';
        $ket   = trim($_POST['keterangan'] ?? '');
        $tgl   = $_POST['tgl_berlaku'] ?: null;

        if (!$file || $file['error'] !== 0) { echo json_encode(['success'=>false,'message'=>'File tidak valid']); exit; }
        if (!$pid || !$jenis) { echo json_encode(['success'=>false,'message'=>'Pegawai dan jenis dokumen wajib diisi']); exit; }
        if (!canAccessPegawai((int)$pid)) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }

        $maxBytes = UPLOAD_MAX_MB * 1024 * 1024;
        if ($file['size'] > $maxBytes) { echo json_encode(['success'=>false,'message'=>'File terlalu besar (maks '.UPLOAD_MAX_MB.'MB)']); exit; }

        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','jpg','jpeg','png','docx','doc'];
        if (!in_array($ext, $allowed)) { echo json_encode(['success'=>false,'message'=>'Tipe file tidak diizinkan']); exit; }

        $fname = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $dir   = UPLOAD_DIR;
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (!move_uploaded_file($file['tmp_name'], $dir.$fname)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file']); exit;
        }

        $stmt = $db->prepare("INSERT INTO dokumen_pegawai (pegawai_id,jenis_dokumen,nama_file,path_file,keterangan,tgl_berlaku,uploaded_by) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$pid,$jenis,$file['name'],'uploads/dokumen/'.$fname,$ket,$tgl,$uid]);

        $peg = $db->prepare("SELECT nama FROM pegawai WHERE id=?");
        $peg->execute([$pid]);
        $pNama = $peg->fetchColumn();
        logActivity('Upload', 'Dokumen', "Upload $jenis untuk pegawai: $pNama");

        echo json_encode(['success'=>true,'message'=>'Dokumen berhasil diupload']);
        exit;
    }

    if ($action === 'delete') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $stmt = $db->prepare("SELECT d.path_file, d.jenis_dokumen, p.id AS pegawai_id FROM dokumen_pegawai d JOIN pegawai p ON p.id=d.pegawai_id WHERE d.id=?");
        $stmt->execute([$_GET['id']]);
        $row = $stmt->fetch();
        if ($row && !canAccessPegawai((int)$row['pegawai_id'])) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        if ($row && file_exists('../'.$row['path_file'])) unlink('../'.$row['path_file']);
        $db->prepare("DELETE FROM dokumen_pegawai WHERE id=?")->execute([$_GET['id']]);
        logActivity('Hapus', 'Dokumen', "Hapus dokumen: ".($row['jenis_dokumen']??''));
        echo json_encode(['success'=>true,'message'=>'Dokumen berhasil dihapus']);
        exit;
    }
}

// ══════════════════════════════════════════════════════
//  PENGINGAT PENSIUN
// ══════════════════════════════════════════════════════
if ($module === 'pengingat') {
    if ($action === 'list') {
        $q     = '%'.($_GET['q'] ?? '').'%';
        $sid   = getUserSatkerId();
        $satKl = $sid !== null ? " AND p.satker_id=$sid" : '';

        // Auto-insert pengingat untuk pegawai yang belum ada
        $db->query("
            INSERT IGNORE INTO pengingat (pegawai_id, tgl_pensiun, catatan)
            SELECT id, DATE_ADD(tanggal_lahir, INTERVAL 58 YEAR), 'Auto dari data pegawai'
            FROM pegawai
            WHERE tanggal_lahir IS NOT NULL AND status='Aktif'
            AND id NOT IN (SELECT pegawai_id FROM pengingat)
        ");

        // Auto-update jika tanggal lahir berubah
        $db->query("
            UPDATE pengingat pg
            JOIN pegawai p ON p.id = pg.pegawai_id
            SET pg.tgl_pensiun = DATE_ADD(p.tanggal_lahir, INTERVAL 58 YEAR)
            WHERE p.tanggal_lahir IS NOT NULL
        ");

        $stmt = $db->prepare("
            SELECT pg.*, p.nama, p.nip, p.jabatan, p.golongan, s.nama AS satker,
                   DATEDIFF(pg.tgl_pensiun, NOW()) AS sisa_hari,
                   TIMESTAMPDIFF(YEAR, p.tanggal_lahir, NOW()) AS umur_sekarang
            FROM pengingat pg
            JOIN pegawai p ON p.id=pg.pegawai_id
            LEFT JOIN satker s ON s.id=p.satker_id
            WHERE (p.nama LIKE ? OR p.nip LIKE ?)$satKl
            ORDER BY pg.tgl_pensiun ASC
        ");
        $stmt->execute([$q, $q]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
        exit;
    }

    if ($action === 'save') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $pid     = $body['pegawai_id'] ?? '';
        $tgl     = $body['tgl_pensiun'] ?? '';
        $catatan = trim($body['catatan'] ?? '');
        if (!canAccessPegawai((int)$pid)) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $stmt = $db->prepare("INSERT INTO pengingat (pegawai_id,tgl_pensiun,catatan) VALUES (?,?,?) ON DUPLICATE KEY UPDATE tgl_pensiun=?,catatan=?");
        $stmt->execute([$pid,$tgl,$catatan,$tgl,$catatan]);
        logActivity('Simpan', 'Pengingat', "Set pensiun pegawai ID: $pid tanggal: $tgl");
        echo json_encode(['success'=>true,'message'=>'Pengingat pensiun berhasil disimpan']);
        exit;
    }

    if ($action === 'delete') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $db->prepare("DELETE FROM pengingat WHERE id=?")->execute([$_GET['id']]);
        echo json_encode(['success'=>true,'message'=>'Pengingat berhasil dihapus']);
        exit;
    }
}

// ══════════════════════════════════════════════════════
//  ACTIVITY LOG
// ══════════════════════════════════════════════════════
if ($module === 'log') {
    if (!isAdmin()) { echo json_encode(['success'=>false,'message'=>'Hanya admin']); exit; }
    $q     = '%'.($_GET['q'] ?? '').'%';
    $modul = $_GET['modul'] ?? '';
    $sql   = "SELECT * FROM activity_log WHERE (user_nama LIKE ? OR aksi LIKE ? OR detail LIKE ?)";
    $params= [$q,$q,$q];
    if ($modul) { $sql .= " AND modul=?"; $params[] = $modul; }
    $sql .= " ORDER BY created_at DESC LIMIT 100";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

// ══════════════════════════════════════════════════════
//  KENAIKAN GAJI BERKALA
// ══════════════════════════════════════════════════════
if ($module === 'kgb') {
    if ($action === 'list') {
        $q     = '%'.($_GET['q'] ?? '').'%';
        $bulan = $_GET['bulan'] ?? '';
        $tahun = $_GET['tahun'] ?? '';
        $satKl = satkerClause('p');
        $sql   = "SELECT k.*, p.nama, p.nip FROM kenaikan_gaji k JOIN pegawai p ON p.id=k.pegawai_id WHERE (p.nama LIKE ? OR p.nip LIKE ?)$satKl";
        $params= [$q,$q];
        if ($bulan) { $sql .= " AND MONTH(k.tmt)=?"; $params[] = $bulan; }
        if ($tahun) { $sql .= " AND YEAR(k.tmt)=?";  $params[] = $tahun; }
        $sql .= " ORDER BY k.tmt DESC LIMIT 100";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
        exit;
    }

    if ($action === 'save') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $id    = $body['id'] ?? '';
        $nip   = trim($body['nip'] ?? '');
        $gol_l = $body['gol_lama'] ?? '';
        $gol_b = $body['gol_baru'] ?? '';
        $tmt   = $body['tmt'] ?? '';
        $no_sk = trim($body['no_sk'] ?? '');

        $sid = getUserSatkerId();
        $peg = $db->prepare("SELECT id FROM pegawai WHERE nip=?" . ($sid !== null ? " AND satker_id=$sid" : ""));
        $peg->execute([$nip]);
        $p = $peg->fetch();
        if (!$p) { echo json_encode(['success'=>false,'message'=>'NIP tidak ditemukan atau bukan satker Anda']); exit; }

        if ($id) {
            $stmt = $db->prepare("UPDATE kenaikan_gaji SET gol_lama=?,gol_baru=?,tmt=?,no_sk=? WHERE id=?");
            $stmt->execute([$gol_l,$gol_b,$tmt,$no_sk,$id]);
        } else {
            $stmt = $db->prepare("INSERT INTO kenaikan_gaji (pegawai_id,gol_lama,gol_baru,tmt,no_sk) VALUES (?,?,?,?,?)");
            $stmt->execute([$p['id'],$gol_l,$gol_b,$tmt,$no_sk]);
        }
        logActivity($id?'Update':'Tambah', 'KGB', "KGB NIP: $nip");
        echo json_encode(['success'=>true,'message'=>'KGB berhasil disimpan']);
        exit;
    }

    if ($action === 'delete') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $db->prepare("DELETE FROM kenaikan_gaji WHERE id=?")->execute([$_GET['id']]);
        echo json_encode(['success'=>true,'message'=>'KGB berhasil dihapus']);
        exit;
    }
}

// ══════════════════════════════════════════════════════
//  TUNJANGAN
// ══════════════════════════════════════════════════════
if ($module === 'tunjangan') {
    if ($action === 'list') {
        $q     = '%'.($_GET['q'] ?? '').'%';
        $satKl = satkerClause('p');
        $stmt  = $db->prepare("SELECT t.*, p.nama, p.nip, FORMAT(t.nominal,0) AS nominal_fmt FROM tunjangan t JOIN pegawai p ON p.id=t.pegawai_id WHERE (p.nama LIKE ? OR p.nip LIKE ?)$satKl ORDER BY t.created_at DESC LIMIT 100");
        $stmt->execute([$q,$q]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
        exit;
    }

    if ($action === 'save') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $id      = $body['id'] ?? '';
        $nip     = trim($body['nip'] ?? '');
        $jenis   = $body['jenis_tunjangan'] ?? '';
        $nominal = $body['nominal'] ?? 0;
        $periode = trim($body['periode'] ?? '');
        $status  = $body['status'] ?? 'Aktif';

        $sid = getUserSatkerId();
        $peg = $db->prepare("SELECT id FROM pegawai WHERE nip=?" . ($sid !== null ? " AND satker_id=$sid" : ""));
        $peg->execute([$nip]);
        $p = $peg->fetch();
        if (!$p) { echo json_encode(['success'=>false,'message'=>'NIP tidak ditemukan atau bukan satker Anda']); exit; }

        if ($id) {
            $stmt = $db->prepare("UPDATE tunjangan SET jenis_tunjangan=?,nominal=?,periode=?,status=? WHERE id=?");
            $stmt->execute([$jenis,$nominal,$periode,$status,$id]);
        } else {
            $stmt = $db->prepare("INSERT INTO tunjangan (pegawai_id,jenis_tunjangan,nominal,periode,status) VALUES (?,?,?,?,?)");
            $stmt->execute([$p['id'],$jenis,$nominal,$periode,$status]);
        }
        logActivity($id?'Update':'Tambah', 'Tunjangan', "Tunjangan NIP: $nip");
        echo json_encode(['success'=>true,'message'=>'Tunjangan berhasil disimpan']);
        exit;
    }

    if ($action === 'delete') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $db->prepare("DELETE FROM tunjangan WHERE id=?")->execute([$_GET['id']]);
        echo json_encode(['success'=>true,'message'=>'Tunjangan berhasil dihapus']);
        exit;
    }
}

// ══════════════════════════════════════════════════════
//  SLKS
// ══════════════════════════════════════════════════════
if ($module === 'slks') {
    if ($action === 'list') {
        $q     = '%'.($_GET['q'] ?? '').'%';
        $satKl = satkerClause('p');
        $stmt  = $db->prepare("SELECT s.*, p.nama, p.nip FROM slks s JOIN pegawai p ON p.id=s.pegawai_id WHERE (p.nama LIKE ? OR p.nip LIKE ?)$satKl ORDER BY s.periode DESC LIMIT 100");
        $stmt->execute([$q,$q]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
        exit;
    }

    if ($action === 'save') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $id       = $body['id'] ?? '';
        $nip      = trim($body['nip'] ?? '');
        $periode  = $body['periode'] ?? date('Y');
        $skp      = $body['nilai_skp'] ?? 0;
        $perilaku = $body['perilaku'] ?? 0;
        $total    = round(($skp + $perilaku) / 2, 2);
        $ket      = $total >= 91 ? 'Sangat Baik' : ($total >= 76 ? 'Baik' : ($total >= 61 ? 'Cukup' : 'Kurang'));

        $sid = getUserSatkerId();
        $peg = $db->prepare("SELECT id FROM pegawai WHERE nip=?" . ($sid !== null ? " AND satker_id=$sid" : ""));
        $peg->execute([$nip]);
        $p = $peg->fetch();
        if (!$p) { echo json_encode(['success'=>false,'message'=>'NIP tidak ditemukan atau bukan satker Anda']); exit; }

        if ($id) {
            $stmt = $db->prepare("UPDATE slks SET periode=?,nilai_skp=?,perilaku=?,total=?,keterangan=? WHERE id=?");
            $stmt->execute([$periode,$skp,$perilaku,$total,$ket,$id]);
        } else {
            $stmt = $db->prepare("INSERT INTO slks (pegawai_id,periode,nilai_skp,perilaku,total,keterangan) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$p['id'],$periode,$skp,$perilaku,$total,$ket]);
        }
        logActivity($id?'Update':'Tambah', 'SLKS', "SLKS NIP: $nip periode: $periode");
        echo json_encode(['success'=>true,'message'=>'SLKS berhasil disimpan']);
        exit;
    }

    if ($action === 'delete') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $db->prepare("DELETE FROM slks WHERE id=?")->execute([$_GET['id']]);
        echo json_encode(['success'=>true,'message'=>'SLKS berhasil dihapus']);
        exit;
    }
}

// ══════════════════════════════════════════════════════
//  ARSIP
// ══════════════════════════════════════════════════════
if ($module === 'arsip') {
    if ($action === 'list') {
        $q    = '%'.($_GET['q'] ?? '').'%';
        $stmt = $db->prepare("SELECT * FROM arsip WHERE nama_file LIKE ? ORDER BY created_at DESC LIMIT 100");
        $stmt->execute([$q]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
        exit;
    }

    if ($action === 'upload') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== 0) { echo json_encode(['success'=>false,'message'=>'File tidak valid']); exit; }
        $maxBytes = UPLOAD_MAX_MB * 1024 * 1024;
        if ($file['size'] > $maxBytes) { echo json_encode(['success'=>false,'message'=>'File terlalu besar']); exit; }
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','xlsx','xls','docx','doc','png','jpg','jpeg'];
        if (!in_array($ext, $allowed)) { echo json_encode(['success'=>false,'message'=>'Tipe file tidak diizinkan']); exit; }
        $fname = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $dir   = UPLOAD_DIR;
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (!move_uploaded_file($file['tmp_name'], $dir.$fname)) { echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file']); exit; }
        $tipe = strtoupper($ext);
        $kb   = round($file['size'] / 1024);
        $stmt = $db->prepare("INSERT INTO arsip (nama_file,path_file,tipe_file,ukuran_kb,uploaded_by) VALUES (?,?,?,?,?)");
        $stmt->execute([$file['name'],'uploads/dokumen/'.$fname,$tipe,$kb,$uid]);
        logActivity('Upload', 'Arsip', "Upload file: ".$file['name']);
        echo json_encode(['success'=>true,'message'=>'File berhasil diupload']);
        exit;
    }

    if ($action === 'delete') {
        if (!canEdit()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit; }
        $stmt = $db->prepare("SELECT path_file FROM arsip WHERE id=?");
        $stmt->execute([$_GET['id']]);
        $row = $stmt->fetch();
        if ($row && file_exists('../'.$row['path_file'])) unlink('../'.$row['path_file']);
        $db->prepare("DELETE FROM arsip WHERE id=?")->execute([$_GET['id']]);
        logActivity('Hapus', 'Arsip', "Hapus file arsip ID: ".$_GET['id']);
        echo json_encode(['success'=>true,'message'=>'File berhasil dihapus']);
        exit;
    }
}

// ══════════════════════════════════════════════════════
//  PENCARIAN GLOBAL
// ══════════════════════════════════════════════════════
if ($module === 'search') {
    $q     = '%'.($_GET['q'] ?? '').'%';
    $type  = $_GET['type'] ?? 'all';
    $satKl = satkerClause('p');
    $result= [];

    if ($type === 'all' || $type === 'pegawai') {
        $stmt = $db->prepare("SELECT id, nip, nama, jabatan, golongan, status FROM pegawai p WHERE (nama LIKE ? OR nip LIKE ? OR jabatan LIKE ?)$satKl LIMIT 10");
        $stmt->execute([$q,$q,$q]);
        $result['pegawai'] = $stmt->fetchAll();
    }
    if ($type === 'all' || $type === 'satker') {
        $sid = getUserSatkerId();
        $sql = "SELECT p.id, p.nip, p.nama, s.nama AS satker FROM pegawai p JOIN satker s ON s.id=p.satker_id WHERE s.nama LIKE ?";
        if ($sid !== null) $sql .= " AND p.satker_id=$sid";
        $sql .= " LIMIT 10";
        $stmt = $db->prepare($sql);
        $stmt->execute([$q]);
        $result['satker'] = $stmt->fetchAll();
    }
    if ($type === 'all' || $type === 'dokumen') {
        $satKlD = satkerClause('p');
        $stmt   = $db->prepare("SELECT d.id, d.jenis_dokumen, d.nama_file, p.nama AS nama_pegawai FROM dokumen_pegawai d JOIN pegawai p ON p.id=d.pegawai_id WHERE (d.jenis_dokumen LIKE ? OR d.nama_file LIKE ?)$satKlD LIMIT 10");
        $stmt->execute([$q,$q]);
        $result['dokumen'] = $stmt->fetchAll();
    }

    echo json_encode(['success'=>true,'data'=>$result]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Module atau action tidak ditemukan']);
