<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'FAQ | Gadget Hub',
      'description' => 'Frequently asked questions about ordering, payments, shipping, returns, privacy, and account use at GADGET HUB.',
      'canonical' => 'faq.php',
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
<script src="components/header.js"></script>

<section class="page-hero">
  <div class="hero-dot-bg"></div>
  <div class="page-hero-orb page-hero-orb-1"></div>
  <div class="page-hero-orb page-hero-orb-2"></div>
  <div class="container page-hero-inner">
    <div class="page-hero-text" data-anim="up">
      <span class="section-tag">Help Centre</span>
      <h1>Frequently Asked <em>Questions</em></h1>
      <p>Quick answers about ordering, payments, delivery, returns, privacy, and account use.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>FAQ</span>
    </nav>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="faq-page-grid">

      <aside class="faq-cats-nav" data-anim="left">
        <h4>Categories</h4>
        <a href="#orders" class="active"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="#payments"><i class="fas fa-credit-card"></i> Payments</a>
        <a href="#delivery"><i class="fas fa-truck"></i> Delivery</a>
        <a href="#returns"><i class="fas fa-undo-alt"></i> Returns</a>
        <a href="#privacy"><i class="fas fa-shield-alt"></i> Privacy</a>
        <a href="#account"><i class="fas fa-user-circle"></i> Account &amp; Help</a>
      </aside>

      <div data-anim="right">

        <div class="faq-search-bar">
          <i class="fas fa-search"></i>
          <input type="text" id="faqSearch" placeholder="Search questions...">
        </div>

        <div class="faq-no-results" id="faqNoResults">
          <i class="fas fa-search"></i>
          <p>No questions found for your search. Try different keywords.</p>
          <a href="contact.php" class="btn btn-gold" style="margin-top:16px"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>

        <div class="faq-cat-group" id="orders">
          <div class="faq-cat-title">
            <div class="fct-ico"><i class="fas fa-shopping-bag"></i></div>
            <h3>Orders</h3>
          </div>

          <div class="faq-item">
            <button class="faq-q">How do I place an order?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>You can place an order by selecting products through the website and following the checkout flow. If you need pre-purchase help, you can also use the <a href="contact.php">Contact page</a> before ordering.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">Is my order confirmed immediately after I submit it?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Not always. An order may still require payment authorization, stock review, fraud checks, or manual confirmation before it is fully accepted.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">Can an order be cancelled or declined?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Yes. Orders may be cancelled or declined because of stock issues, pricing errors, duplicate submissions, payment concerns, suspected fraud, or other operational reasons. If you need help with an order, use the <a href="contact.php">Contact page</a>.</p>
            </div></div>
          </div>
        </div>

        <div class="faq-cat-group" id="payments">
          <div class="faq-cat-title">
            <div class="fct-ico"><i class="fas fa-credit-card"></i></div>
            <h3>Payments</h3>
          </div>

          <div class="faq-item">
            <button class="faq-q">What payment methods are available?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Available payment methods are shown on the website or during checkout and may change from time to time depending on product type, delivery method, or payment service availability.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">Do you store my full card details?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>No. Full card credentials and sensitive payment authentication data are not stored on this website. Online payments are handled by the relevant payment provider or gateway.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">What happens if my payment fails?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>A payment may fail because of bank restrictions, incorrect details, authorization failure, session timeout, or payment gateway issues. If a problem continues, please use the <a href="contact.php">Contact page</a> for support.</p>
            </div></div>
          </div>
        </div>

        <div class="faq-cat-group" id="delivery">
          <div class="faq-cat-title">
            <div class="fct-ico"><i class="fas fa-truck"></i></div>
            <h3>Delivery</h3>
          </div>

          <div class="faq-item">
            <button class="faq-q">Do you deliver across Sri Lanka?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>We deliver to serviceable locations within Sri Lanka, subject to courier coverage, product restrictions, and operational conditions.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">How long does delivery take?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Delivery timing depends on the destination, payment confirmation, order processing, courier schedules, weekends, and holidays. Any estimated timing should be treated as approximate unless clearly stated otherwise.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">Can I track my order?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>When tracking is available, shipment details may be shared after dispatch. If you need help checking delivery progress, use the <a href="contact.php">Contact page</a>.</p>
            </div></div>
          </div>
        </div>

        <div class="faq-cat-group" id="returns">
          <div class="faq-cat-title">
            <div class="fct-ico"><i class="fas fa-undo-alt"></i></div>
            <h3>Returns</h3>
          </div>

          <div class="faq-item">
            <button class="faq-q">When can I request a return?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Returns are generally reviewed for damaged, defective, incorrect, incomplete, or materially misdescribed items, subject to the conditions in our <a href="returns.php">Return Policy</a>.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">Should I send the item back immediately?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>No. Please wait for instructions after submitting your request through the <a href="contact.php">Contact page</a>. Unapproved returns may be delayed or refused.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">How long do refunds take?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Once an eligible returned item is received and inspected, approved refund processing usually begins within 3 to 7 business days. Final settlement timing may depend on banks or payment providers.</p>
            </div></div>
          </div>
        </div>

        <div class="faq-cat-group" id="privacy">
          <div class="faq-cat-title">
            <div class="fct-ico"><i class="fas fa-shield-alt"></i></div>
            <h3>Privacy</h3>
          </div>

          <div class="faq-item">
            <button class="faq-q">What information do you collect?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>We may collect identity, contact, order, communication, and technical information needed to operate the website and fulfill purchases. See our <a href="privacy.php">Privacy Policy</a> for full details.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">Do you sell my personal information?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>No. We do not sell personal information. Information may be shared only where reasonably necessary for services such as payment processing, delivery, website support, or legal compliance.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">How can I ask a privacy-related question?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Please use our <a href="contact.php">Contact page</a> for privacy, data, or policy-related questions.</p>
            </div></div>
          </div>
        </div>

        <div class="faq-cat-group" id="account">
          <div class="faq-cat-title">
            <div class="fct-ico"><i class="fas fa-user-circle"></i></div>
            <h3>Account &amp; Help</h3>
          </div>

          <div class="faq-item">
            <button class="faq-q">Do I need an account to shop?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Account requirements may vary depending on how the website is being used, but customers can review the website flow at checkout to see whether account creation is necessary for a particular purchase.</p>
            </div></div>
          </div>

          <div class="faq-item">
            <button class="faq-q">Where should I ask for help if my issue is not listed here?<i class="fas fa-chevron-down faq-chevron"></i></button>
            <div class="faq-a"><div class="faq-a-inner">
              <p>Please submit your question through the <a href="contact.php">Contact page</a>. Include any order reference or relevant details so we can review it faster.</p>
            </div></div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<section class="section section-dark" style="padding:60px 0">
  <div class="container" style="text-align:center">
    <div data-anim="up">
      <div style="width:64px;height:64px;border-radius:50%;background:rgba(212,146,10,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:26px;color:var(--primary)"><i class="fas fa-headset"></i></div>
      <h2 class="section-title">Still Need <em>Help?</em></h2>
      <p class="section-desc" style="max-width:480px;margin:0 auto 28px">If your question is not covered here, send it through our contact page and we will review it.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="contact.php" class="btn btn-gold"><i class="fas fa-envelope"></i> Go to Contact Page</a>
      </div>
    </div>
  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>

<button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top">
  <i class="fas fa-chevron-up"></i>
</button>

<script src="assets/js/main.js"></script>
<script>
document.querySelectorAll('.faq-q').forEach(btn => {
  btn.addEventListener('click', () => {
    const item = btn.closest('.faq-item');
    const wasOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
    if (!wasOpen) item.classList.add('open');
  });
});

document.querySelectorAll('.faq-cats-nav a').forEach(link => {
  link.addEventListener('click', function() {
    document.querySelectorAll('.faq-cats-nav a').forEach(a => a.classList.remove('active'));
    this.classList.add('active');
  });
});

const faqSearchInput = document.getElementById('faqSearch');
const noResults = document.getElementById('faqNoResults');

faqSearchInput.addEventListener('input', function() {
  const q = this.value.trim().toLowerCase();
  const groups = document.querySelectorAll('.faq-cat-group');
  let totalVisible = 0;

  groups.forEach(group => {
    const items = group.querySelectorAll('.faq-item');
    let groupVisible = 0;
    items.forEach(item => {
      const text = item.querySelector('.faq-q').textContent.toLowerCase()
                 + item.querySelector('.faq-a-inner').textContent.toLowerCase();
      const match = !q || text.includes(q);
      item.style.display = match ? '' : 'none';
      if (match) groupVisible++;
    });
    group.style.display = groupVisible ? '' : 'none';
    totalVisible += groupVisible;
  });

  noResults.style.display = totalVisible === 0 ? 'block' : 'none';
});
</script>
</body>
</html>
