<?php
/**
 * TSLGC — Session Guard / Check
 *
 * Called by the dashboard JS to verify the user is still authenticated
 * on the server side.
 *
 * Response if valid:
 *   { "status": "ok", "data": { "name", "email", "code" } }
 *
 * Response if NOT logged in:
 *   HTTP 401 + { "status": "unauthorized", "message": "..." }
 *
 * Usage in frontend JS:
 *   fetch('../backend/session-check.php')
 *     .then(r => r.json())
 *     .then(res => { if (res.status !== 'ok') { redirect to login } })
 */

error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

// Match cookie params from login.php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

if (!empty($_SESSION['member_id'])) {
    echo json_encode([
        'status' => 'ok',
        'data'   => [
            'name'  => $_SESSION['member_name']  ?? '',
            'email' => $_SESSION['member_email'] ?? '',
            'code'  => $_SESSION['member_code']  ?? '',
            'plan'  => $_SESSION['member_plan']  ?? '',
        ],
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(401);
    echo json_encode([
        'status'  => 'unauthorized',
        'message' => 'Session expired. Please login again.',
    ]);
}
exit;
