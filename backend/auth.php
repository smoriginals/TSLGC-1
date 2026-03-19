<?php
/**
 * UnifyHub – Authentication Handler
 * Handles: admin_login, franchise_login, logout
 *
 * POST body (JSON): { "action": "...", ... }
 */

require_once __DIR__ . '/config.php';

session_start();

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = trim($input['action'] ?? '');

switch ($action) {

    // -------------------------------------------------------
    case 'admin_login':
    // -------------------------------------------------------
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            jsonResponse('error', 'Username aur password dono required hain');
        }

        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM admins WHERE username = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($password, $admin['password_hash'])) {
                jsonResponse('error', 'Invalid credentials');
            }

            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['role']       = $admin['role'];

            jsonResponse('success', 'Login successful', ['role' => $admin['role']]);
        } catch (PDOException $e) {
            jsonResponse('error', 'Login failed. Please try again.');
        }
        break;

    // -------------------------------------------------------
    case 'franchise_login':
    // -------------------------------------------------------
        $identifier = trim($input['identifier'] ?? '');
        $password   = $input['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            jsonResponse('error', 'ID aur password required hain');
        }

        // Identifier can be franchise ID (FR-XXXX) or mobile number
        $isMobile = preg_match('/^[6-9]\d{9}$/', $identifier);

        try {
            $pdo  = getDB();
            if ($isMobile) {
                $stmt = $pdo->prepare(
                    'SELECT f.id, f.franchise_code, m.full_name, m.password_hash, f.status
                     FROM franchises f
                     JOIN members m ON m.id = f.member_id
                     WHERE m.mobile = ? AND f.status = "active" LIMIT 1'
                );
            } else {
                $frId = strtoupper($identifier);
                $stmt = $pdo->prepare(
                    'SELECT f.id, f.franchise_code, m.full_name, m.password_hash, f.status
                     FROM franchises f
                     JOIN members m ON m.id = f.member_id
                     WHERE f.franchise_code = ? AND f.status = "active" LIMIT 1'
                );
            }
            $stmt->execute([$identifier]);
            $franchise = $stmt->fetch();

            if (!$franchise || !password_verify($password, $franchise['password_hash'])) {
                jsonResponse('error', 'Invalid credentials or inactive account');
            }

            session_regenerate_id(true);
            $_SESSION['franchise_id']   = $franchise['id'];
            $_SESSION['franchise_code'] = $franchise['franchise_code'];
            $_SESSION['fr_name']        = $franchise['full_name'];

            jsonResponse('success', 'Login successful');
        } catch (PDOException $e) {
            jsonResponse('error', 'Login failed. Please try again.');
        }
        break;

    // -------------------------------------------------------
    case 'logout':
    // -------------------------------------------------------
        session_destroy();
        jsonResponse('success', 'Logged out');
        break;

    default:
        http_response_code(400);
        jsonResponse('error', 'Invalid action');
}
