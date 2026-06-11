<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($body['id'] ?? 0);
$paymentStatus = trim((string)($body['payment_status'] ?? ''));
$validStatuses = ['pending', 'awaiting_payment', 'paid', 'failed', 'cancelled', 'refunded'];

if (!$id || !in_array($paymentStatus, $validStatuses, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare('UPDATE orders SET payment_status = ? WHERE id = ?');
$stmt->execute([$paymentStatus, $id]);

echo json_encode(['success' => true, 'payment_status' => $paymentStatus]);
