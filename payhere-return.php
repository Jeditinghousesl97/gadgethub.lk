<?php
require_once __DIR__ . '/includes/payment-page.php';

$orderNo = trim((string)($_GET['order'] ?? $_GET['order_id'] ?? ''));
$payload = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

if (!empty($payload['merchant_id']) && !empty($payload['order_id']) && !empty($payload['md5sig'])) {
    writeGatewayDebugLog('payhere', 'return_received', $payload);
    try {
        $result = processPayherePaymentPayload($payload);
        $orderNo = trim((string)($result['order_id'] ?? $orderNo));
        writeGatewayDebugLog('payhere', 'return_processed', [
            'order_id' => $result['order_id'] ?? '',
            'payment_status' => $result['payment_status'] ?? '',
            'status_code' => $result['status_code'] ?? '',
            'payment_id' => $result['payment_id'] ?? '',
        ]);
    } catch (Throwable $e) {
        writeGatewayDebugLog('payhere', 'return_process_failed', [
            'message' => $e->getMessage(),
            'payload' => $payload,
        ]);
        // Fall back to current stored status below so the page still renders.
    }
} else {
    writeGatewayDebugLog('payhere', 'return_without_signed_payload', $payload);
}

$order = getOrderByNumber($orderNo);
$paymentStatus = strtolower((string)($order['payment_status'] ?? 'awaiting_payment'));
$page = getPaymentPageConfig($paymentStatus, 'payhere', 'return');
$paymentMeta = [];
if ($order && !empty($order['payment_meta'])) {
    $paymentMeta = json_decode($order['payment_meta'], true);
    if (!is_array($paymentMeta)) {
        $paymentMeta = [];
    }
}

renderGatewayPaymentPage([
    'title' => $page['title'],
    'description' => 'PayHere payment status for your Gadget Hub order.',
    'canonical' => 'payhere-return.php',
    'gateway' => 'PayHere',
    'tag' => $page['tag'],
    'message' => $page['message'],
    'payment_status' => $paymentStatus,
    'status_label' => $page['title'],
    'icon' => $page['icon'],
    'icon_bg' => $page['icon_bg'],
    'icon_color' => $page['icon_color'],
    'order_number' => $orderNo,
    'transaction_id' => (string)($paymentMeta['payhere']['payment_id'] ?? ''),
    'show_pay_again' => $paymentStatus !== 'paid',
    'show_account' => true,
    'primary_href' => 'index.php',
    'primary_label' => 'Go Home',
    'secondary_href' => 'cart.php',
    'secondary_label' => 'View Cart',
    'auto_refresh_seconds' => in_array($paymentStatus, ['awaiting_payment', 'pending'], true) ? 45 : 0,
    'extra_note' => $paymentStatus === 'paid'
        ? 'Your payment is already marked as paid in the system.'
        : "PayHere does not send the final payment result to this return page.\nWe can mark the order as paid only after the server-to-server notification reaches our notify URL.\nThis page will refresh automatically for a short time while we wait for that confirmation.",
]);
