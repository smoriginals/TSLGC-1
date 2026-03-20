<?php
/**
 * TSLGC — Member Signup
 *
 * POST JSON: { full_name, mobile, email, password, city, state, referral_id, plan }
 * Response:  { status, message, [data: { member_id, member_code }] }
 *
 * Password is hashed with PASSWORD_BCRYPT — never stored as plain text.
 * New members get status='pending' and need admin approval before login.
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse('error', 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// ── Extract & trim all fields ─────────────────────────────────────────────────
$fullName   = trim($input['full_name']   ?? '');
$mobile     = trim($input['mobile']      ?? '');
$email      = strtolower(trim($input['email']     ?? ''));
$password   = $input['password']         ?? '';   // raw — will be hashed below
$city       = trim($input['city']        ?? '');
$state      = trim($input['state']       ?? '');
$referralId = trim($input['referral_id'] ?? '');
$plan       = trim($input['plan']        ?? '');

// ── Required field validation ─────────────────────────────────────────────────
if ($fullName === '' || $mobile === '' || $email === '' || $password === '' ||
    $city === '' || $state === '' || $plan === '') {
    jsonResponse('error', 'All required fields must be filled');
}

if (strlen($fullName) < 2 || strlen($fullName) > 120) {
    jsonResponse('error', 'Full name must be 2–120 characters');
}

if (!isValidMobile($mobile)) {
    jsonResponse('error', 'Enter a valid 10-digit Indian mobile number');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Enter a valid email address');
}

if (strlen($password) < 6) {
    jsonResponse('error', 'Password must be at least 6 characters');
}

$validPlans = ['full', 'installment'];
if (!in_array($plan, $validPlans, true)) {
    jsonResponse('error', 'Invalid plan selected');
}

// ── Sanitize strings (prevent XSS if later rendered as HTML) ─────────────────
$fullName   = htmlspecialchars($fullName,   ENT_QUOTES, 'UTF-8');
$city       = htmlspecialchars($city,       ENT_QUOTES, 'UTF-8');
$state      = htmlspecialchars($state,      ENT_QUOTES, 'UTF-8');
$referralId = htmlspecialchars($referralId, ENT_QUOTES, 'UTF-8');

// ── Hash password with bcrypt ─────────────────────────────────────────────────
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $pdo = getDB();

    // ── Check for duplicate email or mobile ───────────────────────────────────
    $check = $pdo->prepare('SELECT id FROM members WHERE email = ? OR mobile = ? LIMIT 1');
    $check->execute([$email, $mobile]);
    if ($check->fetch()) {
        jsonResponse('error', 'An account with this email or mobile number already exists');
    }

    // ── Resolve optional referral code → referrer_id (FK) ────────────────────
    $referrerId = null;
    if ($referralId !== '') {
        $ref = $pdo->prepare('SELECT id FROM members WHERE member_code = ? LIMIT 1');
        $ref->execute([$referralId]);
        $refRow = $ref->fetch();
        if (!$refRow) {
            jsonResponse('error', 'Referral ID not found. Please check and try again.');
        }
        $referrerId = (int) $refRow['id'];
    }

    // ── Auto-generate unique member code: UH-000001, UH-000002 … ─────────────
    $seq  = (int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
    $code = 'UH-' . str_pad($seq + 1, 6, '0', STR_PAD_LEFT);

    // ── Insert new member (status = pending — admin must approve) ─────────────
    $stmt = $pdo->prepare(
        'INSERT INTO members
           (member_code, full_name, mobile, email, city, state, referrer_id, plan, password_hash, status, created_at)
         VALUES
           (?, ?, ?, ?, ?, ?, ?, ?, ?, \'pending\', NOW())'
    );
    $stmt->execute([$code, $fullName, $mobile, $email, $city, $state, $referrerId, $plan, $hash]);

    jsonResponse('success', 'Account Created', [
        'member_id'   => (int) $pdo->lastInsertId(),
        'member_code' => $code,
    ]);

} catch (PDOException $e) {
    jsonResponse('error', 'Registration failed. Please try again.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse('error', 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// --- Sanitize & validate inputs ---
$fullName   = trim($input['full_name']    ?? '');
$mobile     = trim($input['mobile']       ?? '');
$email      = trim($input['email']        ?? '');
$dob        = trim($input['dob']          ?? '');
$city       = trim($input['city']         ?? '');
$state      = trim($input['state']        ?? '');
$referralId = trim($input['referral_id']  ?? '');
$plan       = trim($input['plan']         ?? '');

// Required field checks
if (empty($fullName) || empty($mobile) || empty($email) || empty($dob) || empty($city) || empty($state) || empty($plan)) {
    jsonResponse('error', 'Sabhi required fields fill karein');
}

// Mobile validation
if (!isValidMobile($mobile)) {
    jsonResponse('error', 'Valid 10-digit Indian mobile number required');
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Valid email address required');
}

// Age validation: must be 18+
$dobDate     = DateTime::createFromFormat('Y-m-d', $dob);
$today       = new DateTime();
$age         = $today->diff($dobDate)->y;
if (!$dobDate || $age < 18) {
    jsonResponse('error', 'Aapki umar 18 saal se kam nahi honi chahiye');
}

// Plan validation
$validPlans = ['full', 'installment'];
if (!in_array($plan, $validPlans, true)) {
    jsonResponse('error', 'Invalid plan selected');
}

// Sanitize strings
$fullName   = htmlspecialchars($fullName,   ENT_QUOTES, 'UTF-8');
$city       = htmlspecialchars($city,       ENT_QUOTES, 'UTF-8');
$state      = htmlspecialchars($state,      ENT_QUOTES, 'UTF-8');
$referralId = htmlspecialchars($referralId, ENT_QUOTES, 'UTF-8');

$password = bin2hex(random_bytes(4)); // Auto-generated password — sent via SMS/email in production
$hash     = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $pdo = getDB();

    // Check for duplicate mobile or email
    $check = $pdo->prepare('SELECT id FROM members WHERE mobile = ? OR email = ? LIMIT 1');
    $check->execute([$mobile, $email]);
    if ($check->fetch()) {
        jsonResponse('error', 'Is mobile ya email se account pehle se registered hai');
    }

    // Validate referral ID if provided
    $referrerId = null;
    if (!empty($referralId)) {
        $ref = $pdo->prepare('SELECT id FROM members WHERE member_code = ? LIMIT 1');
        $ref->execute([$referralId]);
        $refRow = $ref->fetch();
        if (!$refRow) {
            jsonResponse('error', 'Referral ID valid nahi hai');
        }
        $referrerId = $refRow['id'];
    }

    // Generate unique member code: UH- + 6-digit sequence
    $seq  = $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
    $code = 'UH-' . str_pad((int)$seq + 1, 6, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare(
        'INSERT INTO members
           (member_code, full_name, mobile, email, dob, city, state, referrer_id, plan, password_hash, status, created_at)
         VALUES
           (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())'
    );
    $stmt->execute([$code, $fullName, $mobile, $email, $dob, $city, $state, $referrerId, $plan, $hash]);
    $memberId = $pdo->lastInsertId();

    // TODO: Send welcome SMS / email with member_code + auto-password

    jsonResponse('success', "Registration successful! Aapka Member ID hai: $code. Admin approval ke baad aap active ho jayenge.", [
        'member_id'   => (int) $memberId,
        'member_code' => $code,
    ]);

} catch (PDOException $e) {
    jsonResponse('error', 'Registration failed. Please try again.');
}

