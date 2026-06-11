<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Privacy Policy | Gadget Hub',
      'description' => 'Review how Gadget Hub collects, uses, shares, and protects personal information on the website.',
      'canonical' => 'privacy.php',
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
      <h1>Privacy <em>Policy</em></h1>
      <p>This policy explains how personal information is collected, used, shared, and protected when you use our website.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Privacy Policy</span>
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
          <li><a href="#collection"><span class="toc-num">02</span> Information We Collect</a></li>
          <li><a href="#use"><span class="toc-num">03</span> How We Use Information</a></li>
          <li><a href="#sharing"><span class="toc-num">04</span> Sharing Information</a></li>
          <li><a href="#payments"><span class="toc-num">05</span> Payment Processing</a></li>
          <li><a href="#cookies"><span class="toc-num">06</span> Cookies &amp; Local Storage</a></li>
          <li><a href="#security"><span class="toc-num">07</span> Data Security</a></li>
          <li><a href="#rights"><span class="toc-num">08</span> Your Choices</a></li>
          <li><a href="#changes"><span class="toc-num">09</span> Changes to This Policy</a></li>
          <li><a href="#contact"><span class="toc-num">10</span> Contact</a></li>
        </ul>
      </aside>

      <div data-anim="right">
        <div class="policy-last-updated"><i class="fas fa-calendar-alt"></i> Last Updated: June 4, 2026</div>

        <div class="policy-highlight">
          <p><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:8px"></i> We collect only the information needed to operate the website, process orders, support deliveries, prevent fraud, and comply with legal obligations.</p>
        </div>

        <div class="policy-section" id="overview">
          <h2><span class="ps-num">01</span> Overview</h2>
          <p>This Privacy Policy applies to information collected through Gadget Hub when you browse our website, create an account, place an order, submit an inquiry, or otherwise interact with our services.</p>
          <p>By using this website, you acknowledge that your information may be processed as described in this policy. If you do not agree, please do not use the website or submit personal information through it.</p>
        </div>

        <div class="policy-section" id="collection">
          <h2><span class="ps-num">02</span> Information We Collect</h2>
          <p>We may collect the following categories of information:</p>
          <ul>
            <li><strong>Identity details:</strong> such as your name when you create an account or place an order.</li>
            <li><strong>Contact details:</strong> such as your email address, telephone number, and delivery details.</li>
            <li><strong>Order information:</strong> products ordered, quantities, delivery selections, and transaction references.</li>
            <li><strong>Communications:</strong> information you provide when submitting inquiries, support requests, or return requests.</li>
            <li><strong>Technical data:</strong> IP address, browser type, device information, page visits, and basic usage activity.</li>
            <li><strong>Locally stored website data:</strong> cart contents, wishlist data, and session-related preferences stored in your browser.</li>
          </ul>
        </div>

        <div class="policy-section" id="use">
          <h2><span class="ps-num">03</span> How We Use Information</h2>
          <p>We use collected information to operate our ecommerce services and maintain a safe shopping experience. This includes:</p>
          <ul>
            <li>processing and fulfilling orders;</li>
            <li>coordinating shipping, delivery, replacement, return, and refund requests;</li>
            <li>responding to customer inquiries and service requests;</li>
            <li>sending order, account, or service-related notifications;</li>
            <li>detecting fraud, abuse, unauthorized activity, or technical issues;</li>
            <li>improving the website, products, and customer experience; and</li>
            <li>meeting legal, tax, accounting, and regulatory obligations.</li>
          </ul>
        </div>

        <div class="policy-section" id="sharing">
          <h2><span class="ps-num">04</span> Sharing Information</h2>
          <p>We do not sell personal information. We may share information only where reasonably necessary for business operations, such as:</p>
          <ul>
            <li><strong>delivery and logistics providers</strong> for dispatch and delivery of orders;</li>
            <li><strong>payment and transaction providers</strong> for payment authorization, settlement, fraud screening, and refund handling;</li>
            <li><strong>technology or service providers</strong> who support website infrastructure, security, analytics, or communications; and</li>
            <li><strong>legal or regulatory authorities</strong> where disclosure is required by law or necessary to protect legitimate rights.</li>
          </ul>
          <p>Any such sharing is limited to the information reasonably required for the relevant purpose.</p>
        </div>

        <div class="policy-section" id="payments">
          <h2><span class="ps-num">05</span> Payment Processing</h2>
          <p>Online payments may be processed through third-party payment service providers, including payment gateway partners used on this website. Those providers may collect payment-related information directly from you in order to authorize and settle transactions.</p>
          <p>We do not store full card numbers, card security codes, or complete sensitive payment credentials on this website. Payment processing is subject to the privacy and security practices of the relevant payment provider.</p>
        </div>

        <div class="policy-section" id="cookies">
          <h2><span class="ps-num">06</span> Cookies &amp; Local Storage</h2>
          <p>We may use cookies, local storage, and similar browser technologies to keep the website functional and improve usability. These technologies may be used to remember cart contents, wishlist items, interface preferences, and session-related information.</p>
          <p>You may control cookies through your browser settings. Disabling certain storage features may affect how parts of the website function.</p>
        </div>

        <div class="policy-section" id="security">
          <h2><span class="ps-num">07</span> Data Security</h2>
          <p>We use reasonable administrative, technical, and organizational safeguards to protect personal information against unauthorized access, loss, misuse, disclosure, or alteration.</p>
          <p>Despite these safeguards, no internet transmission or electronic storage method can be guaranteed as completely secure. You use the website and submit information at your own risk.</p>
        </div>

        <div class="policy-section" id="rights">
          <h2><span class="ps-num">08</span> Your Choices</h2>
          <p>You may request assistance regarding information associated with your orders or account, including correction of inaccurate details where appropriate.</p>
          <p>You may also stop using the website, clear browser-stored data, or contact us about privacy-related concerns through our <a href="contact.php">Contact page</a>.</p>
        </div>

        <div class="policy-section" id="changes">
          <h2><span class="ps-num">09</span> Changes to This Policy</h2>
          <p>We may update this Privacy Policy from time to time to reflect business, technical, legal, or operational changes. Any updates will be posted on this page with a revised last updated date.</p>
          <p>Your continued use of the website after an updated version is published constitutes acceptance of the revised policy.</p>
        </div>

        <div class="policy-section" id="contact">
          <h2><span class="ps-num">10</span> Contact</h2>
          <p>If you have any privacy-related questions, requests, or concerns, please use our <a href="contact.php">Contact page</a>. We review inquiries submitted through that page and respond through the appropriate support channel.</p>
          <div class="policy-contact-box">
            <div class="pcb-ico"><i class="fas fa-shield-alt"></i></div>
            <div class="pcb-text">
              <strong>Privacy Assistance</strong>
              <span>Use our contact page for policy or data-handling questions.</span>
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
