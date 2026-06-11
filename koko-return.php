<?php
require_once __DIR__ . '/includes/payment-page.php';

$orderNo = trim((string)($_GET['order'] ?? $_GET['orderId'] ?? ''));
$transactionId = trim((string)($_GET['trnId'] ?? ''));
$status = strtoupper(trim((string)($_GET['status'] ?? '')));

if ($orderNo !== '' && $status !== '') {
    $order = getOrderByNumber($orderNo);
    if ($order) {
        $meta = [];
        if (!empty($order['payment_meta'])) {
            $meta = json_decode($order['payment_meta'], true);
            if (!is_array($meta)) {
                $meta = [];
            }
        }

        $meta['koko_return'] = [
            'transaction_id' => $transactionId,
            'status' => $status,
            'returned_at' => date('c'),
        ];

        $incomingPaymentStatus = mapKokoStatusToPaymentStatus($status);
        $currentPaymentStatus = (string)($order['payment_status'] ?? '');
        $paymentStatus = mergePaymentStatus($currentPaymentStatus, $incomingPaymentStatus);

        getDB()->prepare('UPDATE orders SET payment_status = ?, payment_meta = ? WHERE id = ?')
            ->execute([$paymentStatus, json_encode($meta, JSON_UNESCAPED_UNICODE), $order['id']]);
    }
}

$order = getOrderByNumber($orderNo);
$paymentStatus = strtolower((string)($order['payment_status'] ?? mapKokoStatusToPaymentStatus($status ?: 'PENDING')));
$page = getPaymentPageConfig($paymentStatus, 'koko', 'return');

renderGatewayPaymentPage([
    'title' => $page['title'],
    'description' => 'KOKO payment status for your Gadget Hub order.',
    'canonical' => 'koko-return.php',
    'gateway' => 'KOKO',
    'tag' => $page['tag'],
    'message' => $page['message'],
    'payment_status' => $paymentStatus,
    'status_label' => $page['title'],
    'icon' => $page['icon'],
    'icon_bg' => $page['icon_bg'],
    'icon_color' => $page['icon_color'],
    'order_number' => $orderNo,
    'transaction_id' => $transactionId,
    'show_pay_again' => $paymentStatus !== 'paid',
    'show_account' => true,
    'primary_href' => 'index.php',
    'primary_label' => 'Go Home',
    'secondary_href' => 'cart.php',
    'secondary_label' => 'View Cart',
    'extra_note' => $status !== ''
        ? 'KOKO return status: ' . $status . ($paymentStatus !== 'paid' ? "\nYou can try the payment again if the order is still not marked as paid." : '')
        : 'We are checking the latest KOKO payment result for this order.',
]);
