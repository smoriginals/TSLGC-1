<?php
/**
 * TSLGC – Franchise API
 * Handles all franchise portal data operations.
 *
 * GET  actions: franchise_dashboard, my_team, my_income, list_leads, get_announcements
 * POST actions: add_lead, update_lead_status, update_profile, update_bank, change_password
 *
 * Session: requires $_SESSION['franchise_id'] (set by auth.php)
 */

require_once __DIR__ . '/config.php';

session_start();

// ---- Franchise session guard ----
if (empty($_SESSION['franchise_id'])) {
    http_response_code(401);
    jsonResponse('error', 'Unauthorized. Please login.');
}

$frId   = (int) $_SESSION['franchise_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = '';
$input  = [];

if ($method === 'GET') {
    $action = trim($_GET['action'] ?? '');
} elseif ($method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = trim($input['action'] ?? '');
} else {
    http_response_code(405);
    jsonResponse('error', 'Method not allowed');
}

$pdo = getDB();

// Helper: get franchise's member_id
function getMemberId(PDO $pdo, int $frId): int {
    $s = $pdo->prepare('SELECT member_id FROM franchises WHERE id = ? LIMIT 1');
    $s->execute([$frId]);
    return (int)($s->fetchColumn() ?: 0);
}

switch ($action) {

    // -------------------------------------------------------
    case 'franchise_dashboard':
    // -------------------------------------------------------
        $memberId = getMemberId($pdo, $frId);

        $name = $pdo->prepare('SELECT full_name FROM members WHERE id = ? LIMIT 1');
        $name->execute([$memberId]);
        $frName = $name->fetchColumn() ?: 'Franchise Partner';

        $frCode = $pdo->prepare('SELECT franchise_code FROM franchises WHERE id = ? LIMIT 1');
        $frCode->execute([$frId]);
        $code = $frCode->fetchColumn() ?: 'FR-0000';

        // Team count (direct referrals only for now)
        $team = $pdo->prepare('SELECT COUNT(*) FROM members WHERE referrer_id = ?');
        $team->execute([$memberId]);
        $teamCount = (int) $team->fetchColumn();

        // Total earnings
        $earn = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM franchise_income WHERE franchise_id = ?');
        $earn->execute([$frId]);
        $earnings = (float) $earn->fetchColumn();

        // Open leads count
        $leads = $pdo->prepare('SELECT COUNT(*) FROM leads WHERE franchise_id = ? AND status IN ("new","contacted")');
        $leads->execute([$frId]);
        $leadCount = (int) $leads->fetchColumn();

        // Determine rank based on team size
        $rank = 'bronze';
        if ($teamCount >= 500)      $rank = 'legend';
        elseif ($teamCount >= 250)  $rank = 'diamond';
        elseif ($teamCount >= 100)  $rank = 'gold';
        elseif ($teamCount >= 50)   $rank = 'silver';

        jsonResponse('success', '', [
            'name'         => $frName,
            'franchise_id' => $code,
            'team'         => $teamCount,
            'earnings'     => $earnings,
            'leads'        => $leadCount,
            'rank'         => $rank,
        ]);
        break;

    // -------------------------------------------------------
    case 'my_team':
    // -------------------------------------------------------
        $memberId = getMemberId($pdo, $frId);

        // Level 1 – direct referrals
        $stmt = $pdo->prepare(
            'SELECT member_code AS id, full_name AS name, mobile, city, plan, status, DATE(created_at) AS joined, 1 AS level
             FROM members WHERE referrer_id = ?'
        );
        $stmt->execute([$memberId]);
        $level1 = $stmt->fetchAll();

        // Level 2 – referrals of referrals
        $l1Ids = array_column($level1, 'id');
        $l2    = [];
        if ($l1Ids) {
            $in   = implode(',', array_fill(0, count($l1Ids), '?'));
            $stmt = $pdo->prepare(
                "SELECT m.member_code AS id, m.full_name AS name, m.mobile, m.city, m.plan, m.status, DATE(m.created_at) AS joined, 2 AS level
                 FROM members m
                 JOIN members p ON p.id = m.referrer_id
                 WHERE p.member_code IN ($in)"
            );
            $stmt->execute($l1Ids);
            $l2 = $stmt->fetchAll();
        }

        jsonResponse('success', '', array_merge($level1, $l2));
        break;

    // -------------------------------------------------------
    case 'my_income':
    // -------------------------------------------------------
        $stmt = $pdo->prepare(
            'SELECT id, DATE(created_at) AS date, income_type AS type, source_name AS `from`, amount, status
             FROM franchise_income WHERE franchise_id = ? ORDER BY created_at DESC LIMIT 100'
        );
        $stmt->execute([$frId]);
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    // -------------------------------------------------------
    case 'add_lead':
    // -------------------------------------------------------
        $name  = trim($input['leadName']  ?? '');
        $phone = trim($input['leadPhone'] ?? '');
        $city  = trim($input['leadCity']  ?? '');
        $notes = trim($input['leadNotes'] ?? '');

        if (!$name || !$phone) { jsonResponse('error', 'Name aur phone required hain'); }
        if (!isValidMobile($phone)) { jsonResponse('error', 'Valid 10-digit mobile number required'); }

        $name  = htmlspecialchars($name,  ENT_QUOTES, 'UTF-8');
        $city  = htmlspecialchars($city,  ENT_QUOTES, 'UTF-8');
        $notes = htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');

        $stmt = $pdo->prepare('INSERT INTO leads (franchise_id, lead_name, lead_phone, lead_city, notes, status, created_at) VALUES (?, ?, ?, ?, ?, "new", NOW())');
        $stmt->execute([$frId, $name, $phone, $city, $notes]);
        jsonResponse('success', 'Lead added successfully');
        break;

    // -------------------------------------------------------
    case 'list_leads':
    // -------------------------------------------------------
        $stmt = $pdo->prepare('SELECT id, lead_name AS name, lead_phone AS phone, lead_city AS city, status, DATE(created_at) AS added FROM leads WHERE franchise_id = ? ORDER BY created_at DESC');
        $stmt->execute([$frId]);
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    // -------------------------------------------------------
    case 'update_lead_status':
    // -------------------------------------------------------
        $leadId = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $valid  = ['new','contacted','converted','lost'];
        if (!$leadId || !in_array($status, $valid, true)) { jsonResponse('error', 'Invalid request'); }

        // Ensure lead belongs to this franchise
        $check = $pdo->prepare('SELECT id FROM leads WHERE id = ? AND franchise_id = ? LIMIT 1');
        $check->execute([$leadId, $frId]);
        if (!$check->fetch()) { jsonResponse('error', 'Lead not found'); }

        $pdo->prepare('UPDATE leads SET status = ? WHERE id = ?')->execute([$status, $leadId]);
        jsonResponse('success', 'Lead status updated');
        break;

    // -------------------------------------------------------
    case 'update_profile':
    // -------------------------------------------------------
        $memberId = getMemberId($pdo, $frId);
        $name  = trim($input['full_name'] ?? '');
        $email = trim($input['email']     ?? '');
        $city  = trim($input['city']      ?? '');
        $state = trim($input['state']     ?? '');
        $dob   = trim($input['dob']       ?? '');

        if (!$name || !$email) { jsonResponse('error', 'Name aur email required hain'); }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { jsonResponse('error', 'Valid email required'); }

        $name  = htmlspecialchars($name,  ENT_QUOTES, 'UTF-8');
        $city  = htmlspecialchars($city,  ENT_QUOTES, 'UTF-8');
        $state = htmlspecialchars($state, ENT_QUOTES, 'UTF-8');

        $pdo->prepare('UPDATE members SET full_name=?, email=?, city=?, state=?, dob=? WHERE id=?')
            ->execute([$name, $email, $city, $state, $dob ?: null, $memberId]);
        jsonResponse('success', 'Profile updated successfully');
        break;

    // -------------------------------------------------------
    case 'update_bank':
    // -------------------------------------------------------
        $memberId   = getMemberId($pdo, $frId);
        $accountNo  = trim($input['account_no']   ?? '');
        $ifsc       = strtoupper(trim($input['ifsc'] ?? ''));
        $accName    = trim($input['account_name'] ?? '');
        $upiId      = trim($input['upi_id']       ?? '');

        if ($accountNo && !preg_match('/^\d{9,18}$/', $accountNo)) { jsonResponse('error', 'Invalid account number'); }
        if ($ifsc && !preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc)) { jsonResponse('error', 'Invalid IFSC code'); }

        // Upsert into bank_details table
        $exists = $pdo->prepare('SELECT id FROM bank_details WHERE member_id = ? LIMIT 1');
        $exists->execute([$memberId]);
        if ($exists->fetch()) {
            $pdo->prepare('UPDATE bank_details SET account_no=?, ifsc=?, account_name=?, upi_id=? WHERE member_id=?')
                ->execute([$accountNo, $ifsc, $accName, $upiId, $memberId]);
        } else {
            $pdo->prepare('INSERT INTO bank_details (member_id, account_no, ifsc, account_name, upi_id) VALUES (?,?,?,?,?)')
                ->execute([$memberId, $accountNo, $ifsc, $accName, $upiId]);
        }
        jsonResponse('success', 'Bank details saved');
        break;

    // -------------------------------------------------------
    case 'change_password':
    // -------------------------------------------------------
        $memberId = getMemberId($pdo, $frId);
        $current  = $input['current_password'] ?? '';
        $newPass  = $input['new_password']      ?? '';
        $confirm  = $input['confirm_password']  ?? '';

        if (strlen($newPass) < 8) { jsonResponse('error', 'Password minimum 8 characters hona chahiye'); }
        if ($newPass !== $confirm) { jsonResponse('error', 'Passwords match nahi kar rahe'); }

        $row = $pdo->prepare('SELECT password_hash FROM members WHERE id = ? LIMIT 1');
        $row->execute([$memberId]);
        $hash = $row->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            jsonResponse('error', 'Current password incorrect hai');
        }

        $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare('UPDATE members SET password_hash = ? WHERE id = ?')->execute([$newHash, $memberId]);
        jsonResponse('success', 'Password successfully changed');
        break;

    // -------------------------------------------------------
    case 'get_announcements':
    // -------------------------------------------------------
        $stmt = $pdo->query('SELECT id, title, category, content, publish_date, created_by FROM announcements WHERE target IN ("all","franchises") AND publish_date <= CURDATE() ORDER BY publish_date DESC LIMIT 10');
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    default:
        http_response_code(400);
        jsonResponse('error', 'Unknown action');
}

