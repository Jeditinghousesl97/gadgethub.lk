<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

ensureOrderPaymentColumns();
ensureOrderCustomerColumns();

$orderNo = trim((string)($_GET['order'] ?? ''));
$order = $orderNo !== '' ? getDB()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1') : null;
if ($order) {
    $order->execute([$orderNo]);
    $order = $order->fetch();
}

$error = '';
$redirect = null;

if (!$order) {
    $error = 'We could not find that order.';
} else {
    try {
        $redirect = buildGatewayRedirectForExistingOrder($order);

        $meta = [];
        if (!empty($order['payment_meta'])) {
            $meta = json_decode($order['payment_meta'], true);
            if (!is_array($meta)) {
                $meta = [];
            }
        }

        $retryCount = (int)($meta['retry_count'] ?? 0) + 1;
        $meta['retry_count'] = $retryCount;
        $meta['last_retry_at'] = date('c');
        $meta['last_retry_gateway'] = $redirect['gateway'];

        getDB()->prepare('UPDATE orders SET payment_status = ?, payment_meta = ? WHERE id = ?')
            ->execute([
                'awaiting_payment',
                json_encode($meta, JSON_UNESCAPED_UNICODE),
                $order['id'],
            ]);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Retry Payment | Gadget Hub',
      'description' => 'Retry payment for your existing Gadget Hub order.',
      'canonical' => 'pay-again.php',
      'robots' => 'noindex,nofollow',
      'include_site_jsonld' => false,
  ]) ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= urlencode(ASSET_VERSION) ?>">
</head>
<body>

<div class="page-loader"><div class="loader-bar"></div></div>

<div id="header-slot"></div>
<script src="assets/js/cart.js"></script>
<script src="assets/js/wishlist.js"></script>
<script src="assets/js/main.js"></script>
<script src="components/header.js"></script>

<section class="page-hero" style="padding:44px 0 38px">
  <div class="hero-dot-bg"></div>
  <div class="page-hero-orb page-hero-orb-1"></div>
  <div class="container page-hero-inner">
    <div class="page-hero-text" data-anim="up">
      <span class="section-tag"><?= $error === '' ? 'Retry Payment' : 'Payment Retry Unavailable' ?></span>
      <h1><?= $error === '' ? 'Redirecting to Payment Gateway' : 'Could Not Start Payment Retry' ?></h1>
      <p style="max-width:640px;color:var(--text-muted)">
        <?= $error === ''
            ? 'We are reopening the payment gateway for your existing order. No new order will be created.'
            : htmlspecialchars($error) ?>
      </p>
    </div>
  </div>
</section>

<section class="section" style="padding-top:18px">
  <div class="container" style="max-width:760px">
    <div class="card" style="padding:24px 24px 26px;text-align:center">
      <?php if ($error === '' && $redirect): ?>
        <div style="width:72px;height:72px;border-radius:22px;background:rgba(59,130,246,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 18px">
          <i class="fas fa-arrow-right" style="font-size:30px;color:#3b82f6"></i>
        </div>
        <p style="font-size:14px;color:var(--text-muted);margin-bottom:16px">
          Order: <strong style="color:var(--text)"><?= htmlspecialchars($orderNo) ?></strong>
        </p>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">If you are not redirected automatically, use the button below.</p>

        <form action="<?= htmlspecialchars($redirect['url']) ?>" method="post" id="retryPaymentForm">
          <?php foreach ($redirect['fields'] as $key => $value): ?>
            <input type="hidden" name="<?= htmlspecialchars((string)$key) ?>" value="<?= htmlspecialchars((string)$value, ENT_QUOTES) ?>">
          <?php endforeach ?>
          <button type="submit" class="btn btn-primary" style="min-width:220px;justify-content:center">
            Continue to <?= htmlspecialchars(strtoupper($redirect['gateway'])) ?>
          </button>
        </form>
        <script>
          setTimeout(function () {
            var form = document.getElementById('retryPaymentForm');
            if (form) form.submit();
          }, 700);
        </script>
      <?php else: ?>
        <div style="width:72px;height:72px;border-radius:22px;background:rgba(239,68,68,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 18px">
          <i class="fas fa-circle-xmark" style="font-size:30px;color:#ef4444"></i>
        </div>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
          <a class="btn btn-primary" href="cart.php">View Cart</a>
          <a class="btn btn-ghost" href="checkout.php">Back to Checkout</a>
        </div>
      <?php endif ?>
    </div>
  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>

</body>
</html>
