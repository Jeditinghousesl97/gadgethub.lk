<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
$db = getDB();
ensureBrandsTable();

$phone      = getSetting('store_whatsapp', '94777237962');
$phoneClean = preg_replace('/\D/', '', $phone);

// Count active products per brand name
$dbBrands = $db->query("
    SELECT LOWER(brand) AS brand_key, brand, COUNT(*) AS product_count
    FROM products
    WHERE brand != '' AND brand IS NOT NULL AND is_active = 1
    GROUP BY LOWER(brand), brand
    ORDER BY brand ASC
")->fetchAll();
$inStock = [];
foreach ($dbBrands as $b) $inStock[$b['brand_key']] = (int)$b['product_count'];

// Managed brands (Admin -> Brands)
$managed = $db->query("SELECT * FROM brands WHERE is_active=1 ORDER BY sort_order ASC, name ASC")->fetchAll();
$brandCatalogue = [];

if ($managed) {
    foreach ($managed as $m) {
        $slug = strtolower($m['slug'] ?: slugify($m['name']));
        $tags = preg_split('/\s+/', trim((string)$m['filter_tags'])) ?: [];
        $tags = array_values(array_filter($tags, fn($t) => $t !== ''));

        $brandCatalogue[] = [
            'name'      => $m['name'],
            'logo'      => $slug,
            'logo_path' => $m['logo_path'] ?: null,
            'filter'    => trim((string)$m['filter_tags']) ?: 'other',
            'desc'      => $m['description'] ?: ('Genuine ' . $m['name'] . ' products available in store.'),
            'tags'      => $tags,
        ];
    }
} else {
    // Fallback: if no managed brands yet, derive from existing products
    foreach ($dbBrands as $b) {
        $brandCatalogue[] = [
            'name'      => $b['brand'],
            'logo'      => $b['brand_key'],
            'logo_path' => null,
            'filter'    => 'other',
            'desc'      => 'Genuine ' . $b['brand'] . ' products available in store.',
            'tags'      => [],
        ];
    }
}

// Build filter tabs dynamically from admin-added tags
$filterTabs = [];
foreach ($brandCatalogue as $b) {
    foreach (($b['tags'] ?? []) as $tag) {
        $tag = strtolower(trim((string)$tag));
        if ($tag === '' || $tag === 'other') continue;
        $filterTabs[$tag] = ucwords(str_replace(['-', '_'], ' ', $tag));
    }
}
ksort($filterTabs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Brands | Gadget Hub',
      'description' => 'Explore trusted computer and electronics brands available at GADGET HUB, including Intel, AMD, Samsung, Logitech and more.',
      'canonical' => 'brands.php',
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

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="hero-dot-bg"></div>
  <div class="page-hero-orb page-hero-orb-1"></div>
  <div class="page-hero-orb page-hero-orb-2"></div>
  <div class="container page-hero-inner">
    <div class="page-hero-text" data-anim="up">
      <span class="section-tag">Our Partners</span>
      <h1>Trusted <em>Brands</em></h1>
      <p>We carry only the world's most trusted brands - every product 100% genuine with full manufacturer warranty.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Brands</span>
    </nav>
  </div>
</section>

<!-- BRAND FILTER + GRID -->
<section class="section">
  <div class="container">

    <!-- Filter tabs -->
    <div class="brand-filter-tabs" data-anim="up">
      <button class="brand-tab active" data-brand-filter="all">All Brands</button>
      <?php foreach ($filterTabs as $tagKey => $tagLabel): ?>
      <button class="brand-tab" data-brand-filter="<?= htmlspecialchars($tagKey) ?>"><?= htmlspecialchars($tagLabel) ?></button>
      <?php endforeach ?>
    </div>

    <!-- Brand grid -->
    <div class="brands-page-grid" id="brandsGrid">
      <?php foreach ($brandCatalogue as $i => $b):
        $key      = strtolower($b['name']);
        $count    = $inStock[$key] ?? 0;
        $logoPath = $b['logo_path'] ?: ('images/brands/' . $b['logo'] . '.png');
        $hasLogo  = file_exists(__DIR__ . '/' . $logoPath);
        $shopLink = 'shop.php?brand=' . urlencode($key);
        $delay    = ($i % 4) + 1;
      ?>
      <div class="brand-pg-card" data-brand-cat="<?= htmlspecialchars(strtolower(trim((string)$b['filter']))) ?>" data-anim="up" data-delay="<?= $delay ?>">
        <div class="bpg-top">
          <div class="bpg-logo">
            <?php if ($hasLogo): ?>
              <img src="<?= htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($b['name']) ?>">
            <?php else: ?>
              <span style="font-size:18px;font-weight:800;color:var(--text)"><?= htmlspecialchars($b['name']) ?></span>
            <?php endif ?>
          </div>
          <?php if ($count > 0): ?>
            <span class="bpg-genuine"><i class="fas fa-shield-alt"></i> Genuine &middot; <?= $count ?> item<?= $count !== 1 ? 's' : '' ?></span>
          <?php else: ?>
            <span class="bpg-genuine"><i class="fas fa-shield-alt"></i> Genuine</span>
          <?php endif ?>
        </div>
        <p class="bpg-desc"><?= htmlspecialchars($b['desc']) ?></p>
        <?php if ($b['tags']): ?>
        <div class="bpg-tags">
          <?php foreach ($b['tags'] as $tag): ?>
            <span><?= htmlspecialchars($tag) ?></span>
          <?php endforeach ?>
        </div>
        <?php endif ?>
        <a href="<?= $shopLink ?>" class="bpg-btn"><i class="fas fa-shopping-bag"></i> Shop <?= htmlspecialchars($b['name']) ?></a>
      </div>
      <?php endforeach ?>
    </div>

  </div>
</section>

<!-- MARQUEE -->
<div class="brands-section">
  <div class="container" style="margin-bottom:28px">
    <div class="section-header" data-anim="up" style="margin-bottom:0">
      <span class="section-tag">All Partners</span>
      <h2 class="section-title" style="margin-bottom:0">Brands We <em>Carry</em></h2>
    </div>
  </div>
  <div class="brands-marquee-wrap">
    <div class="brands-marquee-track">
      <?php
      $marquee = [];
      foreach ($brandCatalogue as $bc) {
          $marquee[] = [
              'name' => $bc['name'],
              'path' => $bc['logo_path'] ?: ('images/brands/' . $bc['logo'] . '.png')
          ];
      }
      // Render twice for seamless loop
      for ($pass = 0; $pass < 2; $pass++):
        foreach ($marquee as $m):
          $logoFile = $m['path'];
          $altText  = htmlspecialchars($m['name']);
          if (!file_exists(__DIR__ . '/' . $logoFile)) continue;
      ?>
      <div class="bm-item"><div class="bm-card"><img src="<?= $logoFile ?>" alt="<?= $altText ?>"></div></div>
      <?php endforeach; endfor; ?>
    </div>
  </div>
</div>

<!-- CTA -->
<section class="section section-dark">
  <div class="container">
    <div class="brands-cta" data-anim="up">
      <div class="brands-cta-text">
        <span class="section-tag">Wholesale Inquiries</span>
        <h2 class="section-title" style="margin-bottom:8px">Looking for <em>Bulk Orders?</em></h2>
        <p style="color:var(--text-muted);font-size:14px;max-width:480px">We offer special wholesale pricing for all the brands above. Contact us for bulk order quotes and dealer pricing.</p>
      </div>
      <div class="brands-cta-btns">
        <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>" target="_blank" rel="noopener" class="btn btn-green"><i class="fab fa-whatsapp"></i> WhatsApp for Quote</a>
        <a href="contact.php" class="btn btn-ghost"><i class="fas fa-envelope"></i> Send Inquiry</a>
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
const tabs  = document.querySelectorAll('.brand-tab');
const cards = document.querySelectorAll('.brand-pg-card');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const f = tab.dataset.brandFilter;
    cards.forEach(c => {
      const cats = (c.dataset.brandCat || '').split(/\s+/).filter(Boolean);
      const show = f === 'all' || cats.includes(f);
      c.style.display = show ? '' : 'none';
      if (show) {
        c.style.opacity = '0';
        c.style.transform = 'translateY(16px)';
        requestAnimationFrame(() => {
          c.style.transition = 'opacity .3s ease, transform .3s ease';
          c.style.opacity = '1';
          c.style.transform = 'none';
        });
      }
    });
  });
});
</script>
</body>
</html>
