<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Shipping Policy | Gadget Hub',
      'description' => 'Review Gadget Hub shipping policy, order processing timelines, delivery charges, and dispatch information.',
      'canonical' => 'shipping.php',
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
      <h1>Shipping &amp; Delivery <em>Policy</em></h1>
      <p>This policy explains how orders are processed, dispatched, delivered, and supported after shipment.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Shipping Policy</span>
    </nav>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="policy-layout">

      <aside class="policy-toc" data-anim="left">
        <h4>Contents</h4>
        <ul>
          <li><a href="#coverage"><span class="toc-num">01</span> Delivery Coverage</a></li>
          <li><a href="#processing"><span class="toc-num">02</span> Order Processing</a></li>
          <li><a href="#timing"><span class="toc-num">03</span> Estimated Delivery Timing</a></li>
          <li><a href="#charges"><span class="toc-num">04</span> Shipping Charges</a></li>
          <li><a href="#tracking"><span class="toc-num">05</span> Tracking &amp; Updates</a></li>
          <li><a href="#failed"><span class="toc-num">06</span> Failed or Delayed Deliveries</a></li>
          <li><a href="#inspection"><span class="toc-num">07</span> Receiving Your Order</a></li>
          <li><a href="#contact"><span class="toc-num">08</span> Contact</a></li>
        </ul>
      </aside>

      <div data-anim="right">
        <div class="policy-last-updated"><i class="fas fa-calendar-alt"></i> Last Updated: June 4, 2026</div>

        <div class="policy-highlight">
          <p><i class="fas fa-truck" style="color:var(--primary);margin-right:8px"></i> Delivery time estimates may vary by destination, courier capacity, stock readiness, payment confirmation, weekends, and public holidays.</p>
        </div>

        <div class="policy-section" id="coverage">
          <h2><span class="ps-num">01</span> Delivery Coverage</h2>
          <p>We deliver within Sri Lanka for serviceable addresses supported by our available delivery partners and fulfillment methods.</p>
          <p>Delivery availability may vary based on location, product type, parcel size, service disruptions, or safety limitations in the destination area.</p>
        </div>

        <div class="policy-section" id="processing">
          <h2><span class="ps-num">02</span> Order Processing</h2>
          <p>Orders are processed after order review and, where applicable, successful payment authorization or confirmation.</p>
          <ul>
            <li>processing times may vary depending on stock location, product type, order verification, or demand levels;</li>
            <li>orders placed outside business days or before holiday closures may require additional processing time; and</li>
            <li>in some cases we may contact the customer before dispatch to confirm order information or delivery details.</li>
          </ul>
        </div>

        <div class="policy-section" id="timing">
          <h2><span class="ps-num">03</span> Estimated Delivery Timing</h2>
          <p>Delivery estimates shown on the website, at checkout, or during order review are approximate only unless expressly stated otherwise.</p>
          <p>Actual delivery timing may be affected by destination, weather, transport conditions, courier network delays, public holidays, operational restrictions, or force majeure events beyond our control.</p>
        </div>

        <div class="policy-section" id="charges">
          <h2><span class="ps-num">04</span> Shipping Charges</h2>
          <p>Shipping charges may depend on delivery area, parcel size, weight, handling requirements, promotions, and the delivery method selected for the order.</p>
          <p>Where applicable, shipping charges are presented during checkout, communicated during order confirmation, or otherwise disclosed before dispatch.</p>
        </div>

        <div class="policy-section" id="tracking">
          <h2><span class="ps-num">05</span> Tracking &amp; Updates</h2>
          <p>When tracking is available, shipment details or status updates may be shared after dispatch. Some delivery methods may offer limited tracking visibility depending on the carrier or service type.</p>
          <p>If a customer needs help checking shipment progress, assistance may be requested through the <a href="contact.php">Contact page</a>.</p>
        </div>

        <div class="policy-section" id="failed">
          <h2><span class="ps-num">06</span> Failed or Delayed Deliveries</h2>
          <p>A delivery may fail or be delayed if the address is incomplete, the recipient is unavailable, courier access is restricted, contact details cannot be verified, or external disruptions affect transportation.</p>
          <p>If re-delivery, redirection, return-to-origin handling, or storage charges arise due to customer-side delivery issues, additional charges may apply where permitted.</p>
        </div>

        <div class="policy-section" id="inspection">
          <h2><span class="ps-num">07</span> Receiving Your Order</h2>
          <p>Please inspect the parcel as soon as reasonably possible after delivery. If the item appears damaged, incomplete, incorrect, or tampered with, report the issue promptly through the <a href="contact.php">Contact page</a>.</p>
          <p>Timely reporting helps us review courier issues and product discrepancies more effectively.</p>
        </div>

        <div class="policy-section" id="contact">
          <h2><span class="ps-num">08</span> Contact</h2>
          <p>For shipping, delivery, tracking, or delayed-order assistance, please use our <a href="contact.php">Contact page</a>. Include your order reference so we can review the request efficiently.</p>
          <div class="policy-contact-box">
            <div class="pcb-ico"><i class="fas fa-truck"></i></div>
            <div class="pcb-text">
              <strong>Delivery Assistance</strong>
              <span>Use our contact page for dispatch or delivery support.</span>
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
