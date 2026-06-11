<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Order Detail';
    $activePage = 'orders';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();
ensureOrderCustomerColumns();
$id = (int)get('id');
if (!$id) { flash('error','Invalid order.'); redirect(BASE_URL.'admin/orders.php'); }

$order = $db->prepare('SELECT * FROM orders WHERE id = ?');
$order->execute([$id]);
$order = $order->fetch();
if (!$order) { flash('error','Order not found.'); redirect(BASE_URL.'admin/orders.php'); }
$order = normalizeOrderDisplayData($order);

// Handle notes save (POST) 
if (isPost() && post('action') === 'save_notes') {
    $notes = post('notes');
    $db->prepare('UPDATE orders SET notes=? WHERE id=?')->execute([$notes, $id]);
    if (isAjax()) jsonOk('Notes saved.');
    flash('success', 'Notes saved.');
    redirect(BASE_URL . "admin/order-detail.php?id=$id");
}

$items = json_decode($order['items_json'], true) ?? [];

$statusBadge = [
    'pending'    => ['badge-yellow', 'fas fa-clock'],
    'confirmed'  => ['badge-blue',   'fas fa-check'],
    'processing' => ['badge-purple', 'fas fa-cog'],
    'dispatched' => ['badge-cyan',   'fas fa-truck'],
    'delivered'  => ['badge-green',  'fas fa-check-circle'],
    'cancelled'  => ['badge-red',    'fas fa-times-circle'],
];
[$badgeClass, $badgeIcon] = $statusBadge[$order['status']] ?? ['badge-gray','fas fa-circle'];

$timeline = ['pending','confirmed','processing','dispatched','delivered'];
$currentStep = array_search($order['status'], $timeline);

$sourceBadge = ['whatsapp'=>'badge-green','website'=>'badge-blue','instore'=>'badge-yellow'];
$paymentPresentation = getPaymentMethodPresentation((string)($order['payment_method'] ?? ''));
$paymentLabel = $paymentPresentation['label'];
$paymentImage = $paymentPresentation['image'];
$paymentAlt = $paymentPresentation['alt'];
$paymentStatus = (string)($order['payment_status'] ?? 'pending');
$paymentStatusMap = [
    'pending'          => ['Pending', 'badge-yellow', 'fas fa-clock'],
    'awaiting_payment' => ['Awaiting Payment', 'badge-blue', 'fas fa-hourglass-half'],
    'paid'             => ['Paid', 'badge-green', 'fas fa-check-circle'],
    'failed'           => ['Failed', 'badge-red', 'fas fa-times-circle'],
    'cancelled'        => ['Cancelled', 'badge-gray', 'fas fa-ban'],
    'refunded'         => ['Refunded', 'badge-purple', 'fas fa-undo'],
];
[$paymentStatusText, $paymentBadgeClass, $paymentBadgeIcon] = $paymentStatusMap[$paymentStatus] ?? [ucfirst(str_replace('_', ' ', $paymentStatus)), 'badge-gray', 'fas fa-circle'];
$isKokoOrder = ($order['payment_method'] ?? '') === 'koko';
$paymentMeta = $order['payment_meta_array'] ?? [];
$retryCount = (int)($paymentMeta['retry_count'] ?? 0);
$lastRetryAt = trim((string)($paymentMeta['last_retry_at'] ?? ''));
$lastRetryGateway = trim((string)($paymentMeta['last_retry_gateway'] ?? ''));
?>

<div class="page-header">
  <div>
    <h1><?= htmlspecialchars($order['order_number']) ?></h1>
    <p>Placed <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
  </div>
  <div class="ph-actions">
    <a href="orders.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    <?php if ($order['customer_phone']): ?>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$order['customer_phone']) ?>"
         target="_blank" rel="noopener" class="btn btn-gold">
        <i class="fab fa-whatsapp"></i> WhatsApp Customer
      </a>
    <?php endif ?>
  </div>
</div>

<!-- Status timeline -->
<?php if ($order['status'] !== 'cancelled'): ?>
<div class="card" style="margin-bottom:20px;padding:24px 28px">
  <div style="display:flex;align-items:center;justify-content:space-between;position:relative">
    <!-- Line -->
    <div style="position:absolute;top:18px;left:10%;right:10%;height:2px;background:var(--border);z-index:0"></div>
    <div id="tlBar" style="position:absolute;top:18px;left:10%;height:2px;background:var(--gold);z-index:1;
      width:<?= $currentStep===false?0:min(100,($currentStep/4)*100) ?>%;transition:.5s"></div>

    <?php foreach ($timeline as $i => $step):
      $done    = $currentStep !== false && $i <= $currentStep;
      $current = $currentStep !== false && $i === $currentStep;
    ?>
    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;z-index:2;flex:1">
      <div class="tl-circle" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;
        background:<?= $done?'var(--gold)':'var(--bg-3)' ?>;
        border:2px solid <?= $done?'transparent':'var(--border)' ?>;
        color:<?= $done?'#fff':'var(--text-dim)' ?>;
        box-shadow:<?= $current?'0 0 0 4px rgba(212,146,10,.2)':'' ?>">
        <i class="fas <?= $done?'fa-check':'fa-circle' ?> fa-sm"></i>
      </div>
      <span class="tl-label" style="font-size:11.5px;font-weight:<?= $current?'700':'400' ?>;color:<?= $done?'var(--text)':'var(--text-dim)' ?>;text-align:center">
        <?= ucfirst($step) ?>
      </span>
    </div>
    <?php endforeach ?>
  </div>
</div>
<?php endif ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

  <!-- LEFT -->
  <div style="display:flex;flex-direction:column;gap:20px">

    <!-- Items -->
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:18px 24px;border-bottom:1px solid var(--border)">
        <div class="card-title" style="margin:0">Order Items</div>
      </div>
      <?php if ($items): ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Product</th>
            <th style="text-align:center">Qty</th>
            <th style="text-align:right">Unit Price</th>
            <th style="text-align:right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td>
              <div style="font-weight:500;font-size:13px"><?= htmlspecialchars($item['name'] ?? '-') ?></div>
              <?php if (!empty($item['category'])): ?>
                <div style="font-size:11.5px;color:var(--text-muted)"><?= htmlspecialchars($item['category']) ?></div>
              <?php endif ?>
            </td>
            <td style="text-align:center;font-weight:600"><?= (int)($item['qty'] ?? $item['quantity'] ?? 1) ?></td>
            <td style="text-align:right;color:var(--text-muted)"><?= fmtPrice((float)($item['price'] ?? 0)) ?></td>
            <td style="text-align:right;font-weight:700;color:var(--primary)">
              <?= fmtPrice((float)($item['price'] ?? 0) * (int)($item['qty'] ?? $item['quantity'] ?? 1)) ?>
            </td>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
      <!-- Totals -->
      <div style="padding:16px 24px;border-top:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:8px">
          <span>Subtotal</span><span><?= fmtPrice((float)$order['subtotal']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:12px">
          <span>Delivery</span>
          <span><?= $order['delivery_charge']>0 ? fmtPrice((float)$order['delivery_charge']) : '<span style="color:var(--green)">Free</span>' ?></span>
        </div>
        <?php if ((float)($order['handling_fee'] ?? 0) > 0): ?>
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:12px">
          <span>Handling Fee</span>
          <span><?= fmtPrice((float)$order['handling_fee']) ?></span>
        </div>
        <?php endif ?>
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:var(--text);border-top:1px solid var(--border);padding-top:12px">
          <span>Total</span><span style="color:var(--primary)"><?= fmtPrice((float)$order['total']) ?></span>
        </div>
      </div>
      <?php else: ?>
        <div class="empty-state" style="padding:40px"><i class="fas fa-box-open"></i><p>No item data available.</p></div>
      <?php endif ?>
    </div>

    <!-- Notes -->
    <div class="card">
      <div class="card-title"><i class="fas fa-sticky-note" style="color:var(--primary)"></i> Order Notes</div>
      <form method="POST" id="notesForm">
        <input type="hidden" name="action" value="save_notes">
        <textarea name="notes" class="form-textarea" rows="4"
          placeholder="Add internal notes about this order..."><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
        <button type="submit" class="btn btn-ghost" style="margin-top:12px"><i class="fas fa-save"></i> Save Notes</button>
      </form>
    </div>

  </div>

  <!-- RIGHT -->
  <div style="display:flex;flex-direction:column;gap:20px;position:sticky;top:80px">

    <!-- Status update -->
    <div class="card">
      <div class="card-title"><i class="fas fa-exchange-alt" style="color:var(--primary)"></i> Update Status</div>
      <div style="margin-bottom:16px">
        <span class="badge <?= $badgeClass ?>" id="currentStatusBadge" style="font-size:13px;padding:6px 16px">
          <i class="<?= $badgeIcon ?>" id="currentStatusIcon"></i> <span id="currentStatusText"><?= ucfirst($order['status']) ?></span>
        </span>
      </div>
      <div class="form-group" style="margin-bottom:14px">
        <label class="form-label">Change to</label>
        <select id="statusSelect" class="form-select">
          <?php foreach (['pending','confirmed','processing','dispatched','delivered','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <button type="button" id="updateStatusBtn" class="btn btn-gold" style="width:100%;justify-content:center">
        <i class="fas fa-save"></i> Update Status
      </button>
    </div>

    <div class="card">
      <div class="card-title"><i class="fas fa-credit-card" style="color:var(--primary)"></i> Payment Status</div>
      <div style="display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--bg-2);margin-bottom:16px">
        <?php if ($paymentImage): ?>
          <img src="<?= htmlspecialchars(BASE_URL . ltrim($paymentImage, '/')) ?>" alt="<?= htmlspecialchars($paymentAlt) ?>" style="height:34px;width:auto;display:block;flex-shrink:0">
        <?php else: ?>
          <div style="width:34px;height:34px;border-radius:8px;background:var(--bg-3);display:flex;align-items:center;justify-content:center;color:var(--text-muted);flex-shrink:0">
            <i class="fas fa-credit-card"></i>
          </div>
        <?php endif ?>
        <div style="min-width:0">
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted)">Payment Method</div>
          <div style="font-size:14px;font-weight:700;color:var(--text)"><?= htmlspecialchars($paymentLabel) ?></div>
        </div>
      </div>
      <div style="margin-bottom:16px">
        <span class="badge <?= $paymentBadgeClass ?>" id="currentPaymentStatusBadge" style="font-size:13px;padding:6px 16px">
          <i class="<?= $paymentBadgeIcon ?>" id="currentPaymentStatusIcon"></i> <span id="currentPaymentStatusText"><?= htmlspecialchars($paymentStatusText) ?></span>
        </span>
      </div>
      <div class="form-group" style="margin-bottom:14px">
        <label class="form-label">Change to</label>
        <select id="paymentStatusSelect" class="form-select">
          <?php foreach ($paymentStatusMap as $key => [$label]): ?>
            <option value="<?= htmlspecialchars($key) ?>" <?= $paymentStatus === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <button type="button" id="updatePaymentStatusBtn" class="btn btn-ghost" style="width:100%;justify-content:center">
        <i class="fas fa-save"></i> Update Payment Status
      </button>
      <?php if ($isKokoOrder): ?>
      <button type="button" id="syncKokoPaymentBtn" class="btn btn-gold" style="width:100%;justify-content:center;margin-top:10px">
        <i class="fas fa-rotate"></i> Check KOKO Status
      </button>
      <div style="margin-top:10px;font-size:11.5px;color:var(--text-muted);line-height:1.6">
        Pulls the latest payment result from KOKO `orderView` and updates this order.
      </div>
      <?php endif ?>
      <?php if ($paymentMeta): ?>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:8px">
        <?php if ($retryCount > 0): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Retry Attempts:</strong> <?= $retryCount ?></div>
        <?php endif ?>
        <?php if ($lastRetryAt !== ''): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Last Retry:</strong> <?= htmlspecialchars(date('d M Y, h:i A', strtotime($lastRetryAt))) ?><?= $lastRetryGateway !== '' ? ' via ' . htmlspecialchars(strtoupper($lastRetryGateway)) : '' ?></div>
        <?php endif ?>
        <?php if (!empty($paymentMeta['bank_name'])): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Bank:</strong> <?= htmlspecialchars((string)$paymentMeta['bank_name']) ?></div>
        <?php endif ?>
        <?php if (!empty($paymentMeta['account_name'])): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Account Name:</strong> <?= htmlspecialchars((string)$paymentMeta['account_name']) ?></div>
        <?php endif ?>
        <?php if (!empty($paymentMeta['account_number'])): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Account No:</strong> <?= htmlspecialchars((string)$paymentMeta['account_number']) ?></div>
        <?php endif ?>
        <?php if (!empty($paymentMeta['branch'])): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Branch:</strong> <?= htmlspecialchars((string)$paymentMeta['branch']) ?></div>
        <?php endif ?>
        <?php if (!empty($paymentMeta['instructions'])): ?>
          <div style="font-size:12px;color:var(--text-muted);line-height:1.6"><strong style="color:var(--text)">Instructions:</strong><br><?= nl2br(htmlspecialchars((string)$paymentMeta['instructions'])) ?></div>
        <?php endif ?>
        <?php if (!empty($paymentMeta['koko_order_view']['status'])): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Last KOKO Sync:</strong> <?= htmlspecialchars((string)$paymentMeta['koko_order_view']['status']) ?><?php if (!empty($paymentMeta['koko_order_view']['checked_at'])): ?> on <?= htmlspecialchars(date('d M Y, h:i A', strtotime((string)$paymentMeta['koko_order_view']['checked_at']))) ?><?php endif ?></div>
        <?php endif ?>
        <?php if (!empty($paymentMeta['payhere']['status_code'])): ?>
          <div style="font-size:12px;color:var(--text-muted)"><strong style="color:var(--text)">Last PayHere Status Code:</strong> <?= (int)$paymentMeta['payhere']['status_code'] ?><?php if (!empty($paymentMeta['payhere']['updated_at'])): ?> on <?= htmlspecialchars(date('d M Y, h:i A', strtotime((string)$paymentMeta['payhere']['updated_at']))) ?><?php endif ?></div>
        <?php endif ?>
      </div>
      <?php endif ?>
    </div>

    <!-- Customer info -->
    <div class="card">
      <div class="card-title"><i class="fas fa-user" style="color:var(--primary)"></i> Customer</div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">Name</div>
          <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($order['customer_name']) ?></div>
        </div>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">Phone</div>
          <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" style="color:var(--primary);font-weight:500">
            <?= htmlspecialchars($order['customer_phone']) ?>
          </a>
        </div>
        <?php if (!empty($order['customer_phone_alt_display'])): ?>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">Phone 2</div>
          <a href="tel:<?= htmlspecialchars($order['customer_phone_alt_display']) ?>" style="color:var(--primary);font-weight:500">
            <?= htmlspecialchars($order['customer_phone_alt_display']) ?>
          </a>
        </div>
        <?php endif ?>
        <?php if ($order['customer_email']): ?>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">Email</div>
          <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>" style="color:var(--primary);font-size:13px">
            <?= htmlspecialchars($order['customer_email']) ?>
          </a>
        </div>
        <?php endif ?>
        <?php if (!empty($order['customer_city_display'])): ?>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">City</div>
          <div style="font-size:13px;color:var(--text-2);line-height:1.6"><?= htmlspecialchars($order['customer_city_display']) ?></div>
        </div>
        <?php endif ?>
        <?php if (!empty($order['customer_district_display'])): ?>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">District</div>
          <div style="font-size:13px;color:var(--text-2);line-height:1.6"><?= htmlspecialchars($order['customer_district_display']) ?></div>
        </div>
        <?php endif ?>
        <?php if (!empty($order['customer_address_display'])): ?>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">Address</div>
          <div style="font-size:13px;color:var(--text-2);line-height:1.6"><?= nl2br(htmlspecialchars($order['customer_address_display'])) ?></div>
        </div>
        <?php endif ?>
        <?php if (!empty($order['notes_display'])): ?>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:3px">Customer Notes</div>
          <div style="font-size:13px;color:var(--text-2);line-height:1.6"><?= nl2br(htmlspecialchars($order['notes_display'])) ?></div>
        </div>
        <?php endif ?>
      </div>
    </div>

    <!-- Order meta -->
    <div class="card">
      <div class="card-title"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Order Info</div>
      <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Order #</span>
          <strong><?= htmlspecialchars($order['order_number']) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Source</span>
          <span class="badge <?= $sourceBadge[$order['source']] ?? 'badge-gray' ?>"><?= ucfirst($order['source']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Payment</span>
          <span><?= htmlspecialchars($paymentLabel) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Payment Status</span>
          <span class="badge <?= $paymentBadgeClass ?>" id="metaPaymentStatusBadge">
            <i class="<?= $paymentBadgeIcon ?>" id="metaPaymentStatusIcon"></i>
            <span id="metaPaymentStatusText"><?= htmlspecialchars($paymentStatusText) ?></span>
          </span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Placed</span>
          <span><?= date('d M Y', strtotime($order['created_at'])) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Updated</span>
          <span><?= date('d M Y', strtotime($order['updated_at'])) ?></span>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
  var orderId = <?= $order['id'] ?>;

  var statusMeta = {
    pending:    { badge: 'badge-yellow', icon: 'fas fa-clock' },
    confirmed:  { badge: 'badge-blue',   icon: 'fas fa-check' },
    processing: { badge: 'badge-purple', icon: 'fas fa-cog' },
    dispatched: { badge: 'badge-cyan',   icon: 'fas fa-truck' },
    delivered:  { badge: 'badge-green',  icon: 'fas fa-check-circle' },
    cancelled:  { badge: 'badge-red',    icon: 'fas fa-times-circle' }
  };

  var timeline = ['pending', 'confirmed', 'processing', 'dispatched', 'delivered'];
  var paymentStatusMeta = {
    pending:          { badge: 'badge-yellow', icon: 'fas fa-clock', text: 'Pending' },
    awaiting_payment: { badge: 'badge-blue', icon: 'fas fa-hourglass-half', text: 'Awaiting Payment' },
    paid:             { badge: 'badge-green', icon: 'fas fa-check-circle', text: 'Paid' },
    failed:           { badge: 'badge-red', icon: 'fas fa-times-circle', text: 'Failed' },
    cancelled:        { badge: 'badge-gray', icon: 'fas fa-ban', text: 'Cancelled' },
    refunded:         { badge: 'badge-purple', icon: 'fas fa-undo', text: 'Refunded' }
  };

  var badgeEl  = document.getElementById('currentStatusBadge');
  var iconEl   = document.getElementById('currentStatusIcon');
  var textEl   = document.getElementById('currentStatusText');
  var selectEl = document.getElementById('statusSelect');
  var btn      = document.getElementById('updateStatusBtn');
  var paymentBadgeEl = document.getElementById('currentPaymentStatusBadge');
  var paymentIconEl = document.getElementById('currentPaymentStatusIcon');
  var paymentTextEl = document.getElementById('currentPaymentStatusText');
  var paymentMetaBadgeEl = document.getElementById('metaPaymentStatusBadge');
  var paymentMetaIconEl = document.getElementById('metaPaymentStatusIcon');
  var paymentMetaTextEl = document.getElementById('metaPaymentStatusText');
  var paymentSelectEl = document.getElementById('paymentStatusSelect');
  var paymentBtn = document.getElementById('updatePaymentStatusBtn');
  var syncKokoBtn = document.getElementById('syncKokoPaymentBtn');

  function applyStatus(status) {
    var meta = statusMeta[status];
    if (!meta || !badgeEl) return;

    // Update badge
    badgeEl.className = 'badge ' + meta.badge;
    badgeEl.style.cssText = 'font-size:13px;padding:6px 16px';
    iconEl.className  = meta.icon;
    textEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);

    // Update timeline circles and progress bar
    var step = timeline.indexOf(status);
    var circles = document.querySelectorAll('.tl-circle');
    var labels  = document.querySelectorAll('.tl-label');
    var bar     = document.getElementById('tlBar');

    circles.forEach(function (el, i) {
      var done = step !== -1 && i <= step;
      el.style.background   = done ? 'var(--gold)' : 'var(--bg-3)';
      el.style.borderColor  = done ? 'transparent' : 'var(--border)';
      el.style.color        = done ? '#fff' : 'var(--text-dim)';
      el.style.boxShadow    = (step !== -1 && i === step) ? '0 0 0 4px rgba(212,146,10,.2)' : '';
      el.querySelector('i').className = (done ? 'fas fa-check' : 'fas fa-circle') + ' ' + 'fa-sm';
    });
    labels.forEach(function (el, i) {
      var done = step !== -1 && i <= step;
      el.style.color      = done ? 'var(--text)' : 'var(--text-dim)';
      el.style.fontWeight = (step !== -1 && i === step) ? '700' : '400';
    });
    if (bar) bar.style.width = (step === -1 ? 0 : Math.min(100, (step / 4) * 100)) + '%';
  }

  function applyPaymentStatus(status) {
    var meta = paymentStatusMeta[status] || {
      badge: 'badge-gray',
      icon: 'fas fa-circle',
      text: status ? status.replace(/_/g, ' ').replace(/\b\w/g, function (char) { return char.toUpperCase(); }) : 'Unknown'
    };

    if (paymentBadgeEl) {
      paymentBadgeEl.className = 'badge ' + meta.badge;
      paymentBadgeEl.style.cssText = 'font-size:13px;padding:6px 16px';
    }
    if (paymentIconEl) paymentIconEl.className = meta.icon;
    if (paymentTextEl) paymentTextEl.textContent = meta.text;

    if (paymentMetaBadgeEl) {
      paymentMetaBadgeEl.className = 'badge ' + meta.badge;
    }
    if (paymentMetaIconEl) paymentMetaIconEl.className = meta.icon;
    if (paymentMetaTextEl) paymentMetaTextEl.textContent = meta.text;
    if (paymentSelectEl && paymentSelectEl.value !== status) {
      paymentSelectEl.value = status;
    }
  }

  if (btn) {
    btn.addEventListener('click', function () {
      var status = selectEl.value;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating…';

      fetch('<?= BASE_URL ?>admin/api/update-order-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId, status: status })
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Update Status';
        if (res.success) {
          applyStatus(status);
          adminSnackbar('Status updated to ' + status.charAt(0).toUpperCase() + status.slice(1), 'success');
        } else {
          adminSnackbar(res.error || 'Update failed.', 'error');
        }
      })
      .catch(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Update Status';
        adminSnackbar('Network error. Try again.', 'error');
      });
    });
  }

  if (paymentBtn) {
    paymentBtn.addEventListener('click', function () {
      var paymentStatus = paymentSelectEl.value;
      paymentBtn.disabled = true;
      paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

      fetch('<?= BASE_URL ?>admin/api/update-order-payment-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId, payment_status: paymentStatus })
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        paymentBtn.disabled = false;
        paymentBtn.innerHTML = '<i class="fas fa-save"></i> Update Payment Status';
        if (res.success) {
          applyPaymentStatus(paymentStatus);
          adminSnackbar('Payment status updated to ' + (paymentStatusMeta[paymentStatus] ? paymentStatusMeta[paymentStatus].text : paymentStatus), 'success');
        } else {
          adminSnackbar(res.error || 'Payment status update failed.', 'error');
        }
      })
      .catch(function () {
        paymentBtn.disabled = false;
        paymentBtn.innerHTML = '<i class="fas fa-save"></i> Update Payment Status';
        adminSnackbar('Network error. Try again.', 'error');
      });
    });
  }

  if (syncKokoBtn) {
    syncKokoBtn.addEventListener('click', function () {
      syncKokoBtn.disabled = true;
      syncKokoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';

      fetch('<?= BASE_URL ?>admin/api/koko-order-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId })
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        syncKokoBtn.disabled = false;
        syncKokoBtn.innerHTML = '<i class="fas fa-rotate"></i> Check KOKO Status';
        if (res.success) {
          applyPaymentStatus(res.payment_status);
          adminSnackbar(
            'KOKO status: ' + res.koko_status + (res.signature_verified ? ' (verified)' : ''),
            'success'
          );
        } else {
          adminSnackbar(res.error || 'Could not fetch KOKO status.', 'error');
        }
      })
      .catch(function () {
        syncKokoBtn.disabled = false;
        syncKokoBtn.innerHTML = '<i class="fas fa-rotate"></i> Check KOKO Status';
        adminSnackbar('Network error. Try again.', 'error');
      });
    });
  }
})();

// Notes AJAX save
(function () {
  var notesForm = document.getElementById('notesForm');
  if (!notesForm) return;
  notesForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn  = notesForm.querySelector('[type="submit"]');
    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    var data = new FormData(notesForm);
    fetch(location.href, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: data
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      btn.disabled = false;
      btn.innerHTML = orig;
      adminSnackbar(res.message, res.success ? 'success' : 'error');
    })
    .catch(function () {
      btn.disabled = false;
      btn.innerHTML = orig;
      adminSnackbar('Network error. Try again.', 'error');
    });
  });
})();
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
