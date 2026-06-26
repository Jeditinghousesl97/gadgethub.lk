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

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = (int)($body['id'] ?? 0);
$status = $body['status'] ?? '';
$valid  = ['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled'];

if (!$id || !in_array($status, $valid, true)) {
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

$previousStatus = (string)($order['status'] ?? '');

if ($previousStatus !== $status) {
    $db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    $order['status'] = $status;

    $customerEmail = trim((string)($order['customer_email'] ?? ''));
    if ($customerEmail !== '') {
        $items = json_decode((string)($order['items_json'] ?? '[]'), true);
        if (!is_array($items)) {
            $items = [];
        }

        $subject = 'Order Status Updated - ' . (string)($order['order_number'] ?? 'Order') . ' | GADGET HUB';
        $html = emailOrderStatusUpdate($order, $items, $previousStatus, $status);
        sendMail($customerEmail, (string)($order['customer_name'] ?? 'Customer'), $subject, $html);
    }
}

echo json_encode(['success' => true, 'status' => $status]);
