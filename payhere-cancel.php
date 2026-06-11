<?php
require_once __DIR__ . '/includes/payment-page.php';

$orderNo = trim((string)($_GET['order'] ?? ''));
$order = getOrderByNumber($orderNo);

if ($order && ($order['payment_status'] ?? '') !== 'paid') {
    $meta = [];
    if (!empty($order['payment_meta'])) {
        $meta = json_decode($order['payment_meta'], true);
        if (!is_array($meta)) {
            $meta = [];
        }
    }

    $meta['payhere_cancel'] = [
        'cancelled_at' => date('c'),
    ];

    getDB()->prepare('UPDATE orders SET payment_status = ?, payment_meta = ? WHERE id = ?')
        ->execute(['cancelled', json_encode($meta, JSON_UNESCAPED_UNICODE), $order['id']]);

    $order = getOrderByNumber($orderNo);
}

$paymentStatus = strtolower((string)($order['payment_status'] ?? 'cancelled'));
$page = getPaymentPageConfig($paymentStatus, 'payhere', 'cancel');

renderGatewayPaymentPage([
    'title' => $page['title'],
    'description' => 'PayHere payment cancellation status for your Gadget Hub order.',
    'canonical' => 'payhere-cancel.php',
    'gateway' => 'PayHere',
    'tag' => $page['tag'],
    'message' => $page['message'],
    'payment_status' => $paymentStatus,
    'status_label' => $page['title'],
    'icon' => $page['icon'],
    'icon_bg' => $page['icon_bg'],
    'icon_color' => $page['icon_color'],
    'order_number' => $orderNo,
    'show_pay_again' => $paymentStatus !== 'paid',
    'primary_href' => 'checkout.php',
    'primary_label' => 'Back to Checkout',
    'secondary_href' => 'cart.php',
    'secondary_label' => 'View Cart',
]);
