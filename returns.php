<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Return Policy | Gadget Hub',
      'description' => 'Read Gadget Hub return policy for returns, exchanges, damaged items, and refund handling.',
      'canonical' => 'returns.php',
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
      <span class="section-tag">Our Policies</span>
      <h1>Return &amp; Refund <em>Policy</em></h1>
      <p>This policy explains when returns are accepted, how refunds are reviewed, and how customers should request assistance.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Return Policy</span>
    </nav>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="policy-layout">

      <aside class="policy-toc" data-anim="left">
        <h4>Contents</h4>
        <ul>
          <li><a href="#overview"><span class="toc-num">01</span> Overview</a></li>
          <li><a href="#eligibility"><span class="toc-num">02</span> Return Eligibility</a></li>
          <li><a href="#exceptions"><span class="toc-num">03</span> Non-Returnable Items</a></li>
          <li><a href="#process"><span class="toc-num">04</span> How to Request a Return</a></li>
          <li><a href="#refunds"><span class="toc-num">05</span> Refunds &amp; Exchanges</a></li>
          <li><a href="#damaged"><span class="toc-num">06</span> Damaged, Defective, or Incorrect Items</a></li>
          <li><a href="#shipping"><span class="toc-num">07</span> Return Shipping</a></li>
          <li><a href="#contact"><span class="toc-num">08</span> Contact</a></li>
        </ul>
      </aside>

      <div data-anim="right">
        <div class="policy-last-updated"><i class="fas fa-calendar-alt"></i> Last Updated: June 4, 2026</div>

        <div class="policy-highlight">
          <p><i class="fas fa-undo-alt" style="color:var(--primary);margin-right:8px"></i> Return requests should be submitted promptly after delivery and must be reviewed before any item is sent back.</p>
        </div>

        <div class="policy-section" id="overview">
          <h2><span class="ps-num">01</span> Overview</h2>
          <p>We want customers to receive the correct product in proper condition. If an item is damaged, defective, incorrect, or materially different from what was ordered, a return, replacement, exchange, repair assessment, or refund may be available depending on the circumstances.</p>
          <p>This policy should be read together with our <a href="terms.php">Terms &amp; Conditions</a> and <a href="shipping.php">Shipping Policy</a>.</p>
        </div>

        <div class="policy-section" id="eligibility">
          <h2><span class="ps-num">02</span> Return Eligibility</h2>
          <p>To be considered for a return, the request should generally:</p>
          <ul>
            <li>be submitted within <strong>7 calendar days</strong> of delivery unless a different warranty or product-specific condition applies;</li>
            <li>relate to a damaged, defective, incorrect, missing, or materially misdescribed item, or another approved return circumstance;</li>
            <li>include proof of purchase or enough order information for verification; and</li>
            <li>include the item with its original packaging, accessories, manuals, labels, and any sealed components where reasonably applicable.</li>
          </ul>
          <p>Items showing misuse, accidental damage, unauthorized repair attempts, or missing essential parts may not qualify for return or refund approval.</p>
        </div>

        <div class="policy-section" id="exceptions">
          <h2><span class="ps-num">03</span> Non-Returnable Items</h2>
          <p>Unless faulty, incorrect, or otherwise required by law, the following categories are generally not eligible for return:</p>
          <ul>
            <li>items that have been used, altered, physically damaged, or improperly installed after delivery;</li>
            <li>items returned without essential original packaging or required accessories;</li>
            <li>software, digital goods, license keys, downloads, or activated products;</li>
            <li>special-order, custom-built, or customized items prepared specifically for a customer; and</li>
            <li>consumable or hygiene-sensitive items where return is unsuitable once opened, unless defective.</li>
          </ul>
        </div>

        <div class="policy-section" id="process">
          <h2><span class="ps-num">04</span> How to Request a Return</h2>
          <ol>
            <li>Submit your request through our <a href="contact.php">Contact page</a>.</li>
            <li>Include your order reference, the item concerned, and a clear description of the issue.</li>
            <li>Where relevant, provide photographs or videos showing the problem, packaging condition, or incorrect item received.</li>
            <li>Wait for return instructions before sending the item back.</li>
          </ol>
          <p>Items sent back without prior review or instructions may delay processing or be refused.</p>
        </div>

        <div class="policy-section" id="refunds">
          <h2><span class="ps-num">05</span> Refunds &amp; Exchanges</h2>
          <p>Once a returned item is received and inspected, we will determine whether a refund, exchange, replacement, repair assessment, or rejection is appropriate.</p>
          <ul>
            <li><strong>Refunds:</strong> approved refunds are generally issued to the original payment method where possible.</li>
            <li><strong>Exchanges or replacements:</strong> may be offered where stock is available and the issue qualifies.</li>
            <li><strong>Processing time:</strong> approved refund processing usually begins within <strong>3 to 7 business days</strong> after inspection, although final settlement timing may depend on banks or payment providers.</li>
          <li><strong>Original shipping charges:</strong> may be excluded from refunds unless the return results from our error or a verified item issue.</li>
          </ul>
        </div>

        <div class="policy-section" id="damaged">
          <h2><span class="ps-num">06</span> Damaged, Defective, or Incorrect Items</h2>
          <p>If an item arrives damaged, defective, incomplete, or incorrect, please report it as soon as possible through the <a href="contact.php">Contact page</a>.</p>
          <p>Prompt reporting helps us review courier issues, product faults, and order discrepancies more effectively. Depending on the outcome of the review, we may offer a replacement, repair path, exchange, refund, or other suitable resolution.</p>
        </div>

        <div class="policy-section" id="shipping">
          <h2><span class="ps-num">07</span> Return Shipping</h2>
          <p>Return shipping responsibility depends on the reason for the return:</p>
          <ul>
            <li>if the return is due to our error, an incorrect item, or a verified product issue, return shipping support may be arranged or reimbursed where appropriate;</li>
            <li>if the return is not due to our error, return shipping costs may be the customer's responsibility; and</li>
            <li>customers should follow the specific instructions provided after return review to avoid delivery issues or processing delays.</li>
          </ul>
        </div>

        <div class="policy-section" id="contact">
          <h2><span class="ps-num">08</span> Contact</h2>
          <p>For returns, refunds, exchanges, or damaged-item assistance, please use our <a href="contact.php">Contact page</a>. Include your order reference and a short explanation so we can review the request efficiently.</p>
          <div class="policy-contact-box">
            <div class="pcb-ico"><i class="fas fa-box-open"></i></div>
            <div class="pcb-text">
              <strong>Returns Assistance</strong>
              <span>Submit all return and refund requests through the contact page.</span>
            </div>
            <div class="pcb-links">
              <a href="contact.php" class="btn btn-gold btn-sm"><i class="fas fa-paper-plane"></i> Go to Contact Page</a>
            </div>
          </div>
        </div>

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
const sections = document.querySelectorAll('.policy-section[id]');
const tocLinks = document.querySelectorAll('.policy-toc a');
window.addEventListener('scroll', () => {
  let cur = '';
  sections.forEach(s => { if (scrollY >= s.offsetTop - 130) cur = s.id; });
  tocLinks.forEach(a => {
    a.classList.toggle('toc-active', a.getAttribute('href') === '#' + cur);
  });
}, { passive: true });
</script>
</body>
</html>
