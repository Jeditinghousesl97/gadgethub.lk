<?php
header('Content-Type: text/plain; charset=UTF-8');

require_once dirname(__DIR__) . '/includes/functions.php';
ensureOrderPaymentColumns();

$publicKey = trim(getSetting('pm_koko_public_key', ''));
if ($publicKey === '') {
    http_response_code(400);
    echo 'KOKO not configured';
    exit;
}

$orderId = trim((string)($_POST['orderId'] ?? ''));
$trnId = trim((string)($_POST['trnId'] ?? ''));
$status = strtoupper(trim((string)($_POST['status'] ?? '')));
$desc = trim((string)($_POST['desc'] ?? ''));
$signature = trim((string)($_POST['signature'] ?? ''));

if ($orderId === '' || $trnId === '' || $status === '' || $signature === '') {
    http_response_code(422);
    echo 'Invalid payload';
    exit;
}

$dataString = $orderId . $trnId . $status;
if (!verifyKokoSignature($dataString, $signature, $publicKey)) {
    http_response_code(403);
    echo 'Invalid signature';
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT id, payment_method, payment_status, payment_meta FROM orders WHERE order_number = ? LIMIT 1');
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    echo 'Order not found';
    exit;
}
if (($order['payment_method'] ?? '') !== 'koko') {
    http_response_code(409);
    echo 'Order is not a KOKO payment';
    exit;
}

$meta = [];
if (!empty($order['payment_meta'])) {
    $meta = json_decode($order['payment_meta'], true);
    if (!is_array($meta)) {
        $meta = [];
    }
}

$meta['koko'] = [
    'order_id' => $orderId,
    'transaction_id' => $trnId,
    'status' => $status,
    'description' => $desc,
    'signature' => $signature,
    'notified_at' => date('c'),
];

$incomingPaymentStatus = mapKokoStatusToPaymentStatus($status);
$currentPaymentStatus = (string)($order['payment_status'] ?? '');
$paymentStatus = mergePaymentStatus($currentPaymentStatus, $incomingPaymentStatus);

$up = $db->prepare('UPDATE orders SET payment_status = ?, payment_meta = ? WHERE id = ?');
$up->execute([$paymentStatus, json_encode($meta, JSON_UNESCAPED_UNICODE), $order['id']]);

echo 'OK';
