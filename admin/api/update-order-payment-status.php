<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/mailer.php';
require_once dirname(__DIR__, 2) . '/includes/email-templates.php';

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
$stmt = $db->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found.']);
    exit;
}

$previousPaymentStatus = trim((string)($order['payment_status'] ?? ''));

if ($previousPaymentStatus !== $paymentStatus) {
    $stmt = $db->prepare('UPDATE orders SET payment_status = ? WHERE id = ?');
    $stmt->execute([$paymentStatus, $id]);
    $order['payment_status'] = $paymentStatus;

    $customerEmail = trim((string)($order['customer_email'] ?? ''));
    if ($customerEmail !== '') {
        $items = json_decode((string)($order['items_json'] ?? '[]'), true);
        if (!is_array($items)) {
            $items = [];
        }

        $subject = 'Payment Status Updated - ' . (string)($order['order_number'] ?? 'Order') . ' | GADGET HUB';
        $html = emailPaymentStatusUpdate($order, $items, $previousPaymentStatus, $paymentStatus);
        sendMail($customerEmail, (string)($order['customer_name'] ?? 'Customer'), $subject, $html);
    }
}

echo json_encode(['success' => true, 'payment_status' => $paymentStatus]);
