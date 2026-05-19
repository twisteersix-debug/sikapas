# SIPATEN — Panduan Instalasi & Penggunaan

## Cara Cepat (XAMPP / Lokal)

### 1. Persiapan
- Install XAMPP: https://www.apachefriends.org
- Pastikan Apache & MySQL sudah jalan (tombol Start di XAMPP Control Panel)

### 2. Salin File
```
Salin folder SIPATEN/ ke:
  C:\xampp\htdocs\sipaten\
```

### 3. Buat Database
- Buka browser → http://localhost/phpmyadmin
- Klik "New" → buat database: **sipaten_db**
- Klik tab "Import" → pilih file **database.sql** → klik "Go"

### 4. Konfigurasi Database
Edit file `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sipaten_db');
define('DB_USER', 'root');   // sesuaikan
define('DB_PASS', '');       // sesuaikan (kosong jika tidak ada password)
```

### 5. Buka Aplikasi
- Buka browser → http://localhost/sipaten
- Login dengan:
  - **Username**: admin
  - **Password**: admin123

---

## Struktur Folder
```
sipaten/
├── index.php          ← Halaman utama (dashboard)
├── login.php          ← Halaman login
├── logout.php         ← Proses logout
├── database.sql       ← Schema + data awal
├── .htaccess          ← Konfigurasi Apache
├── includes/
│   └── config.php     ← Konfigurasi DB & helper
├── api/
│   └── index.php      ← Semua endpoint API (CRUD)
└── uploads/
    └── dokumen/       ← File yang diupload
```

## Fitur
| Modul | Fungsi |
|-------|--------|
| Ringkasan | Statistik & daftar satuan kerja (real-time dari DB) |
| Data Pegawai | CRUD pegawai + pencarian NIP/nama |
| Kenaikan Gaji | Pengajuan & tracking status KGB |
| Tunjangan | Input & kelola tunjangan pegawai |
| SLKS/SKP | Input nilai + hitung otomatis predikat |
| Arsip File | Upload, download, hapus dokumen |

## Ganti Password Admin
Jalankan query di phpMyAdmin:
```sql
UPDATE users
SET password = '$2y$10$HASH_BARU'
WHERE username = 'admin';
```
Untuk generate hash: https://bcrypt-generator.com (cost: 10)

## Hosting Online (jika diinginkan)
1. Upload semua file ke server via FTP/cPanel File Manager
2. Import `database.sql` di phpMyAdmin hosting
3. Edit `includes/config.php` sesuai kredensial hosting
4. Akses via domain/subdomain Anda

## Teknologi
- **Backend**: PHP 8.0+ (native, tanpa framework)
- **Database**: MySQL 5.7+ / MariaDB 10+
- **Frontend**: HTML5 + CSS3 + Vanilla JS (fetch API)
- **Web Server**: Apache (XAMPP) / Nginx
- **Autentikasi**: Session PHP + bcrypt password

## Keamanan
- Password di-hash dengan bcrypt
- Semua query pakai PDO prepared statement (aman dari SQL injection)
- Session httponly cookie
- Validasi file upload (tipe & ukuran)
- Proteksi folder sensitif via .htaccess
