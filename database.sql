-- ============================================================
--  SIPATEN — Sistem Informasi Pegawai Tenaga Negeri
--  Database Schema v1.0
--  Jalankan di phpMyAdmin atau: mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS sipaten_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sipaten_db;

-- ── TABEL USERS (login admin) ──────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(100) NOT NULL,
  username    VARCHAR(50)  NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,   -- bcrypt
  role        ENUM('admin','operator','viewer') DEFAULT 'operator',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── TABEL SATUAN KERJA ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS satker (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  nama  VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ── TABEL PEGAWAI ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pegawai (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nip         VARCHAR(20)  NOT NULL UNIQUE,
  nama        VARCHAR(150) NOT NULL,
  jabatan     VARCHAR(150),
  golongan    VARCHAR(10),
  satker_id   INT,
  tmt_pns     DATE,
  status      ENUM('Aktif','Pensiun','Dalam Proses') DEFAULT 'Aktif',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (satker_id) REFERENCES satker(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── TABEL KENAIKAN GAJI BERKALA ────────────────────────────
CREATE TABLE IF NOT EXISTS kenaikan_gaji (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  pegawai_id  INT NOT NULL,
  gol_lama    VARCHAR(10) NOT NULL,
  gol_baru    VARCHAR(10) NOT NULL,
  tmt         DATE        NOT NULL,
  no_sk       VARCHAR(100),
  status      ENUM('Selesai','Proses','Belum') DEFAULT 'Belum',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── TABEL TUNJANGAN ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tunjangan (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  pegawai_id       INT NOT NULL,
  jenis_tunjangan  VARCHAR(100) NOT NULL,
  nominal          DECIMAL(15,2) NOT NULL DEFAULT 0,
  periode          VARCHAR(20),     -- contoh: "Mei 2026"
  status           ENUM('Aktif','Verifikasi','Nonaktif') DEFAULT 'Aktif',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── TABEL SLKS / SKP ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS slks (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  pegawai_id  INT NOT NULL,
  periode     YEAR NOT NULL,
  nilai_skp   DECIMAL(5,2),
  perilaku    DECIMAL(5,2),
  total       DECIMAL(5,2),
  keterangan  VARCHAR(50),   -- Baik, Sangat Baik, Cukup, dst
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── TABEL ARSIP FILE ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS arsip (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nama_file    VARCHAR(255) NOT NULL,
  path_file    VARCHAR(500) NOT NULL,
  tipe_file    VARCHAR(20),    -- PDF, Excel, Word, dst
  ukuran_kb    INT,
  uploaded_by  INT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ═══════════════════════════════════════════════════════════
-- DATA AWAL (SEED)
-- ═══════════════════════════════════════════════════════════

-- Admin default: username=admin, password=Admin123!
-- Hash bcrypt untuk "Admin123!"
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lniW', 'admin'),
('Operator Satu', 'operator1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lniW', 'operator');

-- Satuan Kerja
INSERT INTO satker (nama) VALUES
('Kantor Wilayah'),
('Lapas Kelas I Surabaya'),
('Lapas Kelas II A Malang'),
('Rutan Kelas I Surabaya'),
('Bapas Kelas I Surabaya'),
('Lapas Perempuan Malang');

-- Pegawai
INSERT INTO pegawai (nip, nama, jabatan, golongan, satker_id, tmt_pns, status) VALUES
('198501012010011001', 'Budi Santoso, S.H.',  'Kepala Seksi',       'III/c', 1, '2010-01-01', 'Aktif'),
('197808152005012002', 'Dewi Rahayu, M.M.',   'Kepala Sub Bagian',  'III/d', 1, '2005-01-12', 'Aktif'),
('199002282015031003', 'Ahmad Fauzan',         'Staf Pelaksana',     'II/c',  2, '2015-03-28', 'Aktif'),
('198312122008011004', 'Sri Wahyuni, S.Pd.',   'Penyuluh',           'III/b', 5, '2008-01-12', 'Aktif'),
('197603182000031005', 'Heru Wibowo',          'Kepala Rutan',       'IV/a',  4, '2000-03-18', 'Aktif'),
('196809251990011006', 'Suparman, S.H.',       'Kepala Lapas',       'IV/b',  3, '1990-01-25', 'Pensiun'),
('199507082020122007', 'Rizky Amelia, S.T.',   'Pranata Komputer',   'III/a', 1, '2020-12-08', 'Aktif'),
('200101012022031008', 'Muhammad Haikal',      'CPNS',               'II/a',  6, '2022-03-01', 'Dalam Proses');

-- Kenaikan Gaji Berkala
INSERT INTO kenaikan_gaji (pegawai_id, gol_lama, gol_baru, tmt, no_sk, status) VALUES
(1, 'III/b', 'III/c', '2026-04-01', 'SK/001/IV/2026', 'Selesai'),
(3, 'II/b',  'II/c',  '2026-05-01', 'SK/002/V/2026',  'Proses'),
(4, 'III/a', 'III/b', '2026-06-01', NULL,              'Proses'),
(7, 'II/d',  'III/a', '2026-07-01', NULL,              'Belum');

-- Tunjangan
INSERT INTO tunjangan (pegawai_id, jenis_tunjangan, nominal, periode, status) VALUES
(1, 'Tunjangan Kinerja',  5500000, 'Mei 2026', 'Aktif'),
(2, 'Tunjangan Kinerja',  6200000, 'Mei 2026', 'Aktif'),
(3, 'Tunjangan Umum',     2700000, 'Mei 2026', 'Aktif'),
(4, 'Tunjangan Khusus',   3100000, 'Mei 2026', 'Verifikasi'),
(5, 'Tunjangan Jabatan',  4800000, 'Mei 2026', 'Aktif');

-- SLKS
INSERT INTO slks (pegawai_id, periode, nilai_skp, perilaku, total, keterangan) VALUES
(1, 2025, 88, 89, 88.5, 'Baik'),
(2, 2025, 92, 91, 91.5, 'Sangat Baik'),
(3, 2025, 75, 77, 76.0, 'Cukup'),
(4, 2025, 85, 83, 84.0, 'Baik'),
(5, 2025, 90, 92, 91.0, 'Sangat Baik');

-- Arsip
INSERT INTO arsip (nama_file, path_file, tipe_file, ukuran_kb, uploaded_by) VALUES
('SK Kepala Kantor Wilayah 2026.pdf',        'uploads/dokumen/sk_kanwil_2026.pdf',     'PDF',   2458,  1),
('Daftar Nominatif Pegawai Mei 2026.xlsx',   'uploads/dokumen/nominatif_mei2026.xlsx', 'Excel', 1126,  1),
('SK Kenaikan Gaji Berkala April 2026.pdf',  'uploads/dokumen/sk_kgb_apr2026.pdf',     'PDF',   830,   1),
('Laporan SKP Triwulan I 2026.docx',         'uploads/dokumen/skp_triw1_2026.docx',    'Word',  560,   1),
('Data Tunjangan Pegawai 2026.xlsx',         'uploads/dokumen/tunjangan_2026.xlsx',    'Excel', 980,   1);
