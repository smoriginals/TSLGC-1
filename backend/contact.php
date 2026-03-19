<?php
/**
 * UnifyHub – Contact Form Handler
 * Records contact form submissions to the database and optionally sends an email.
 *
 * POST body (JSON): { name, phone, email, subject, message }
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse('error', 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$name    = trim($input['name']    ?? '');
$phone   = trim($input['phone']   ?? '');
$email   = trim($input['email']   ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

// --- Validation ---
if (!$name || !$email || !$message) {
    jsonResponse('error', 'Name, email aur message required hain');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Valid email address required');
}

if ($phone && !isValidMobile($phone)) {
    jsonResponse('error', 'Valid 10-digit mobile number required');
}

if (strlen($message) > 2000) {
    jsonResponse('error', 'Message 2000 characters se zyada nahi ho sakta');
}

// Sanitize
$name    = htmlspecialchars($name,    ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
$phone   = htmlspecialchars($phone,   ENT_QUOTES, 'UTF-8');

// --- Rate limiting (simple: max 3 messages per IP per hour) ---
$ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$hour = date('Y-m-d H');

try {
    $pdo = getDB();

    $rateCheck = $pdo->prepare(
        'SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND DATE_FORMAT(created_at, "%Y-%m-%d %H") = ?'
    );
    $rateCheck->execute([$ip, $hour]);
    if ((int) $rateCheck->fetchColumn() >= 3) {
        jsonResponse('error', 'Aapne bahut zyada messages bheje hain. Kuch der baad try karein.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO contact_messages (name, phone, email, subject, message, ip_address, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$name, $phone, $email, $subject, $message, $ip]);

    // Optional: send notification email to admin
    // mail('admin@unifyhub.in', "[UnifyHub Contact] $subject", "From: $name\nPhone: $phone\nEmail: $email\n\n$message");

    jsonResponse('success', 'Aapka message mil gaya! Hum jald hi aapse contact karenge.');

} catch (PDOException $e) {
    jsonResponse('error', 'Message send karne mein error aaya. Please try again.');
}
