<?php
// ============================================================
//  SIPATEN — Konfigurasi (support Railway ENV + lokal XAMPP)
// ============================================================
define('DB_HOST',    getenv('MYSQLHOST')    ?: getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('MYSQLPORT')    ?: getenv('DB_PORT')    ?: '3307');
define('DB_NAME',    getenv('MYSQLDATABASE')?: getenv('DB_NAME')    ?: 'sipaten_db');
define('DB_USER',    getenv('MYSQLUSER')    ?: getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD')?: getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');
define('APP_NAME',   'SIPATEN');
define('APP_VERSION','2.0');
define('UPLOAD_DIR', __DIR__ . '/../uploads/dokumen/');
define('UPLOAD_MAX_MB', 10);
define('SESSION_LIFETIME', 3600 * 8);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn  = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
                        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]));
        }
    }
    return $pdo;
}

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params(['lifetime'=>SESSION_LIFETIME,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
        session_start();
    }
}

function requireLogin(): void {
    startSession();
    if (empty($_SESSION['user_id'])) {
        if (isAjax()) { http_response_code(401); die(json_encode(['success'=>false,'message'=>'Sesi habis.'])); }
        header('Location: login.php'); exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        if (isAjax()) { http_response_code(403); die(json_encode(['success'=>false,'message'=>'Hanya admin.'])); }
        header('Location: index.php'); exit;
    }
}

function currentUser(): array  { return $_SESSION['user'] ?? []; }
function isAdmin(): bool       { return ($_SESSION['user']['role'] ?? '') === 'admin'; }
function isAjax(): bool        { return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'; }

function jsonResponse(bool $ok, string $msg, array $data=[]): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success'=>$ok,'message'=>$msg], $data));
    exit;
}

function sanitize(string $v): string { return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8'); }
function formatRupiah(float $n): string { return 'Rp '.number_format($n,0,',','.'); }
// golonganOptions() sengaja dihapus dari sini — sudah ada di index.php
