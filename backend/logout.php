<?php
/**
 * TSLGC — Logout
 *
 * Destroys the PHP session completely and redirects to the login page.
 * Can also be called via AJAX — returns JSON if Accept: application/json
 * or X-Requested-With header is present.
 *
 * No authentication required — always succeeds.
 */

error_reporting(0);
ini_set('display_errors', '0');

// Session cookie params must match exactly what was set in login.php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Wipe all session variables
$_SESSION = [];

// Expire the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']
    );
}

// Destroy session on server
session_destroy();

// ── Respond: JSON for AJAX callers, redirect for direct browser visits ────────
$wantsJson =
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
} else {
    // Determine correct redirect path (works from any subfolder depth)
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $depth  = substr_count(str_replace('\\', '/', $script), '/') - 1;
    $up     = str_repeat('../', max(0, $depth));
    header('Location: ' . $up . 'join.html');
}
exit;
