<?php
header('Content-Type: text/plain; charset=UTF-8');

require_once dirname(__DIR__) . '/includes/functions.php';
ensureOrderPaymentColumns();

$rawBody = file_get_contents('php://input');
$contentType = (string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
$method = (string)($_SERVER['REQUEST_METHOD'] ?? '');

$payload = $_POST;
if (empty($payload) && is_string($rawBody) && trim($rawBody) !== '') {
    $parsedBody = [];
    parse_str($rawBody, $parsedBody);
    if (is_array($parsedBody) && !empty($parsedBody)) {
        $payload = $parsedBody;
    }
}
if (empty($payload) && !empty($_GET)) {
    $payload = $_GET;
}

writeGatewayDebugLog('payhere', 'notify_received', [
    'method' => $method,
    'content_type' => $contentType,
    'post' => $_POST,
    'get' => $_GET,
    'raw_body' => $rawBody,
    'payload_used' => $payload,
]);

try {
    $result = processPayherePaymentPayload($payload);
    writeGatewayDebugLog('payhere', 'notify_processed', [
        'order_id' => $result['order_id'] ?? '',
        'payment_status' => $result['payment_status'] ?? '',
        'status_code' => $result['status_code'] ?? '',
        'payment_id' => $result['payment_id'] ?? '',
    ]);
    echo 'OK';
} catch (InvalidArgumentException $e) {
    writeGatewayDebugLog('payhere', 'notify_invalid', [
        'message' => $e->getMessage(),
        'payload' => $payload,
        'raw_body' => $rawBody,
    ]);
    http_response_code(422);
    echo $e->getMessage();
} catch (RuntimeException $e) {
    $message = $e->getMessage();
    writeGatewayDebugLog('payhere', 'notify_runtime_error', [
        'message' => $message,
        'payload' => $payload,
        'raw_body' => $rawBody,
    ]);
    if ($message === 'PayHere not configured') {
        http_response_code(400);
    } elseif ($message === 'Invalid signature') {
        http_response_code(403);
    } elseif ($message === 'Order not found') {
        http_response_code(404);
    } else {
        http_response_code(500);
    }
    echo $message;
}

