<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/seo.php';

function getOrderByNumber(string $orderNumber): ?array {
    ensureOrderPaymentColumns();
    if ($orderNumber === '') {
        return null;
    }

    $stmt = getDB()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    return $order ?: null;
}

function getPaymentPageConfig(string $paymentStatus, string $gateway, string $pageType = 'return'): array {
    $paymentStatus = strtolower(trim($paymentStatus));
    $gatewayLabel = $gateway === 'koko' ? 'KOKO' : 'PayHere';

    $map = [
        'paid' => [
            'title' => 'Payment Confirmed',
            'message' => 'Your payment was confirmed successfully. We will continue processing your order.',
            'icon' => 'fas fa-check-circle',
            'icon_bg' => 'rgba(16,185,129,.12)',
            'icon_color' => '#10b981',
            'tag' => 'Payment Success',
        ],
        'failed' => [
            'title' => 'Payment Failed',
            'message' => 'The payment was not completed successfully. You can try again using the same items in checkout.',
            'icon' => 'fas fa-circle-xmark',
            'icon_bg' => 'rgba(239,68,68,.12)',
            'icon_color' => '#ef4444',
            'tag' => 'Payment Failed',
        ],
        'cancelled' => [
            'title' => 'Payment Cancelled',
            'message' => "You cancelled the {$gatewayLabel} payment. You can return to checkout and pay again when you are ready.",
            'icon' => 'fas fa-ban',
            'icon_bg' => 'rgba(245,158,11,.14)',
            'icon_color' => '#f59e0b',
            'tag' => 'Payment Cancelled',
        ],
        'awaiting_payment' => [
            'title' => 'Payment Pending',
            'message' => "We have not received a successful {$gatewayLabel} confirmation yet. Please wait a moment or try the payment again.",
            'icon' => 'fas fa-hourglass-half',
            'icon_bg' => 'rgba(59,130,246,.12)',
            'icon_color' => '#3b82f6',
            'tag' => 'Awaiting Payment',
        ],
        'pending' => [
            'title' => 'Payment Pending',
            'message' => "We have not received a successful {$gatewayLabel} confirmation yet. Please wait a moment or try the payment again.",
            'icon' => 'fas fa-hourglass-half',
            'icon_bg' => 'rgba(59,130,246,.12)',
            'icon_color' => '#3b82f6',
            'tag' => 'Awaiting Payment',
        ],
    ];

    $config = $map[$paymentStatus] ?? $map['awaiting_payment'];

    if ($pageType === 'return' && $paymentStatus === 'awaiting_payment') {
        $config['message'] = "We received your {$gatewayLabel} return page, but the final payment confirmation is still pending. You can try again if this payment did not complete.";
    }

    return $config + ['status_key' => $paymentStatus];
}

function renderGatewayPaymentPage(array $options): void {
    $title = $options['title'] ?? 'Payment Status';
    $description = $options['description'] ?? 'Payment status for your Gadget Hub order.';
    $canonical = $options['canonical'] ?? '';
    $orderNumber = $options['order_number'] ?? '';
    $gateway = $options['gateway'] ?? 'Payment';
    $gatewayKey = strtolower(trim((string)$gateway));
    $tag = $options['tag'] ?? 'Payment Update';
    $message = $options['message'] ?? '';
    $statusLabel = $options['status_label'] ?? ucfirst(str_replace('_', ' ', (string)($options['payment_status'] ?? 'pending')));
    $icon = $options['icon'] ?? 'fas fa-circle-info';
    $iconBg = $options['icon_bg'] ?? 'rgba(59,130,246,.12)';
    $iconColor = $options['icon_color'] ?? '#3b82f6';
    $transactionId = $options['transaction_id'] ?? '';
    $showPayAgain = !empty($options['show_pay_again']);
    $showAccount = !empty($options['show_account']);
    $primaryHref = $options['primary_href'] ?? 'index.php';
    $primaryLabel = $options['primary_label'] ?? 'Go Home';
    $secondaryHref = $options['secondary_href'] ?? 'cart.php';
    $secondaryLabel = $options['secondary_label'] ?? 'View Cart';
    $extraNote = $options['extra_note'] ?? '';
    $autoRefreshSeconds = max(0, (int)($options['auto_refresh_seconds'] ?? 0));

    $paymentPresentation = getPaymentMethodPresentation($gatewayKey === 'koko' ? 'koko' : 'payhere');
    $paymentLogo = $paymentPresentation['image'] ?? '';
    $paymentAlt = $paymentPresentation['alt'] ?? $gateway;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => $title . ' | Gadget Hub',
      'description' => $description,
      'canonical' => $canonical,
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
      <span class="section-tag"><?= htmlspecialchars($tag) ?></span>
      <h1><?= htmlspecialchars($title) ?></h1>
      <p style="max-width:640px;color:var(--text-muted)"><?= htmlspecialchars($message) ?></p>
    </div>
  </div>
</section>

<section class="section" style="padding-top:18px">
  <div class="container" style="max-width:860px">
    <div class="card" style="padding:24px 24px 26px">
      <div style="display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:center;margin-bottom:22px">
        <div style="display:flex;align-items:center;gap:16px;min-width:0">
          <div style="width:72px;height:72px;border-radius:22px;background:<?= htmlspecialchars($iconBg) ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="<?= htmlspecialchars($icon) ?>" style="font-size:32px;color:<?= htmlspecialchars($iconColor) ?>"></i>
          </div>
          <div style="min-width:0">
            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-dim);margin-bottom:5px"><?= htmlspecialchars($gateway) ?></div>
            <div style="font-size:24px;font-weight:800;color:var(--text)"><?= htmlspecialchars($statusLabel) ?></div>
          </div>
        </div>
        <?php if ($paymentLogo): ?>
          <img src="<?= htmlspecialchars($paymentLogo) ?>" alt="<?= htmlspecialchars($paymentAlt) ?>" style="height:42px;width:auto;display:block">
        <?php endif ?>
      </div>

      <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:20px">
        <div style="padding:16px 18px;border:1px solid var(--border);border-radius:14px;background:var(--bg-2)">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-dim);margin-bottom:6px">Order Number</div>
          <div style="font-size:15px;font-weight:700;color:var(--text)"><?= htmlspecialchars($orderNumber ?: 'Not available') ?></div>
        </div>
        <div style="padding:16px 18px;border:1px solid var(--border);border-radius:14px;background:var(--bg-2)">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-dim);margin-bottom:6px">Transaction ID</div>
          <div style="font-size:15px;font-weight:700;color:var(--text);word-break:break-word"><?= htmlspecialchars($transactionId ?: 'Pending / Not available') ?></div>
        </div>
      </div>

      <?php if ($extraNote !== ''): ?>
      <div style="padding:14px 16px;border:1px dashed rgba(212,146,10,.35);border-radius:12px;background:rgba(212,146,10,.06);font-size:13px;color:var(--text-muted);line-height:1.7;margin-bottom:20px">
        <?= nl2br(htmlspecialchars($extraNote)) ?>
      </div>
      <?php endif ?>

      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-primary" href="<?= htmlspecialchars($primaryHref) ?>" style="min-width:160px;justify-content:center">
          <?= htmlspecialchars($primaryLabel) ?>
        </a>
        <a class="btn btn-ghost" href="<?= htmlspecialchars($secondaryHref) ?>" style="min-width:150px;justify-content:center">
          <?= htmlspecialchars($secondaryLabel) ?>
        </a>
        <?php if ($showPayAgain): ?>
        <a class="btn btn-gold" href="pay-again.php?order=<?= urlencode((string)$orderNumber) ?>" style="min-width:150px;justify-content:center">
          <i class="fas fa-rotate-right"></i> Pay Again
        </a>
        <?php endif ?>
        <?php if ($showAccount): ?>
        <a class="btn btn-ghost" href="account.php" style="min-width:150px;justify-content:center">
          <i class="fas fa-user"></i> My Account
        </a>
        <?php endif ?>
      </div>
    </div>
  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>
<?php if ($autoRefreshSeconds > 0): ?>
<script>
  window.setTimeout(function () {
    window.location.reload();
  }, <?= $autoRefreshSeconds * 1000 ?>);
</script>
<?php endif; ?>

</body>
</html>
<?php
}
