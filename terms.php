<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Terms & Conditions | Gadget Hub',
      'description' => 'Read the Gadget Hub terms and conditions for website access, orders, and ecommerce purchases.',
      'canonical' => 'terms.php',
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
      <h1>Terms &amp; <em>Conditions</em></h1>
      <p>These terms explain the conditions that apply when you use this website or purchase products through it.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Terms &amp; Conditions</span>
    </nav>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="policy-layout">

      <aside class="policy-toc" data-anim="left">
        <h4>Contents</h4>
        <ul>
          <li><a href="#acceptance"><span class="toc-num">01</span> Acceptance of Terms</a></li>
          <li><a href="#website-use"><span class="toc-num">02</span> Website Use</a></li>
          <li><a href="#products"><span class="toc-num">03</span> Products &amp; Pricing</a></li>
          <li><a href="#orders"><span class="toc-num">04</span> Orders</a></li>
          <li><a href="#payments"><span class="toc-num">05</span> Payments</a></li>
          <li><a href="#shipping"><span class="toc-num">06</span> Shipping, Delivery &amp; Risk</a></li>
          <li><a href="#returns"><span class="toc-num">07</span> Returns &amp; Refunds</a></li>
          <li><a href="#ip"><span class="toc-num">08</span> Intellectual Property</a></li>
          <li><a href="#liability"><span class="toc-num">09</span> Limitation of Liability</a></li>
          <li><a href="#general"><span class="toc-num">10</span> General Terms</a></li>
          <li><a href="#contact"><span class="toc-num">11</span> Contact</a></li>
        </ul>
      </aside>

      <div data-anim="right">
        <div class="policy-last-updated"><i class="fas fa-calendar-alt"></i> Last Updated: June 4, 2026</div>

        <div class="policy-highlight">
          <p><i class="fas fa-file-contract" style="color:var(--primary);margin-right:8px"></i> By using this website or placing an order through it, you agree to the terms below together with our related policies.</p>
        </div>

        <div class="policy-section" id="acceptance">
          <h2><span class="ps-num">01</span> Acceptance of Terms</h2>
          <p>These Terms and Conditions govern your access to and use of the Gadget Hub website and any purchases made through the website.</p>
          <p>If you do not agree to these terms, please do not use the website or place an order through it. We may revise these terms at any time by publishing an updated version on this page.</p>
        </div>

        <div class="policy-section" id="website-use">
          <h2><span class="ps-num">02</span> Website Use</h2>
          <p>You agree to use this website lawfully and responsibly. You must not:</p>
          <ul>
            <li>use the website for fraudulent, unlawful, or misleading activity;</li>
            <li>attempt to interfere with the website, related systems, or security controls;</li>
            <li>copy, scrape, reproduce, or redistribute website content without permission; or</li>
            <li>submit inaccurate, false, or unauthorized information when creating an account or placing an order.</li>
          </ul>
          <p>We may restrict or suspend access where misuse, abuse, or security concerns are detected.</p>
        </div>

        <div class="policy-section" id="products">
          <h2><span class="ps-num">03</span> Products &amp; Pricing</h2>
          <p>We aim to keep product descriptions, images, specifications, availability, and pricing accurate. However, errors, delays in updates, or supplier changes may occur.</p>
          <ul>
            <li>All products are subject to availability.</li>
            <li>Prices may change without prior notice.</li>
            <li>Product images are illustrative and may differ slightly from actual packaging or appearance.</li>
            <li>We reserve the right to correct any pricing, listing, or content errors before or after an order is placed.</li>
          </ul>
        </div>

        <div class="policy-section" id="orders">
          <h2><span class="ps-num">04</span> Orders</h2>
          <p>Placing an order through the website is treated as an offer to purchase. An order is not final until it has been accepted and successfully processed by us.</p>
          <p>We reserve the right to refuse, limit, or cancel any order where necessary, including in cases involving pricing errors, stock unavailability, payment concerns, suspected fraud, duplicate submissions, or other operational issues.</p>
        </div>

        <div class="policy-section" id="payments">
          <h2><span class="ps-num">05</span> Payments</h2>
          <p>Payments may be made through the payment methods offered on the website from time to time. Online card or digital payments may be processed by third-party payment gateway providers.</p>
          <ul>
            <li>You are responsible for providing complete and accurate billing and transaction information.</li>
            <li>Authorization failures, banking restrictions, or gateway issues may prevent a transaction from completing.</li>
            <li>We do not store full card credentials or sensitive payment authentication data on this website.</li>
            <li>Refund handling is subject to our <a href="returns.php">Return Policy</a> and the rules of the original payment method.</li>
          </ul>
        </div>

        <div class="policy-section" id="shipping">
          <h2><span class="ps-num">06</span> Shipping, Delivery &amp; Risk</h2>
          <p>Shipping and delivery are governed by our <a href="shipping.php">Shipping Policy</a>. Delivery timelines are estimates unless expressly stated otherwise.</p>
          <p>Risk in the goods passes to the customer upon delivery to the address provided for the order, subject to rights available under applicable law for items received damaged, defective, or incorrect.</p>
        </div>

        <div class="policy-section" id="returns">
          <h2><span class="ps-num">07</span> Returns &amp; Refunds</h2>
          <p>Returns, exchanges, and refunds are governed by our <a href="returns.php">Return Policy</a>. That policy forms part of these Terms and Conditions.</p>
          <p>Customers should review the Return Policy carefully before making a purchase.</p>
        </div>

        <div class="policy-section" id="ip">
          <h2><span class="ps-num">08</span> Intellectual Property</h2>
          <p>All website content, including text, graphics, branding, images, interface elements, code, and layout, belongs to Gadget Hub or its respective licensors unless otherwise stated.</p>
          <p>No content may be reproduced, republished, copied, modified, or commercially used without prior written permission, except where such use is permitted by law.</p>
        </div>

        <div class="policy-section" id="liability">
          <h2><span class="ps-num">09</span> Limitation of Liability</h2>
          <p>To the maximum extent permitted by law, we are not liable for indirect, incidental, special, or consequential losses arising from use of the website or purchase of products through it.</p>
          <p>Nothing in these terms excludes liability that cannot legally be excluded, including any non-excludable rights available to consumers under applicable law.</p>
        </div>

        <div class="policy-section" id="general">
          <h2><span class="ps-num">10</span> General Terms</h2>
          <ul>
            <li>These terms are governed by the laws of Sri Lanka.</li>
            <li>If any part of these terms is found unenforceable, the remaining provisions continue to apply.</li>
            <li>Our failure to enforce a provision immediately does not waive the right to enforce it later.</li>
            <li>These terms should be read together with our Privacy, Return, and Shipping policies.</li>
          </ul>
        </div>

        <div class="policy-section" id="contact">
          <h2><span class="ps-num">11</span> Contact</h2>
          <p>If you have questions about these Terms and Conditions, please use our <a href="contact.php">Contact page</a>. We review all policy-related queries submitted through that page.</p>
          <div class="policy-contact-box">
            <div class="pcb-ico"><i class="fas fa-file-contract"></i></div>
            <div class="pcb-text">
              <strong>Terms &amp; Order Queries</strong>
              <span>Use our contact page for clarification before placing an order.</span>
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
