<?php
/**
 * UnifyHub – Admin API
 * Handles all admin panel data operations.
 *
 * GET  actions: dashboard_stats, list_members, list_franchises, list_payments, list_announcements, recent_members
 * POST actions: update_member_status, update_franchise_status, create_franchise,
 *               verify_payment, post_announcement, delete_announcement, export_members
 *
 * Session: requires $_SESSION['admin_id'] (set by auth.php)
 */

require_once __DIR__ . '/config.php';

session_start();

// ---- Admin session guard ----
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    jsonResponse('error', 'Unauthorized. Please login.');
}

$method = $_SERVER['REQUEST_METHOD'];
$action = '';

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

switch ($action) {

    // -------------------------------------------------------
    case 'dashboard_stats':
    // -------------------------------------------------------
        $members    = $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
        $franchises = $pdo->query('SELECT COUNT(*) FROM franchises WHERE status="active"')->fetchColumn();
        $revenue    = $pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status="verified"')->fetchColumn();
        $pending    = $pdo->query('SELECT COUNT(*) FROM members WHERE status="pending"')->fetchColumn();
        jsonResponse('success', '', [
            'members'    => (int) $members,
            'franchises' => (int) $franchises,
            'revenue'    => (float) $revenue,
            'pending'    => (int) $pending,
        ]);
        break;

    // -------------------------------------------------------
    case 'recent_members':
    // -------------------------------------------------------
        $limit = min((int)($_GET['limit'] ?? 10), 50);
        $stmt  = $pdo->prepare('SELECT id, member_code, full_name, mobile, city, plan, status, created_at FROM members ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    // -------------------------------------------------------
    case 'list_members':
    // -------------------------------------------------------
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min((int)($_GET['per_page'] ?? 15), 100);
        $offset  = ($page - 1) * $perPage;
        $search  = '%' . trim($_GET['search'] ?? '') . '%';
        $status  = trim($_GET['status'] ?? '');

        $where  = 'WHERE (full_name LIKE ? OR mobile LIKE ? OR member_code LIKE ? OR email LIKE ?)';
        $params = [$search, $search, $search, $search];
        if ($status) { $where .= ' AND status = ?'; $params[] = $status; }

        $total = $pdo->prepare("SELECT COUNT(*) FROM members $where");
        $total->execute($params);
        $count = (int) $total->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, member_code, full_name, mobile, email, city, state, plan, referrer_id, status, created_at FROM members $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        jsonResponse('success', '', [
            'data'        => $rows,
            'total'       => $count,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($count / $perPage),
        ]);
        break;

    // -------------------------------------------------------
    case 'update_member_status':
    // -------------------------------------------------------
        $id     = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $valid  = ['active', 'pending', 'rejected', 'inactive'];
        if (!$id || !in_array($status, $valid, true)) {
            jsonResponse('error', 'Invalid request');
        }
        $stmt = $pdo->prepare('UPDATE members SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        jsonResponse('success', 'Member status updated');
        break;

    // -------------------------------------------------------
    case 'list_franchises':
    // -------------------------------------------------------
        $stmt = $pdo->prepare(
            'SELECT f.id, f.franchise_code, m.full_name AS owner_name, m.mobile, f.area, f.type, f.status,
                    (SELECT COUNT(*) FROM members WHERE referrer_id = m.id) AS team_size,
                    f.created_at
             FROM franchises f
             JOIN members m ON m.id = f.member_id
             ORDER BY f.created_at DESC'
        );
        $stmt->execute();
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    // -------------------------------------------------------
    case 'create_franchise':
    // -------------------------------------------------------
        $memberId = (int)($input['member_id'] ?? 0);
        $area     = trim($input['area'] ?? '');
        $type     = trim($input['type'] ?? '');

        if (!$memberId || !$area || !$type) { jsonResponse('error', 'All fields required'); }

        // Check member exists
        $m = $pdo->prepare('SELECT id FROM members WHERE id = ? LIMIT 1');
        $m->execute([$memberId]);
        if (!$m->fetch()) { jsonResponse('error', 'Member not found'); }

        // Auto-generate franchise code
        $count = $pdo->query('SELECT COUNT(*) FROM franchises')->fetchColumn();
        $code  = 'FR-' . str_pad((int)$count + 1, 4, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare('INSERT INTO franchises (member_id, franchise_code, area, type, status, created_at) VALUES (?, ?, ?, ?, "active", NOW())');
        $stmt->execute([$memberId, $code, $area, $type]);
        jsonResponse('success', "Franchise $code created successfully");
        break;

    // -------------------------------------------------------
    case 'update_franchise_status':
    // -------------------------------------------------------
        $id     = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        if (!$id || !in_array($status, ['active','blocked'], true)) { jsonResponse('error', 'Invalid request'); }
        $stmt = $pdo->prepare('UPDATE franchises SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        jsonResponse('success', 'Franchise status updated');
        break;

    // -------------------------------------------------------
    case 'list_payments':
    // -------------------------------------------------------
        $status = trim($_GET['status'] ?? '');
        $where  = $status ? 'WHERE p.status = ?' : '';
        $params = $status ? [$status] : [];
        $stmt = $pdo->prepare(
            "SELECT p.id, p.txn_ref AS txn, m.full_name AS member, p.amount, p.plan,
                    p.installment_no AS inst_no, p.installment_total AS inst_total,
                    p.payment_method AS method, p.upi_ref AS ref,
                    DATE(p.created_at) AS date, p.status
             FROM payments p
             JOIN members m ON m.id = p.member_id
             $where
             ORDER BY p.created_at DESC"
        );
        $stmt->execute($params);
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    // -------------------------------------------------------
    case 'verify_payment':
    // -------------------------------------------------------
        $id     = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        if (!$id || !in_array($status, ['verified','rejected'], true)) { jsonResponse('error', 'Invalid request'); }
        $stmt = $pdo->prepare('UPDATE payments SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);

        // Activate member after full-payment or final installment verified
        if ($status === 'verified') {
            $pay = $pdo->prepare('SELECT member_id, installment_no, installment_total FROM payments WHERE id = ?');
            $pay->execute([$id]);
            $p = $pay->fetch();
            if ($p && (int)$p['installment_no'] >= (int)$p['installment_total']) {
                $pdo->prepare('UPDATE members SET status = "active" WHERE id = ? AND status = "pending"')->execute([$p['member_id']]);
            }
        }
        jsonResponse('success', 'Payment status updated');
        break;

    // -------------------------------------------------------
    case 'post_announcement':
    // -------------------------------------------------------
        $title    = trim($input['title']        ?? '');
        $category = trim($input['category']     ?? 'general');
        $target   = trim($input['target']       ?? 'all');
        $content  = trim($input['content']      ?? '');
        $pubDate  = trim($input['publish_date'] ?? date('Y-m-d'));

        if (!$title || !$content) { jsonResponse('error', 'Title aur content required hain'); }

        $validCats    = ['general','update','event','alert'];
        $validTargets = ['all','members','franchises'];
        if (!in_array($category, $validCats, true))    { $category = 'general'; }
        if (!in_array($target,   $validTargets, true)) { $target   = 'all'; }

        $title   = htmlspecialchars($title,   ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        $stmt = $pdo->prepare('INSERT INTO announcements (title, category, target, content, publish_date, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$title, $category, $target, $content, $pubDate, $_SESSION['admin_name'] ?? 'Admin']);
        jsonResponse('success', 'Announcement posted successfully');
        break;

    // -------------------------------------------------------
    case 'list_announcements':
    // -------------------------------------------------------
        $stmt = $pdo->query('SELECT id, title, category, target, content, publish_date, created_by FROM announcements ORDER BY created_at DESC LIMIT 50');
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    // -------------------------------------------------------
    case 'delete_announcement':
    // -------------------------------------------------------
        $id = (int)($input['id'] ?? 0);
        if (!$id) { jsonResponse('error', 'Invalid ID'); }
        $pdo->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
        jsonResponse('success', 'Announcement deleted');
        break;

    // -------------------------------------------------------
    case 'export_members':
    // -------------------------------------------------------
        // Override content type to CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="unifyhub_members_' . date('Ymd') . '.csv"');

        $stmt = $pdo->query('SELECT member_code, full_name, mobile, email, city, state, plan, status, created_at FROM members ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Member ID','Full Name','Mobile','Email','City','State','Plan','Status','Joined']);
        foreach ($rows as $row) { fputcsv($output, $row); }
        fclose($output);
        exit;

    default:
        http_response_code(400);
        jsonResponse('error', 'Unknown action');
}
