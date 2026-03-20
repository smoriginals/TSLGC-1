<?php
/**
 * TSLGC – Database Configuration
 * Update DB_USER and DB_PASS before deploying on Hostinger.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'tslgc_db');
define('DB_USER', 'your_db_user');       // ← Change this
define('DB_PASS', 'your_db_password');   // ← Change this
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'TSLGC');
define('APP_URL', 'https://yourdomain.com'); // ← Change this

/**
 * Returns a PDO instance with strict error mode.
 * Exits with a JSON error on connection failure.
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) { return $pdo; }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
}

/**
 * Sends a JSON response and exits.
 */
function jsonResponse(string $status, string $message, array $data = []): void {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['status' => $status, 'message' => $message];
    if (!empty($data)) { $response['data'] = $data; }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validates a 10-digit Indian mobile number (starts with 6–9).
 */
function isValidMobile(string $mobile): bool {
    return (bool) preg_match('/^[6-9]\d{9}$/', $mobile);
}

// Global JSON output header
header('Content-Type: application/json; charset=utf-8');

// CORS – adjust origin as needed
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
