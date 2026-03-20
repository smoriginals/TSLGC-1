<?php
/**
 * TSLGC — Member Login
 *
 * POST JSON: { email, password }
 * Response:  { status, message, [data: { name, email, rank, plan, redirect }] }
 *
 * Security:
 *  - Session cookie is Secure + HttpOnly (cannot be read by JS or sent over HTTP)
 *  - session_regenerate_id() prevents session fixation attacks
 *  - Generic error message for wrong email/password (prevents user enumeration)
 *  - PDO prepared statements throughout (no SQL injection)
 */

require_once __DIR__ . '/config.php';

// ── Secure session cookie — MUST be set BEFORE session_start() ───────────────
session_set_cookie_params([
    'lifetime' => 0,            // Expire when browser closes
    'path'     => '/',
    'domain'   => '',           // Current domain only
    'secure'   => true,         // HTTPS only — cookie not sent over HTTP
    'httponly' => true,         // Not accessible via JavaScript (blocks XSS theft)
    'samesite' => 'Strict',     // Not sent on cross-site requests (CSRF protection)
]);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse('error', 'Method not allowed');
}

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$email    = strtolower(trim($input['email']    ?? ''));
$password = $input['password'] ?? '';

// ── Input validation ──────────────────────────────────────────────────────────
if ($email === '' || $password === '') {
    jsonResponse('error', 'Email and password are required');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Enter a valid email address');
}

try {
    $pdo  = getDB();

    // ── Look up member by email ───────────────────────────────────────────────
    $stmt = $pdo->prepare(
        'SELECT id, member_code, full_name, email, password_hash, plan, status
         FROM members WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // ── Verify password (same message for "not found" + "wrong password"
    //    to prevent user enumeration attacks) ──────────────────────────────────
    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse('error', 'Invalid Email or Password');
    }

    // ── Block pending / inactive accounts ────────────────────────────────────
    if ($user['status'] === 'pending') {
        jsonResponse('error', 'Your account is pending approval. Our team will activate it shortly.');
    }
    if ($user['status'] === 'rejected' || $user['status'] === 'inactive') {
        jsonResponse('error', 'Your account has been deactivated. Please contact support.');
    }

    // ── Regenerate session ID (prevents session fixation) ────────────────────
    session_regenerate_id(true);

    // ── Store member info in session ──────────────────────────────────────────
    $_SESSION['member_id']    = (int)  $user['id'];
    $_SESSION['member_code']  =        $user['member_code'];
    $_SESSION['member_name']  =        $user['full_name'];
    $_SESSION['member_email'] =        $user['email'];
    $_SESSION['member_plan']  =        $user['plan'];
    $_SESSION['login_time']   =        time();

    // ── Also set franchise_id if this member has a franchise record ───────────
    // (required by franchise-api.php for dashboard stats)
    $frStmt = $pdo->prepare(
        'SELECT id FROM franchises WHERE member_id = ? AND status = \'active\' LIMIT 1'
    );
    $frStmt->execute([(int) $user['id']]);
    $franchise = $frStmt->fetch();
    if ($franchise) {
        $_SESSION['franchise_id'] = (int) $franchise['id'];
    }

    // ── Determine rank from team size ─────────────────────────────────────────
    $teamStmt = $pdo->prepare('SELECT COUNT(*) FROM members WHERE referrer_id = ? LIMIT 1');
    $teamStmt->execute([(int) $user['id']]);
    $teamSize = (int) $teamStmt->fetchColumn();
    $rank = 'Bronze';
    if ($teamSize >= 500)     $rank = 'Legend';
    elseif ($teamSize >= 250) $rank = 'Diamond';
    elseif ($teamSize >= 100) $rank = 'Gold';
    elseif ($teamSize >= 25)  $rank = 'Silver';

    // ── Success — do NOT return password_hash or any sensitive field ──────────
    jsonResponse('success', 'Welcome Back', [
        'name'     => $user['full_name'],
        'email'    => $user['email'],
        'code'     => $user['member_code'],
        'rank'     => $rank,
        'plan'     => $user['plan'],
        'redirect' => 'franchise/dashboard.html',
    ]);

} catch (PDOException $e) {
    jsonResponse('error', 'Login failed. Please try again.');
}
