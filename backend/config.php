<?php
/**
 * TSLGC — Database Configuration
 * !! Update DB_USER, DB_PASS, DB_NAME, APP_URL before deploying on Hostinger !!
 *
 * In Hostinger hPanel → Databases → MySQL Databases, create:
 *   Database name : tslgc_db          (copy exact name shown, it's prefixed)
 *   Username      : your_db_user      (copy exact username shown)
 *   Password      : your_db_password  (the password you set)
 */

// ── Production: suppress all error output to browser ─────────────────────────
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors',     '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

// ── Database credentials ──────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'tslgc_db');          // ← Your Hostinger DB name (e.g. u123456789_tslgc_db)
define('DB_USER',    'your_db_user');      // ← Your Hostinger DB username
define('DB_PASS',    'your_db_password');  // ← Your Hostinger DB password
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'TSLGC');
define('APP_URL',  'https://yourdomain.com'); // ← Your live domain (no trailing slash)

// ── JSON header (sent for every backend response) ────────────────────────────
header('Content-Type: application/json; charset=utf-8');

// ── CORS — allow only same origin ────────────────────────────────────────────
$allowedOrigin = rtrim(APP_URL, '/');
$requestOrigin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';
if ($requestOrigin === $allowedOrigin) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── PDO singleton ─────────────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) { return $pdo; }

    $dsn  = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Service temporarily unavailable. Please try later.']);
        exit;
    }
}

// ── Send JSON and exit ────────────────────────────────────────────────────────
function jsonResponse(string $status, string $message, array $data = []): void {
    $r = ['status' => $status, 'message' => $message];
    if (!empty($data)) { $r['data'] = $data; }
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Validate 10-digit Indian mobile ──────────────────────────────────────────
function isValidMobile(string $mobile): bool {
    return (bool) preg_match('/^[6-9]\d{9}$/', $mobile);
}
