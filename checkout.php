<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
ensureDeliveryRatesTable();
ensureOrderPaymentColumns();
$districts = getSriLankaDistricts();
$deliveryRatesMap = getDeliveryRatesMap();
$paymentMethods = getEnabledPaymentMethods();
$paymentMethodsConfig = $paymentMethods;
$paymentDisplayOrder = ['koko', 'payhere', 'bank_transfer', 'cod', 'whatsapp'];
$orderedPaymentMethods = [];
foreach ($paymentDisplayOrder as $paymentKey) {
    if (isset($paymentMethods[$paymentKey])) {
        $orderedPaymentMethods[] = $paymentMethods[$paymentKey];
        unset($paymentMethods[$paymentKey]);
    }
}
foreach ($paymentMethods as $paymentMethod) {
    $orderedPaymentMethods[] = $paymentMethod;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Checkout | Gadget Hub',
      'description' => 'Secure checkout for your Gadget Hub order.',
      'canonical' => 'checkout.php',
      'robots' => 'noindex,nofollow',
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

<!-- Breadcrumb -->
<div style="background:var(--bg-2);border-bottom:1px solid var(--border);padding:12px 0">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <a href="cart.php">Cart</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Checkout</span>
    </nav>
  </div>
</div>

<!-- Page Hero -->
<section class="page-hero" style="padding:40px 0 36px">
  <div class="hero-dot-bg"></div>
  <div class="page-hero-orb page-hero-orb-1"></div>
  <div class="container page-hero-inner">
    <div class="page-hero-text" data-anim="up">
      <span class="section-tag">Almost There</span>
      <h1>Complete Your <em>Order</em></h1>
    </div>
  </div>
</section>

<!-- Main -->
<section class="section" style="padding-top:36px">
  <div class="container">

    <!-- Empty cart redirect notice -->
    <div id="emptyNotice" style="display:none;text-align:center;padding:60px 20px">
      <i class="fas fa-shopping-cart" style="font-size:48px;color:var(--text-dim);margin-bottom:16px;display:block"></i>
      <h3 style="margin-bottom:8px">Your cart is empty</h3>
      <p style="color:var(--text-muted);margin-bottom:20px">Add items to your cart before checking out.</p>
      <a href="shop.php" class="btn btn-primary"><i class="fas fa-store"></i> Browse Products</a>
    </div>

    <!-- Success state -->
    <div id="successState" style="display:none;text-align:center;padding:60px 20px;max-width:540px;margin:0 auto">
      <div style="width:72px;height:72px;border-radius:50%;background:rgba(16,185,129,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
        <i class="fas fa-check-circle" style="font-size:36px;color:#10b981"></i>
      </div>
      <h2 style="margin-bottom:8px">Order Placed!</h2>
      <p style="color:var(--text-muted);margin-bottom:16px">Thank you for your order. We've received it and will contact you shortly via WhatsApp to confirm.</p>
      <div id="successOrderNum" style="background:var(--bg-2);border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:8px;padding:14px 20px;margin-bottom:24px;text-align:left">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:4px">Your Order Number</div>
        <div id="orderNumText" style="font-size:22px;font-weight:800;color:var(--text)"></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Save this for your reference</div>
      </div>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:24px">
        <i class="fas fa-envelope" style="color:var(--primary)"></i>
        A confirmation email has been sent if you provided your email address.
      </p>
      <div id="successBankDetails" style="display:none;background:rgba(212,146,10,.06);border:1px dashed rgba(212,146,10,.35);border-radius:10px;padding:16px 18px;margin:0 0 24px 0;text-align:left">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:8px">Bank Transfer Details</div>
        <div id="successBankText" style="font-size:13px;line-height:1.8;color:var(--text-muted)"></div>
      </div>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="shop.php" class="btn btn-ghost"><i class="fas fa-store"></i> Continue Shopping</a>
        <a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Go Home</a>
      </div>
    </div>

    <!-- Checkout grid -->
    <div id="checkoutGrid" style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start">

      <!-- LEFT: Customer form -->
      <div>
        <!-- Error banner -->
        <div id="errBanner" style="display:none;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#f87171;font-size:13.5px">
          <i class="fas fa-exclamation-circle"></i> <span id="errText"></span>
        </div>

        <div class="card" style="margin-bottom:20px">
          <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:20px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-user" style="color:var(--primary)"></i> Your Details
          </div>

          <form id="checkoutForm">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                  Full Name <span style="color:var(--red)">*</span>
                </label>
                <input type="text" id="ckName" class="form-input" placeholder="Your full name" required>
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                  Phone Number <span style="color:var(--red)">*</span>
                </label>
                <input type="tel" id="ckPhone" class="form-input" placeholder="e.g. 077 000 0000" required>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                  Phone Number 2 <span style="font-weight:400;text-transform:none;font-size:11px"></span>
                </label>
                <input type="tel" id="ckPhoneAlt" class="form-input" placeholder="e.g. 071 000 0000">
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                  City <span style="color:var(--red)">*</span>
                </label>
                <input type="text" id="ckCity" class="form-input" placeholder="e.g. Colombo" required>
              </div>
            </div>

            <div style="margin-bottom:16px">
              <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                District <span style="color:var(--red)">*</span>
              </label>
              <select id="ckDistrict" class="form-input" required>
                <option value="">Select District</option>
                <?php foreach ($districts as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                <?php endforeach ?>
              </select>
            </div>

            <div style="margin-bottom:16px">
              <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                Email Address <span style="color:var(--red)">*</span>
              </label>
              <input type="email" id="ckEmail" class="form-input" placeholder="your@email.com" required>
            </div>

            <div style="margin-bottom:16px">
              <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                Delivery Address <span style="color:var(--red)">*</span>
              </label>
              <textarea id="ckAddress" class="form-textarea" rows="3" placeholder="Your full delivery address including city and postal code" required></textarea>
            </div>

            <div style="margin-bottom:24px">
              <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                Order Notes <span style="font-weight:400;text-transform:none;font-size:11px">(optional)</span>
              </label>
              <textarea id="ckNotes" class="form-textarea" rows="2" placeholder="Special requests, colour preferences, delivery instructions..."></textarea>
            </div>

            <div style="margin-bottom:24px">
              <label style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:8px">
                Payment Method <span style="color:var(--red)">*</span>
              </label>
              <div id="ckPaymentMethods" style="display:flex;flex-direction:column;gap:8px">
                <?php foreach ($orderedPaymentMethods as $pm): ?>
                <label style="display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-3);cursor:pointer">
                  <input type="radio" name="payment_method" value="<?= htmlspecialchars($pm['key']) ?>" style="margin-top:2px">
                  <span style="flex:1;min-width:0">
                    <span style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                      <strong style="display:block;font-size:13px;color:var(--text)"><?= htmlspecialchars($pm['label']) ?></strong>
                      <?php if (($pm['key'] ?? '') === 'cod'): ?>
                        <img src="images/payments/cod-logo.png" alt="Cash on Delivery" style="height:24px;width:auto;display:block;flex-shrink:0">
                      <?php elseif (($pm['key'] ?? '') === 'bank_transfer'): ?>
                        <img src="images/payments/bank-transfer-logo.png" alt="Bank Transfer" style="height:24px;width:auto;display:block;flex-shrink:0">
                      <?php elseif (($pm['key'] ?? '') === 'whatsapp'): ?>
                        <img src="images/payments/whatsapp-logo.png" alt="WhatsApp Order" style="height:24px;width:auto;display:block;flex-shrink:0">
                      <?php elseif (($pm['key'] ?? '') === 'koko'): ?>
                        <img src="images/payments/koko-logo.png" alt="KOKO" style="height:24px;width:auto;display:block;flex-shrink:0">
                      <?php elseif (($pm['key'] ?? '') === 'payhere'): ?>
                        <img src="images/payments/payhere-logo.png" alt="PayHere" style="height:24px;width:auto;display:block;flex-shrink:0">
                      <?php endif ?>
                    </span>
                    <span style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($pm['description'] ?? '') ?></span>
                  </span>
                </label>
                <?php endforeach ?>
                <?php if (empty($orderedPaymentMethods)): ?>
                <div style="font-size:12px;color:#f87171">No payment methods enabled. Please enable at least one method in Admin Settings.</div>
                <?php endif ?>
              </div>
              <div id="ckBankDetails" style="display:none;margin-top:8px;padding:10px 12px;border:1px dashed var(--border);border-radius:8px;background:rgba(212,146,10,.05);font-size:12px;line-height:1.7;color:var(--text-muted)">
                <strong style="color:var(--text)">Bank Transfer Details</strong><br>
                <span id="ckBankText"></span>
              </div>
            </div>

            <button type="submit" id="placeBtn" class="btn btn-primary" style="width:100%;justify-content:center;font-size:15px;padding:14px">
              <i class="fas fa-shopping-bag"></i> Place Order
            </button>
          </form>
        </div>

        <!-- Trust badges -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
          <div style="background:var(--bg-2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center">
            <i class="fas fa-shield-alt" style="font-size:20px;color:var(--primary);margin-bottom:6px;display:block"></i>
            <div style="font-size:11.5px;font-weight:600;color:var(--text)">100% Genuine</div>
            <div style="font-size:10.5px;color:var(--text-muted)">Authentic products</div>
          </div>
          <div style="background:var(--bg-2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center">
            <i class="fas fa-truck" style="font-size:20px;color:var(--primary);margin-bottom:6px;display:block"></i>
            <div style="font-size:11.5px;font-weight:600;color:var(--text)">Island-wide Delivery</div>
            <div style="font-size:10.5px;color:var(--text-muted)">Sri Lanka</div>
          </div>
          <div style="background:var(--bg-2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center">
            <i class="fas fa-undo" style="font-size:20px;color:var(--primary);margin-bottom:6px;display:block"></i>
            <div style="font-size:11.5px;font-weight:600;color:var(--text)">Easy Returns</div>
            <div style="font-size:10.5px;color:var(--text-muted)">7-day policy</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Order summary -->
      <div style="position:sticky;top:80px">
        <div class="card">
          <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:16px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-receipt" style="color:var(--primary)"></i> Order Summary
          </div>

          <div id="ckItemsList" style="margin-bottom:16px;display:flex;flex-direction:column;gap:10px">
            <!-- rendered by JS -->
          </div>

          <div style="border-top:1px solid var(--border);padding-top:14px;margin-bottom:16px">
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:8px">
              <span>Subtotal</span><span id="ckSubtotal">Rs. 0.00</span>
            </div>
            <div style="margin-bottom:10px">
              <label style="display:block;font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--text-dim);margin-bottom:6px">
                Select District
              </label>
              <select id="ckDistrictSummary" class="form-input">
                <option value="">Select District</option>
                <?php foreach ($districts as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:12px">
              <span id="ckDeliveryLabel">Delivery</span><span id="ckDelivery" style="color:var(--green)">--</span>
            </div>
            <div id="ckHandlingFeeRow" style="display:none;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:12px">
              <span id="ckHandlingFeeLabel">Handling Fee</span><span id="ckHandlingFee">Rs. 0.00</span>
            </div>
            <div id="ckFreeDeliveryPromo" style="display:none;margin:-4px 0 12px 0">
              <span style="display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;border-radius:999px;padding:6px 12px;font-size:11px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;box-shadow:0 6px 18px rgba(34,197,94,.25)">
                <i class="fas fa-gift"></i> Special Offer: Free Delivery
              </span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:800;color:var(--text)">
              <span>Total</span><span id="ckTotal" style="color:var(--primary)">Rs. 0.00</span>
            </div>
          </div>

          <!-- <div style="background:rgba(212,146,10,.06);border:1px solid rgba(212,146,10,.2);border-radius:8px;padding:12px 14px;font-size:12px;color:var(--text-muted);line-height:1.7">
            <i class="fas fa-info-circle" style="color:var(--primary)"></i>
            We'll confirm availability and final price via WhatsApp before delivery. <strong>No online payment required.</strong>
          </div> -->

            <div style="background:rgba(212,146,10,.06);border:1px solid rgba(212,146,10,.2);border-radius:8px;padding:12px 14px;font-size:12px;color:var(--text-muted);line-height:1.7">
            <i class="fas fa-info-circle" style="color:var(--primary)"></i>
            You will receive this order <strong>Within 1-5 Business Days.</strong>
          </div>
          
        </div>

      </div>

    </div>
  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>
<script src="assets/js/animations.js"></script>

<style>
.form-input,.form-textarea{
  width:100%;background:var(--bg-3);border:1px solid var(--border);border-radius:8px;
  padding:11px 14px;color:var(--text);font-family:'Poppins',sans-serif;font-size:13.5px;
  outline:none;transition:.2s;
}
select.form-input{
  height:44px;
  line-height:44px;
  padding-top:0;
  padding-bottom:0;
}
.form-input::placeholder,.form-textarea::placeholder{
  color:var(--text-muted);
  opacity:.28;
}
.form-input:focus,.form-textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(212,146,10,.12)}
.form-textarea{resize:vertical;min-height:80px}
.btn-primary{background:var(--gold);color:#fff;border:none;border-radius:50px;padding:11px 24px;font-weight:700;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:.2s}
.btn-primary:hover{background:var(--primary-dark,#b8780a);transform:translateY(-1px)}
.btn-primary:disabled{opacity:.6;cursor:not-allowed;transform:none}
@media(max-width:860px){
  #checkoutGrid{grid-template-columns:1fr!important}
  #checkoutGrid>div:first-child{order:2}
  #checkoutGrid>div:last-child{order:1;margin-bottom:16px}
  #checkoutGrid>div:last-child{position:static!important}
}
</style>

<script>
(function () {
  const paymentMethods = <?= json_encode($paymentMethodsConfig, JSON_UNESCAPED_UNICODE) ?>;
  const checkoutStorageKey = 'gadgethub_checkout_customer';
  const items    = GadgetHubCart.getItems();
  const grid     = document.getElementById('checkoutGrid');
  const empty    = document.getElementById('emptyNotice');
  const success  = document.getElementById('successState');
  const successBankBox = document.getElementById('successBankDetails');
  const successBankText = document.getElementById('successBankText');
  const errBanner= document.getElementById('errBanner');
  const errText  = document.getElementById('errText');

  if (!items.length) {
    grid.style.display  = 'none';
    empty.style.display = 'block';
    return;
  }

  const list     = document.getElementById('ckItemsList');
  const deliveryLabelEl = document.getElementById('ckDeliveryLabel');
  const deliveryEl = document.getElementById('ckDelivery');
  const freePromoEl = document.getElementById('ckFreeDeliveryPromo');
  const districtEl = document.getElementById('ckDistrict');
  const districtSummaryEl = document.getElementById('ckDistrictSummary');
  const handlingFeeRowEl = document.getElementById('ckHandlingFeeRow');
  const handlingFeeLabelEl = document.getElementById('ckHandlingFeeLabel');
  const handlingFeeEl = document.getElementById('ckHandlingFee');
  const totalEl = document.getElementById('ckTotal');
  const subtotal = GadgetHubCart.getTotal();
  let currentDelivery = 0;
  let currentHandlingFee = 0;
  let currentHandlingFeePercent = 0;
  let currentChargeableKg = 1;
  let syncingDistrict = false;

  const fieldRefs = {
    name: document.getElementById('ckName'),
    phone: document.getElementById('ckPhone'),
    phoneAlt: document.getElementById('ckPhoneAlt'),
    city: document.getElementById('ckCity'),
    district: districtEl,
    email: document.getElementById('ckEmail'),
    address: document.getElementById('ckAddress'),
  };

  function getSelectedPaymentMethod() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    return selected ? selected.value : '';
  }

  function getHandlingFeePercent(methodKey) {
    const cfg = paymentMethods[methodKey] || {};
    const percent = parseFloat(cfg.handling_fee_percent || 0);
    return Number.isFinite(percent) ? Math.max(0, percent) : 0;
  }

  function updateHandlingFee() {
    const paymentMethod = getSelectedPaymentMethod();
    currentHandlingFeePercent = getHandlingFeePercent(paymentMethod);
    const feeBase = subtotal + currentDelivery;
    currentHandlingFee = currentHandlingFeePercent > 0
      ? Math.round((feeBase * currentHandlingFeePercent) * 100) / 10000
      : 0;

    if (handlingFeeRowEl && handlingFeeLabelEl && handlingFeeEl) {
      if (currentHandlingFee > 0) {
        handlingFeeRowEl.style.display = 'flex';
        handlingFeeLabelEl.textContent = `Handling Fee (${currentHandlingFeePercent.toFixed(2).replace(/\.00$/, '')}%)`;
        handlingFeeEl.textContent = GadgetHubCart.fmt(currentHandlingFee);
      } else {
        handlingFeeRowEl.style.display = 'none';
        handlingFeeLabelEl.textContent = 'Handling Fee';
        handlingFeeEl.textContent = GadgetHubCart.fmt(0);
      }
    }
  }

  function updateDisplayedTotal(totalOverride) {
    const total = typeof totalOverride === 'number'
      ? totalOverride
      : subtotal + currentDelivery + currentHandlingFee;
    totalEl.textContent = GadgetHubCart.fmt(total);
  }

  function saveCheckoutDetails() {
    try {
      localStorage.setItem(checkoutStorageKey, JSON.stringify({
        name: fieldRefs.name.value.trim(),
        phone: fieldRefs.phone.value.trim(),
        phoneAlt: fieldRefs.phoneAlt.value.trim(),
        city: fieldRefs.city.value.trim(),
        district: fieldRefs.district.value.trim(),
        email: fieldRefs.email.value.trim(),
        address: fieldRefs.address.value.trim(),
        paymentMethod: getSelectedPaymentMethod(),
      }));
    } catch (e) {
      // Ignore storage issues so checkout stays usable.
    }
  }

  function loadCheckoutDetails() {
    try {
      const raw = localStorage.getItem(checkoutStorageKey);
      if (!raw) return;
      const saved = JSON.parse(raw);
      if (!saved || typeof saved !== 'object') return;

      fieldRefs.name.value = saved.name || '';
      fieldRefs.phone.value = saved.phone || '';
      fieldRefs.phoneAlt.value = saved.phoneAlt || '';
      fieldRefs.city.value = saved.city || '';
      fieldRefs.district.value = saved.district || '';
      fieldRefs.email.value = saved.email || '';
      fieldRefs.address.value = saved.address || '';

      if (districtSummaryEl) {
        districtSummaryEl.value = fieldRefs.district.value;
      }

      if (saved.paymentMethod) {
        const savedPaymentRadio = document.querySelector(`input[name="payment_method"][value="${saved.paymentMethod}"]`);
        if (savedPaymentRadio) {
          savedPaymentRadio.checked = true;
        }
      }
    } catch (e) {
      // Ignore invalid saved data and keep the form empty.
    }
  }

  function renderPaymentMeta() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    const bankBox = document.getElementById('ckBankDetails');
    const bankTxt = document.getElementById('ckBankText');
    if (!bankBox || !bankTxt) return;
    if (!selected || selected.value !== 'bank_transfer') {
      bankBox.style.display = 'none';
      return;
    }
    const cfg = paymentMethods.bank_transfer || {};
    const lines = [];
    if (cfg.bank_name) lines.push('Bank: ' + cfg.bank_name);
    if (cfg.account_name) lines.push('Account Name: ' + cfg.account_name);
    if (cfg.account_number) lines.push('Account No: ' + cfg.account_number);
    if (cfg.branch) lines.push('Branch: ' + cfg.branch);
    if (cfg.instructions) lines.push(cfg.instructions);
    bankTxt.innerHTML = lines.map(l => l.replace(/</g, '&lt;')).join('<br>');
    bankBox.style.display = 'block';
  }

  function renderBankDetails(lines) {
    if (!successBankBox || !successBankText) return;
    if (!Array.isArray(lines) || !lines.length) {
      successBankBox.style.display = 'none';
      successBankText.innerHTML = '';
      return;
    }
    successBankText.innerHTML = lines
      .map(line => String(line).replace(/&/g, '&amp;').replace(/</g, '&lt;'))
      .join('<br>');
    successBankBox.style.display = 'block';
  }

  document.querySelectorAll('input[name="payment_method"]').forEach(r => {
    r.addEventListener('change', () => {
      renderPaymentMeta();
      updateHandlingFee();
      updateDisplayedTotal();
      saveCheckoutDetails();
    });
  });
  if (document.querySelector('input[name="payment_method"]')) {
    document.querySelector('input[name="payment_method"]').checked = true;
  }
  loadCheckoutDetails();
  renderPaymentMeta();

  items.forEach(item => {
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;justify-content:space-between;align-items:start;gap:10px';
    div.innerHTML = `
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--text);line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.name}</div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">Qty: ${item.qty} × ${GadgetHubCart.fmt(item.price)}</div>
      </div>
      <div style="font-size:13px;font-weight:700;color:var(--primary);flex-shrink:0">${GadgetHubCart.fmt(item.price * item.qty)}</div>
    `;
    list.appendChild(div);
  });

  document.getElementById('ckSubtotal').textContent = GadgetHubCart.fmt(subtotal);
  updateHandlingFee();
  updateDisplayedTotal();

  async function updateDeliveryPreview() {
    const district = districtEl.value.trim();
    if (!district) {
      currentDelivery = 0;
      currentChargeableKg = 1;
      deliveryLabelEl.textContent = 'Delivery';
      deliveryEl.textContent = '--';
      if (freePromoEl) freePromoEl.style.display = 'none';
      updateHandlingFee();
      updateDisplayedTotal();
      return;
    }
    try {
      const res = await fetch('api/delivery-quote.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ district, cart: items }),
      });
      const data = await res.json();
      if (!data.success || !data.has_rate) throw new Error('rate');
      currentDelivery = parseFloat(data.delivery_fee || 0);
      currentChargeableKg = Math.max(1, parseInt(data.chargeable_kg || 1, 10));
      deliveryLabelEl.textContent = `Delivery (${currentChargeableKg}kg)`;
      if (currentDelivery <= 0) {
        deliveryEl.textContent = 'Free';
        if (freePromoEl) freePromoEl.style.display = 'block';
      } else {
        deliveryEl.textContent = GadgetHubCart.fmt(currentDelivery);
        if (freePromoEl) freePromoEl.style.display = 'none';
      }
      updateHandlingFee();
      updateDisplayedTotal();
    } catch (e) {
      currentDelivery = 0;
      currentChargeableKg = 1;
      deliveryLabelEl.textContent = 'Delivery';
      deliveryEl.textContent = 'Not configured';
      if (freePromoEl) freePromoEl.style.display = 'none';
      updateHandlingFee();
      updateDisplayedTotal();
    }
  }

  function syncDistrict(sourceEl, targetEl) {
    if (!targetEl) return;
    if (targetEl.value !== sourceEl.value) {
      syncingDistrict = true;
      targetEl.value = sourceEl.value;
      syncingDistrict = false;
    }
  }

  districtEl.addEventListener('change', () => {
    if (syncingDistrict) return;
    syncDistrict(districtEl, districtSummaryEl);
    saveCheckoutDetails();
    updateDeliveryPreview();
  });

  if (districtSummaryEl) {
    districtSummaryEl.addEventListener('change', () => {
      if (syncingDistrict) return;
      syncDistrict(districtSummaryEl, districtEl);
      saveCheckoutDetails();
      updateDeliveryPreview();
    });
  }

  Object.values(fieldRefs).forEach(field => {
    field.addEventListener('input', saveCheckoutDetails);
    field.addEventListener('change', saveCheckoutDetails);
  });

  updateDeliveryPreview();

  document.getElementById('checkoutForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    errBanner.style.display = 'none';

    const name    = document.getElementById('ckName').value.trim();
    const phone   = document.getElementById('ckPhone').value.trim();
    const phoneAlt= document.getElementById('ckPhoneAlt').value.trim();
    const city    = document.getElementById('ckCity').value.trim();
    const district= document.getElementById('ckDistrict').value.trim();
    const email   = document.getElementById('ckEmail').value.trim();
    const address = document.getElementById('ckAddress').value.trim();
    const notes   = document.getElementById('ckNotes').value.trim();
    const paymentMethodEl = document.querySelector('input[name="payment_method"]:checked');
    const paymentMethod = paymentMethodEl ? paymentMethodEl.value : '';

    if (!name || !phone || !city || !district || !email || !address || !paymentMethod) {
      showError('Please fill in all required fields.');
      return;
    }

    saveCheckoutDetails();

    const btn = document.getElementById('placeBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';

    try {
      const res = await fetch('api/checkout.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ name, phone, phone_alt: phoneAlt, city, district, email, address, notes, payment_method: paymentMethod, delivery_fee_preview: currentDelivery, cart: items }),
      });

      const data = await res.json();

      if (data.success) {
        renderBankDetails(data.bank_transfer_details || []);
        const gatewayRedirect = data.payment_redirect && data.payment_redirect.url && data.payment_redirect.fields
          ? data.payment_redirect
          : (data.payhere && data.payhere.url && data.payhere.fields ? data.payhere : null);

        if (gatewayRedirect) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = gatewayRedirect.url;
          Object.entries(gatewayRedirect.fields).forEach(([k, v]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = k;
            input.value = v == null ? '' : String(v);
            form.appendChild(input);
          });
          document.body.appendChild(form);
          form.submit();
          return;
        }
        GadgetHubCart.clear();
        grid.style.display    = 'none';
        success.style.display = 'block';
        document.getElementById('orderNumText').textContent = data.order_number;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        showError(data.error || 'Something went wrong. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Place Order';
      }
    } catch (err) {
      showError('Network error. Please check your connection and try again.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Place Order';
    }
  });

  function showError(msg) {
    errText.textContent     = msg;
    errBanner.style.display = 'block';
    errBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
})();
</script>

</body>
</html>
