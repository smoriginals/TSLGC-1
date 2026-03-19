<?php
/**
 * UnifyHub – Payment Handler
 * Records new payment submissions and tracks installments.
 *
 * POST body (JSON): { action, member_id, amount, plan, installment_no, payment_method, upi_ref }
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse('error', 'Method not allowed');
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = trim($input['action'] ?? 'submit_payment');

$pdo = getDB();

switch ($action) {

    // -------------------------------------------------------
    case 'submit_payment':
    // -------------------------------------------------------
        $memberId    = (int)($input['member_id']      ?? 0);
        $amount      = (float)($input['amount']       ?? 0);
        $plan        = trim($input['plan']            ?? '');
        $instNo      = (int)($input['installment_no'] ?? 1);
        $method      = trim($input['payment_method']  ?? '');
        $upiRef      = trim($input['upi_ref']         ?? '');

        if (!$memberId || !$amount || !$plan || !$method) {
            jsonResponse('error', 'Required fields missing');
        }

        // Validate plan amounts
        $planAmounts = ['full' => 4999.0, 'installment' => 2500.0];
        if (!array_key_exists($plan, $planAmounts)) { jsonResponse('error', 'Invalid plan'); }
        if (abs($amount - $planAmounts[$plan]) > 0.01) {
            jsonResponse('error', 'Amount plan ke saath match nahi karta');
        }

        $instTotal   = ($plan === 'installment') ? 2 : 1;
        $validMethods = ['UPI', 'Bank Transfer', 'NEFT', 'RTGS', 'Net Banking'];
        if (!in_array($method, $validMethods, true)) { jsonResponse('error', 'Invalid payment method'); }

        // Check member exists
        $m = $pdo->prepare('SELECT id, status FROM members WHERE id = ? LIMIT 1');
        $m->execute([$memberId]);
        if (!$m->fetch()) { jsonResponse('error', 'Member not found'); }

        // Check for duplicate pending payment (same member + installment_no)
        $dup = $pdo->prepare('SELECT id FROM payments WHERE member_id = ? AND installment_no = ? AND status = "pending" LIMIT 1');
        $dup->execute([$memberId, $instNo]);
        if ($dup->fetch()) {
            jsonResponse('error', 'Yeh installment already submitted hai. Admin verification ka wait karein.');
        }

        // Generate unique txn reference
        $txnRef = 'TXN' . date('Y') . str_pad($memberId, 5, '0', STR_PAD_LEFT) . random_int(1000, 9999);
        $upiRef = htmlspecialchars($upiRef, ENT_QUOTES, 'UTF-8');

        $stmt = $pdo->prepare(
            'INSERT INTO payments (member_id, txn_ref, amount, plan, installment_no, installment_total, payment_method, upi_ref, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())'
        );
        $stmt->execute([$memberId, $txnRef, $amount, $plan, $instNo, $instTotal, $method, $upiRef]);

        jsonResponse('success', "Payment submitted! Txn ID: $txnRef. Admin verification ke baad aapka account activate hoga.", [
            'txn_ref' => $txnRef,
        ]);
        break;

    // -------------------------------------------------------
    case 'get_payment_status':
    // -------------------------------------------------------
        $memberId = (int)($input['member_id'] ?? 0);
        if (!$memberId) { jsonResponse('error', 'Member ID required'); }

        session_start();
        // Basic auth check — member can only see their own payments
        if (!isset($_SESSION['admin_id']) && (!isset($_SESSION['member_id']) || $_SESSION['member_id'] !== $memberId)) {
            http_response_code(403);
            jsonResponse('error', 'Forbidden');
        }

        $stmt = $pdo->prepare(
            'SELECT txn_ref, amount, plan, installment_no, installment_total, payment_method, status, DATE(created_at) AS submitted_on
             FROM payments WHERE member_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$memberId]);
        jsonResponse('success', '', $stmt->fetchAll());
        break;

    default:
        http_response_code(400);
        jsonResponse('error', 'Unknown action');
}
