<?php
// ============================================================
// SIPATEN — Patch API: Upload Foto Pegawai
// ============================================================
// Tambahkan fungsi ini ke dalam file api/index.php
// di bagian modul 'pegawai', dalam fungsi handlePegawai()
//
// Cari baris:  'delete' => pegawaiDelete($get, $pdo),
// Tambahkan:   'upload_foto' => pegawaiUploadFoto($_FILES, $post, $pdo),
// ============================================================

// ── Tambah di dalam match handlePegawai(): ─────────────────
//
//   'upload_foto' => pegawaiUploadFoto($_FILES, $_POST, $pdo),
//
// ── Tambah fungsi ini di bawah fungsi pegawaiDelete(): ─────

function pegawaiUploadFoto(array $files, array $post, PDO $pdo): array
{
    if (empty($files['foto'])) {
        return ['success' => false, 'message' => 'File foto tidak ditemukan'];
    }

    $id = !empty($post['pegawai_id']) ? (int)$post['pegawai_id'] : 0;
    if (!$id) {
        return ['success' => false, 'message' => 'ID pegawai tidak valid'];
    }

    $file     = $files['foto'];
    $maxSize  = 2 * 1024 * 1024; // 2MB
    $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
    $extMap   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    // Validasi
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload gagal, coba lagi'];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran foto maksimal 2MB'];
    }

    // Validasi MIME type asli (bukan dari client)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, $allowed)) {
        return ['success' => false, 'message' => 'Format file harus JPG, PNG, atau WEBP'];
    }

    // Buat folder uploads/foto jika belum ada
    $uploadDir = __DIR__ . '/../uploads/foto/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Hapus foto lama jika ada
    $cek = $pdo->prepare('SELECT foto FROM pegawai WHERE id = :id LIMIT 1');
    $cek->execute([':id' => $id]);
    $row = $cek->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['foto']) {
        $fotoLama = __DIR__ . '/../' . $row['foto'];
        if (file_exists($fotoLama)) {
            unlink($fotoLama);
        }
    }

    // Simpan file baru dengan nama unik
    $ext      = $extMap[$mimeType];
    $fileName = 'pegawai_' . $id . '_' . time() . '.' . $ext;
    $filePath = $uploadDir . $fileName;
    $dbPath   = 'uploads/foto/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => false, 'message' => 'Gagal menyimpan file foto'];
    }

    // Update path foto di database
    $stmt = $pdo->prepare('UPDATE pegawai SET foto = :foto WHERE id = :id');
    $stmt->execute([':foto' => $dbPath, ':id' => $id]);

    return ['success' => true, 'message' => 'Foto berhasil diupload', 'foto' => $dbPath];
}

// ============================================================
// SQL — Tambah kolom foto ke tabel pegawai
// Jalankan di Railway Query console:
// ============================================================
/*
ALTER TABLE pegawai
  ADD COLUMN IF NOT EXISTS foto VARCHAR(255) NULL
  COMMENT 'Path foto pegawai' AFTER alamat;
*/
