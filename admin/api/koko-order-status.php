<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

ensureOrderPaymentColumns();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($body['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order.']);
    exit;
}

$readiness = getKokoReadiness();
if (!$readiness['ready']) {
    http_response_code(422);
    echo json_encode(['error' => 'KOKO is not ready: ' . implode(', ', $readiness['missing'])]);
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT id, order_number, payment_method, payment_status, payment_meta FROM orders WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found.']);
    exit;
}
if (($order['payment_method'] ?? '') !== 'koko') {
    http_response_code(409);
    echo json_encode(['error' => 'This order is not using KOKO.']);
    exit;
}

$merchantId = normalizeGatewayToken(getSetting('pm_koko_merchant_id', ''));
$apiKey = normalizeGatewayToken(getSetting('pm_koko_api_key', ''));
$pluginName = trim(getSetting('pm_koko_plugin_name', 'customapi')) ?: 'customapi';
$pluginVersion = trim(getSetting('pm_koko_plugin_version', '1.0.1')) ?: '1.0.1';
$privateKey = trim(getSetting('pm_koko_private_key', ''));
$endpoint = getSetting('pm_koko_sandbox', '1') === '1'
    ? 'https://qaapi.paykoko.com/api/merchants/orderView'
    : 'https://prodapi.paykoko.com/api/merchants/orderView';

$dataString = $merchantId . $pluginName . $pluginVersion . $order['order_number'] . $apiKey;

try {
    $signature = signKokoPayload($dataString, $privateKey);
    $apiResponse = postKokoForm($endpoint, [
        '_mId' => $merchantId,
        '_pluginName' => $pluginName,
        '_pluginVersion' => $pluginVersion,
        'api_key' => $apiKey,
        '_orderId' => $order['order_number'],
        'signature' => $signature,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$parsed = parseKokoApiResponse($apiResponse['body']);
$statusRaw = strtoupper(trim((string)($parsed['status'] ?? '')));
$trnId = trim((string)($parsed['trnId'] ?? $parsed['transactionId'] ?? ''));
$desc = trim((string)($parsed['desc'] ?? ''));
$responseSignature = trim((string)($parsed['signature'] ?? ''));

if ($apiResponse['status_code'] >= 400) {
    http_response_code(502);
    echo json_encode([
        'error' => 'KOKO orderView request failed.',
        'gateway_status_code' => $apiResponse['status_code'],
        'gateway_response' => $parsed ?: $apiResponse['body'],
    ]);
    exit;
}

if ($statusRaw === '') {
    http_response_code(502);
    echo json_encode([
        'error' => 'Unexpected KOKO orderView response.',
        'gateway_response' => $parsed ?: $apiResponse['body'],
    ]);
    exit;
}

$signatureVerified = false;
if ($responseSignature !== '' && $trnId !== '') {
    $signatureVerified = verifyKokoSignature($order['order_number'] . $trnId . $statusRaw, $responseSignature, trim(getSetting('pm_koko_public_key', '')));
}

$meta = [];
if (!empty($order['payment_meta'])) {
    $meta = json_decode($order['payment_meta'], true);
    if (!is_array($meta)) {
        $meta = [];
    }
}

$incomingPaymentStatus = mapKokoStatusToPaymentStatus($statusRaw);
$currentPaymentStatus = (string)($order['payment_status'] ?? '');
$paymentStatus = mergePaymentStatus($currentPaymentStatus, $incomingPaymentStatus);
$meta['koko_order_view'] = [
    'status' => $statusRaw,
    'transaction_id' => $trnId,
    'description' => $desc,
    'signature' => $responseSignature,
    'signature_verified' => $signatureVerified ? 1 : 0,
    'checked_at' => date('c'),
    'http_status_code' => $apiResponse['status_code'],
];

$db->prepare('UPDATE orders SET payment_status = ?, payment_meta = ? WHERE id = ?')
    ->execute([$paymentStatus, json_encode($meta, JSON_UNESCAPED_UNICODE), $order['id']]);

$labels = [
    'pending' => 'Pending',
    'awaiting_payment' => 'Awaiting Payment',
    'paid' => 'Paid',
    'failed' => 'Failed',
    'cancelled' => 'Cancelled',
    'refunded' => 'Refunded',
];

echo json_encode([
    'success' => true,
    'payment_status' => $paymentStatus,
    'payment_status_label' => $labels[$paymentStatus] ?? ucfirst(str_replace('_', ' ', $paymentStatus)),
    'koko_status' => $statusRaw,
    'transaction_id' => $trnId,
    'signature_verified' => $signatureVerified,
]);
