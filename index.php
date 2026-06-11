<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
$db = getDB();
ensureHeroSlidesTable();
ensurePromoBannersTable();
ensureBrandsTable();
ensureProductFreeDeliveryColumn();
ensureProductCategoriesTable();

// Fetch data 
$categories = $db->query('
    SELECT c.*, COUNT(DISTINCT p.id) AS product_count
    FROM categories c
    LEFT JOIN product_categories pc ON pc.category_id = c.id
    LEFT JOIN products p ON p.id = pc.product_id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC
')->fetchAll();

// Build lookup by slug and lowercase name for bento links
$catLookup = [];
foreach ($categories as $cat) {
    $catLookup[strtolower($cat['slug'])] = $cat;
    $catLookup[strtolower($cat['name'])] = $cat;
}

// Static bento layout matching original design
// [name, subtitle, icon, --ci color, --cc color, type: feat|wide|normal]
$bentoItems = [
    ['Mobile Displays & Accessories', 'All major brands',   'fas fa-mobile-alt',       'rgba(236,72,153,.15)', '#ec4899', 'feat'],
    ['Graphics Cards',                'NVIDIA & AMD',        'fas fa-desktop',          'rgba(118,185,0,.12)',  '#76b900', 'normal'],
    ['Storage',                       'SSD & HDD',           'fas fa-hdd',              'rgba(37,211,102,.10)', '#22c55e', 'normal'],
    ['RAM',                           'DDR4 & DDR5',         'fas fa-memory',           'rgba(168,85,247,.12)', '#a855f7', 'normal'],
    ['Monitors',                      'FHD & 4K',            'fas fa-tv',               'rgba(59,130,246,.12)', '#3b82f6', 'normal'],
    ['Processors',                    'Intel & AMD CPUs',    'fas fa-microchip',        'rgba(212,146,10,.12)', '#d4920a', 'wide'],
    ['Motherboards',                  'All Chipsets',        'fas fa-server',           'rgba(245,158,11,.12)', '#f59e0b', 'normal'],
    ['Keyboards',                     'Mech & Membrane',     'fas fa-keyboard',         'rgba(16,185,129,.12)', '#10b981', 'normal'],
    ['Mouse',                         'Gaming & Office',     'fas fa-mouse',            'rgba(239,68,68,.12)',  '#ef4444', 'normal'],
    ['Cables',                        'All Types',           'fas fa-plug',             'rgba(251,191,36,.12)', '#fbbf24', 'normal'],
    ['Earphones',                     'Wired & Wireless',    'fas fa-headphones',       'rgba(167,139,250,.12)','#a78bfa', 'wide'],
    ['Power Banks',                   '10kâ€“20k mAh',         'fas fa-battery-full',     'rgba(52,211,153,.12)', '#34d399', 'normal'],
    ['Chargers',                      'Fast Charging',       'fas fa-charging-station', 'rgba(251,146,60,.12)', '#fb923c', 'normal'],
];

$featuredSlider = $db->query('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
           (SELECT GROUP_CONCAT(DISTINCT c2.slug ORDER BY c2.name SEPARATOR ",")
            FROM product_categories pc2
            JOIN categories c2 ON c2.id = pc2.category_id
            WHERE pc2.product_id = p.id) AS category_slugs
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = 1 AND p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 10
')->fetchAll();

$specialOffers = $db->query('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
           (SELECT GROUP_CONCAT(DISTINCT c2.slug ORDER BY c2.name SEPARATOR ",")
            FROM product_categories pc2
            JOIN categories c2 ON c2.id = pc2.category_id
            WHERE pc2.product_id = p.id) AS category_slugs
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
      AND p.old_price IS NOT NULL
      AND p.old_price > p.price
    ORDER BY p.created_at DESC
    LIMIT 10
')->fetchAll();

$newArrivals = $db->query('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
           (SELECT GROUP_CONCAT(DISTINCT c2.slug ORDER BY c2.name SEPARATOR ",")
            FROM product_categories pc2
            JOIN categories c2 ON c2.id = pc2.category_id
            WHERE pc2.product_id = p.id) AS category_slugs
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 5
')->fetchAll();

$productCount = $db->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$waNumber     = getSetting('store_whatsapp', '94777237962');
$isKokoCardPromoEnabled = getSetting('pm_koko_enabled', '0') === '1';
$kokoLogoSrc = BASE_URL . 'images/payments/koko-logo.png';
$heroSlidesRaw = $db->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$heroSlides = array_values(array_filter($heroSlidesRaw, function ($s) {
    return !empty($s['desktop_image']) && !empty($s['mobile_image']);
}));
$promoBannersRaw = $db->query("SELECT * FROM promo_banners WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$promoBanners = array_values(array_filter($promoBannersRaw, function ($banner) {
    return !empty($banner['image_path']) && file_exists(__DIR__ . '/' . $banner['image_path']);
}));

// Trusted brands for Home marquee (managed from Admin -> Brands)
$homeBrands = $db->query("SELECT name, slug, logo_path FROM brands WHERE is_active = 1 ORDER BY sort_order ASC, name ASC")->fetchAll();

if (!$homeBrands) {
    // Fallback so section never appears empty
    $fallback = $db->query("
        SELECT brand
        FROM products
        WHERE brand IS NOT NULL AND brand != '' AND is_active = 1
        GROUP BY LOWER(brand), brand
        ORDER BY brand ASC
    ")->fetchAll();
    foreach ($fallback as $r) {
        $name = trim((string)$r['brand']);
        if ($name === '') continue;
        $slug = slugify($name);
        $legacyPath = 'images/brands/' . $slug . '.png';
        $homeBrands[] = [
            'name' => $name,
            'slug' => $slug,
            'logo_path' => file_exists(__DIR__ . '/' . $legacyPath) ? $legacyPath : null,
        ];
    }
}

// Category colour palette (cycles if more than 13 categories)
$palette = [
    ['rgba(236,72,153,.12)','#ec4899'],
    ['rgba(118,185,0,.12)', '#76b900'],
    ['rgba(37,211,102,.10)','#22c55e'],
    ['rgba(168,85,247,.12)','#a855f7'],
    ['rgba(59,130,246,.12)','#3b82f6'],
    ['rgba(212,146,10,.12)','#d4920a'],
    ['rgba(245,158,11,.12)','#f59e0b'],
    ['rgba(16,185,129,.12)','#10b981'],
    ['rgba(239,68,68,.12)', '#ef4444'],
    ['rgba(251,191,36,.12)','#fbbf24'],
    ['rgba(167,139,250,.12)','#a78bfa'],
    ['rgba(52,211,153,.12)','#34d399'],
    ['rgba(251,146,60,.12)','#fb923c'],
];

foreach ($categories as $i => $cat) {
    [$ci, $cc] = $palette[$i % count($palette)];
    $type = 'normal';
    if ($i === 0) $type = 'feat';
    elseif ($i > 0 && $i % 5 === 0) $type = 'wide';

    $homeCategoryCards[] = [
        'name'  => $cat['name'],
        'sub'   => ((int)$cat['product_count'] > 0 ? (int)$cat['product_count'] . ' item' . ((int)$cat['product_count'] !== 1 ? 's' : '') : 'Browse category'),
        'icon'  => $cat['icon'] ?: 'fas fa-box',
        'ci'    => $ci,
        'cc'    => $cc,
        'type'  => $type,
        'slug'  => $cat['slug'],
    ];
}

// Unique category slugs from featured products (for tab filter)
$featCats = [];
foreach ($specialOffers as $p) {
    $slugs = array_filter(array_map('trim', explode(',', (string)($p['category_slugs'] ?? $p['cat_slug'] ?? ''))));
    foreach ($slugs as $slug) {
        if (!isset($featCats[$slug]) && isset($catLookup[strtolower($slug)])) {
            $featCats[$slug] = $catLookup[strtolower($slug)]['name'];
        }
    }
}

// Helper: render one product card
function productCard(array $p, string $badgeOverride = '', bool $showKokoPromo = false): string {
    global $isKokoCardPromoEnabled, $kokoLogoSrc;

    $badge    = $badgeOverride ?: ($p['badge'] ?? '');
    $freeDelivery = !empty($p['free_delivery']);
    $inStock  = ($p['in_stock'] ?? 1) ? 'in-stock' : 'out-stock';
    $stockLbl = ($p['in_stock'] ?? 1) ? 'In Stock'  : 'Out of Stock';
    $slug     = htmlspecialchars($p['cat_slug'] ?? '');
    $categorySlugs = htmlspecialchars((string)($p['category_slugs'] ?? $p['cat_slug'] ?? ''));
    $name     = htmlspecialchars($p['name']);
    $price    = (float)$p['price'];
    $weightKg = (float)($p['weight_kg'] ?? 0);
    $oldPrice = $p['old_price'] ? (float)$p['old_price'] : null;
    $icon     = htmlspecialchars($p['icon'] ?? 'fas fa-box');
    $thumb    = $p['thumbnail'] ?? '';
    $hasThumb = $thumb && file_exists(__DIR__ . '/' . $thumb);
    $imgUrl   = $hasThumb ? htmlspecialchars(BASE_URL . $thumb) : '';
    $link     = 'product.php?id=' . (int)$p['id'];
    $freeDeliveryBadgeSrc = htmlspecialchars(BASE_URL . 'images/free-delivery.png');
    $kokoPromoHtml = '';
    if ($showKokoPromo && $isKokoCardPromoEnabled && $price > 0) {
        $kokoInstallment = $price / 3;
        $kokoPromoHtml = "<div class='feat-koko-paylater'>"
            . "<span>or 3 X Rs. " . number_format($kokoInstallment, 2) . " with</span>"
            . "<img src='" . htmlspecialchars($kokoLogoSrc) . "' alt='KOKO' loading='lazy'>"
            . "</div>";
    }

    $badgeHtml = '';
    if ($badge) {
        $cls = match($badge) { 'HOT'=>'badge-hot','NEW'=>'badge-new','SALE'=>'badge-sale', default=>'' };
        $badgeHtml = "<span class='p-badge $cls'>" . htmlspecialchars($badge) . "</span>";
    }
    $freeDeliveryHtml = $freeDelivery ? "<span class='free-delivery-badge'><img src='$freeDeliveryBadgeSrc' alt='Free Delivery' loading='lazy'></span>" : '';

    $imgHtml = $hasThumb
        ? "<img src='$imgUrl' alt='$name' loading='lazy' onerror=\"this.style.display='none';this.nextElementSibling.style.display='flex'\">"
          . "<div class='product-img-placeholder' style='display:none'><i class='$icon'></i></div>"
        : "<div class='product-img-placeholder'><i class='$icon'></i></div>";

    $oldHtml = $oldPrice ? "<span class='p-old'>Rs. " . number_format($oldPrice) . "</span>" : '';

    return "
    <div class='product-card' data-id='" . (int)$p['id'] . "' data-category='$slug' data-categories='$categorySlugs' data-category-label='" . htmlspecialchars($p['cat_name']) . "' data-name='$name' data-price='" . (int)$price . "' data-weight='" . htmlspecialchars((string)$weightKg) . "' data-free-delivery='" . ($freeDelivery ? '1' : '0') . "' onclick=\"window.location.href='$link'\" style='cursor:pointer'>
      <div class='product-img-area " . ($badge ? "has-top-badge" : "") . "'>
        <span class='stock-tag $inStock'>$stockLbl</span>
        $badgeHtml
        $freeDeliveryHtml
        $imgHtml
        <div class='p-hover-btns'>
          <button class='p-hover-btn' title='Add to Wishlist'><i class='far fa-heart'></i></button>
        </div>
      </div>
      <div class='product-body'>
        <span class='p-cat'>" . htmlspecialchars($p['cat_name']) . "</span>
        <div class='p-name'>$name</div>
        <div class='p-pricing'>
          <span class='p-price'>Rs. " . number_format($price) . "</span>
          $oldHtml
        </div>
        $kokoPromoHtml
        <div class='p-card-actions'>
          <button class='btn-cart' onclick='event.stopPropagation();'><i class='fas fa-shopping-cart'></i> Add to Cart</button>
        </div>
      </div>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => getSetting('seo_site_title', 'Gadget Hub | Computer Parts & Electronics'),
      'description' => getSetting('seo_meta_description', 'Premium computer parts, electronics and accessories. Wholesale and retail with fast island-wide service in Sri Lanka.'),
      'canonical' => '',
      'og_type' => 'website',
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

<!-- HERO -->
<section class="hero hero-full">
  <div class="hero-track" id="heroTrack">
    <?php if ($heroSlides): ?>
      <?php foreach ($heroSlides as $i => $slide): ?>
        <?php
          $desktop = $slide['desktop_image'];
          $mobile  = $slide['mobile_image'];
          $link    = trim((string)($slide['link_url'] ?? ''));
          $newTab  = (int)($slide['open_in_new_tab'] ?? 0) === 1;
        ?>
        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>">
          <a class="hero-slide-link" href="<?= htmlspecialchars($link ?: '#') ?>"<?= $newTab && $link ? ' target="_blank" rel="noopener"' : '' ?>>
            <picture>
              <source media="(max-width: 768px)" srcset="<?= htmlspecialchars($mobile) ?>">
              <img src="<?= htmlspecialchars($desktop) ?>" alt="Hero Slide <?= $i + 1 ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
            </picture>
          </a>
        </div>
      <?php endforeach ?>
    <?php else: ?>
      <div class="hero-slide active">
        <a class="hero-slide-link" href="#">
          <div class="hero-empty-slide"></div>
        </a>
      </div>
    <?php endif ?>
  </div>

  <button class="hero-nav hero-prev" id="heroPrev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
  <button class="hero-nav hero-next" id="heroNext" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>

  <div class="hero-dots">
    <?php
      $dotCount = max(1, count($heroSlides));
      for ($i = 0; $i < $dotCount; $i++):
    ?>
      <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
    <?php endfor ?>
  </div>
</section>

<!-- STATS BAR -->
<div class="stats-bar">
  <div class="stats-grid">
    <div class="stat-item" data-anim="up" data-delay="1">
      <div class="stat-ico"><i class="fas fa-box-open"></i></div>
      <div><div class="stat-num" data-count="<?= $productCount ?>"><?= $productCount ?>+</div><div class="stat-label">Products Available</div></div>
    </div>
    <div class="stat-item" data-anim="up" data-delay="2">
      <div class="stat-ico"><i class="fas fa-users"></i></div>
      <div><div class="stat-num" data-count="1000">0</div><div class="stat-label">Happy Customers</div></div>
    </div>
    <div class="stat-item" data-anim="up" data-delay="3">
      <div class="stat-ico"><i class="fas fa-calendar-alt"></i></div>
      <div><div class="stat-num" data-count="5">0</div><div class="stat-label">Years of Experience</div></div>
    </div>
    <div class="stat-item" data-anim="up" data-delay="4">
      <div class="stat-ico"><i class="fas fa-headset"></i></div>
      <div><div class="stat-num">24/7</div><div class="stat-label">Customer Support</div></div>
    </div>
  </div>
</div>

<!-- FEATURED PRODUCTS SLIDER -->
<section class="section featured-slider-section">
  <div class="container">
    <div class="section-head-row" data-anim="up">
      <div>
        <span class="section-tag">Top Picks</span>
        <h2 class="section-title" style="margin-bottom:0">Featured <em>Products</em></h2>
      </div>
      <div class="feat-slider-nav">
        <button class="feat-nav-btn" id="featPrev" type="button" aria-label="Previous featured products"><i class="fas fa-chevron-left"></i></button>
        <button class="feat-nav-btn" id="featNext" type="button" aria-label="Next featured products"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <?php if ($featuredSlider): ?>
    <div class="feat-slider-track" id="featuredTrack">
      <?php foreach ($featuredSlider as $p):
        $name = htmlspecialchars($p['name']);
        $price = (float)$p['price'];
        $oldPrice = $p['old_price'] ? (float)$p['old_price'] : null;
        $icon = htmlspecialchars($p['icon'] ?? 'fas fa-box');
        $thumb = $p['thumbnail'] ?? '';
        $hasThumb = $thumb && file_exists(__DIR__ . '/' . $thumb);
        $imgUrl = $hasThumb ? htmlspecialchars(BASE_URL . $thumb) : '';
        $link = 'product.php?id=' . (int)$p['id'];
        $freeDelivery = !empty($p['free_delivery']);
        $freeDeliveryBadgeSrc = BASE_URL . 'images/free-delivery.png';
        $kokoInstallment = $price > 0 ? $price / 3 : 0;
      ?>
      <div class="feat-slide-card product-card"
           data-id="<?= (int)$p['id'] ?>"
           data-name="<?= $name ?>"
           data-category="<?= htmlspecialchars($p['cat_slug'] ?? '') ?>"
           data-categories="<?= htmlspecialchars((string)($p['category_slugs'] ?? $p['cat_slug'] ?? '')) ?>"
           data-category-label="<?= htmlspecialchars($p['cat_name']) ?>"
           data-price="<?= (int)$price ?>"
           data-weight="<?= htmlspecialchars((string)((float)($p['weight_kg'] ?? 0))) ?>"
           data-free-delivery="<?= $freeDelivery ? '1' : '0' ?>"
           data-anim="up">
        <a href="<?= $link ?>" class="feat-slide-link">
          <div class="feat-slide-img">
          <?php if ($freeDelivery): ?>
            <span class="free-delivery-badge"><img src="<?= htmlspecialchars($freeDeliveryBadgeSrc) ?>" alt="Free Delivery" loading="lazy"></span>
          <?php endif ?>
          <?php if ($hasThumb): ?>
            <img src="<?= $imgUrl ?>" alt="<?= $name ?>" loading="lazy"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="feat-slide-placeholder" style="display:none"><i class="<?= $icon ?>"></i></div>
          <?php else: ?>
            <div class="feat-slide-placeholder"><i class="<?= $icon ?>"></i></div>
          <?php endif ?>
          </div>
          <div class="feat-slide-body">
            <span class="feat-slide-cat"><?= htmlspecialchars($p['cat_name']) ?></span>
            <div class="feat-slide-name"><?= $name ?></div>
            <div class="feat-slide-price">
              <?php if ($oldPrice): ?><span class="feat-old">Rs. <?= number_format($oldPrice) ?></span><?php endif ?>
              <span class="feat-new">Rs. <?= number_format($price) ?></span>
            </div>
            <?php if ($isKokoCardPromoEnabled && $kokoInstallment > 0): ?>
            <div class="feat-koko-paylater">
              <span>or 3 X Rs. <?= number_format($kokoInstallment, 2) ?> with</span>
              <img src="<?= htmlspecialchars($kokoLogoSrc) ?>" alt="KOKO" loading="lazy">
            </div>
            <?php endif ?>
          </div>
        </a>
        <div class="feat-slide-actions">
          <button class="btn-cart" type="button"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
        </div>
      </div>
      <?php endforeach ?>
    </div>
    <?php else: ?>
      <div style="text-align:center;padding:24px 18px;background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);color:var(--text-muted)">
        No featured products yet. Mark products as featured in the admin panel.
      </div>
    <?php endif ?>
  </div>
</section>

<!-- BENTO CATEGORY GRID -->
<section class="bento-section">
  <div class="container">
    <div class="section-header" data-anim="up">
      <span class="section-tag">Browse Our Range</span>
      <h2 class="section-title">Shop by <em>Category</em></h2>
      <p class="section-desc">Find exactly what you need from our wide range of computer parts, electronics and accessories.</p>
    </div>

    <div class="bento-grid">
      <?php foreach ($homeCategoryCards as $i => $item):
        $name   = $item['name'];
        $sub    = $item['sub'];
        $icon   = $item['icon'];
        $ci     = $item['ci'];
        $cc     = $item['cc'];
        $type   = $item['type'];
        $slug   = $item['slug'];
        $isFeat = $type === 'feat';
        $isWide = $type === 'wide';
        $cls    = $isFeat ? 'bcat bcat-feat' : ($isWide ? 'bcat bcat-wide' : 'bcat');
        $style  = $isFeat
          ? "background:linear-gradient(145deg,#1a0d14,#150910);border-color:rgba(236,72,153,.22)"
          : "--ci:$ci;--cc:$cc";
        $link   = 'shop.php?cat=' . urlencode($slug);
      ?>
      <a href="<?= $link ?>" class="<?= $cls ?>" style="<?= $style ?>" data-anim="up" data-delay="<?= ($i % 5) + 1 ?>">
        <div class="bcat-icon" <?= $isFeat ? "style='--ci:rgba(236,72,153,.15);--cc:#ec4899'" : '' ?>>
          <i class="<?= htmlspecialchars($icon) ?>"></i>
        </div>
        <div class="bcat-body">
          <span class="bcat-name"><?= htmlspecialchars($name) ?></span>
          <span class="bcat-sub"><?= htmlspecialchars($sub) ?></span>
          <?php if ($isFeat): ?>
            <span class="bcat-explore" style="color:#ec4899">Explore <i class="fas fa-arrow-right"></i></span>
          <?php endif ?>
        </div>
        <i class="<?= htmlspecialchars($icon) ?> bcat-bg-ico"<?= $isFeat ? ' style="color:rgba(236,72,153,.07)"' : '' ?>></i>
      </a>
      <?php endforeach ?>

      <!-- View All -->
      <a href="shop.php" class="bcat bcat-wide bcat-viewall" data-anim="up">
        <div class="bcat-icon"><i class="fas fa-th-large"></i></div>
        <div class="bcat-body">
          <span class="bcat-name">View All Categories</span>
          <span class="bcat-sub">Browse our complete range</span>
          <span class="bcat-explore">Go to Shop <i class="fas fa-arrow-right"></i></span>
        </div>
        <i class="fas fa-th-large bcat-bg-ico"></i>
      </a>
    </div>
  </div>
</section>

<!-- SPECIAL OFFERS -->
<section class="section section-dark section-bg-img" id="specialOffersSection">
  <div class="container">
    <div class="section-head-row" data-anim="up">
      <div>
        <span class="section-tag">Exclusive Products</span>
        <h2 class="section-title" style="margin-bottom:0">Special <em>Offers</em></h2>
      </div>
      <div class="tab-row">
        <button class="tab-btn active" data-filter="all">All</button>
        <?php foreach ($featCats as $slug => $name): ?>
          <button class="tab-btn" data-filter="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($name) ?></button>
        <?php endforeach ?>
      </div>
    </div>

    <div class="products-grid" id="productsGrid">
      <?php if ($specialOffers): ?>
        <?php foreach ($specialOffers as $p): echo productCard($p, '', true); endforeach ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">
          <i class="fas fa-star" style="font-size:32px;margin-bottom:12px;display:block;opacity:.4"></i>
          <p>No discounted products yet. Add old price and selling price in admin panel.</p>
        </div>
      <?php endif ?>
    </div>

    <div class="see-all-row" data-anim="up">
      <a href="shop.php" class="btn btn-ghost"><i class="fas fa-th-large"></i> View All Products</a>
    </div>
  </div>
</section>

<!-- OUR BRANDS -->
<section class="brands-section">
  <div class="container">
    <div class="section-header" data-anim="up">
      <span class="section-tag">Our Partners</span>
      <h2 class="section-title">Trusted <em>Brands</em></h2>
    </div>
  </div>
  <div class="brands-marquee-wrap">
    <div class="brands-marquee-track">
      <?php
      $renderBrands = [];
      foreach ($homeBrands as $b) {
          $path = !empty($b['logo_path']) ? $b['logo_path'] : ('images/brands/' . $b['slug'] . '.png');
          if (!file_exists(__DIR__ . '/' . $path)) continue;
          $renderBrands[] = ['name' => $b['name'], 'path' => $path];
      }
      foreach (array_merge($renderBrands, $renderBrands) as $b):
      ?>
        <div class="bm-item"><div class="bm-card"><img src="<?= htmlspecialchars($b['path']) ?>" alt="<?= htmlspecialchars($b['name']) ?>"></div></div>
      <?php endforeach ?>
    </div>
  </div>
</section>

<!-- PROMO BANNERS -->
<?php if ($promoBanners): ?>
<section class="section">
  <div class="container">
    <div class="promo-banners-grid" data-anim="up">
      <?php foreach ($promoBanners as $banner): ?>
      <a
        href="<?= htmlspecialchars($banner['link_url'] ?: '#') ?>"
        class="promo-card"
        <?= (int)($banner['open_in_new_tab'] ?? 0) === 1 ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
      >
        <img
          src="<?= htmlspecialchars($banner['image_path']) ?>"
          alt="Promo Banner"
          class="promo-card-img"
          loading="lazy"
        >
      </a>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<!-- NEW ARRIVALS -->
<section class="section section-dark section-bg-img" id="newArrivalsSection">
  <div class="container">
    <div class="section-head-row" data-anim="up">
      <div>
        <span class="section-tag">Our Products</span>
        <h2 class="section-title" style="margin-bottom:0">New <em>Arrivals</em></h2>
      </div>
      <a href="shop.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-right"></i> View All</a>
    </div>

    <div class="products-grid">
      <?php if ($newArrivals): ?>
        <?php foreach ($newArrivals as $i => $p): echo productCard($p, $p['badge'] ?: 'NEW', true); endforeach ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">
          <i class="fas fa-box-open" style="font-size:32px;margin-bottom:12px;display:block;opacity:.4"></i>
          <p>No products yet. Add products in the admin panel.</p>
        </div>
      <?php endif ?>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section">
  <div class="container">
    <div class="section-header" data-anim="up">
      <span class="section-tag">Our Promise</span>
      <h2 class="section-title">Why Choose <em>GADGET HUB</em></h2>
      <p class="section-desc">We're committed to delivering quality products with the best service in Sri Lanka.</p>
    </div>
    <div class="why-grid">
      <div class="why-card" data-anim="up" data-delay="1">
        <div class="why-ico"><i class="fas fa-tags"></i></div>
        <div class="why-title">Best Prices</div>
        <p class="why-desc">Competitive wholesale and retail pricing with regular deals. We guarantee the lowest prices in Sri Lanka.</p>
      </div>
      <div class="why-card" data-anim="up" data-delay="2">
        <div class="why-ico"><i class="fas fa-truck"></i></div>
        <div class="why-title">Fast Delivery</div>
        <p class="why-desc">Quick reliable island-wide delivery. Orders processed and dispatched within 24 hours.</p>
      </div>
      <div class="why-card" data-anim="up" data-delay="3">
        <div class="why-ico"><i class="fas fa-shield-alt"></i></div>
        <div class="why-title">Quality Assured</div>
        <p class="why-desc">All products are 100% genuine with manufacturer warranty. Zero compromises on quality.</p>
      </div>
      <div class="why-card" data-anim="up" data-delay="4">
        <div class="why-ico"><i class="fas fa-headset"></i></div>
        <div class="why-title">Expert Support</div>
        <p class="why-desc">Our experts are always available to help you choose the right product and provide after-sales support.</p>
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
(function () {
  const track = document.getElementById('featuredTrack');
  const prev  = document.getElementById('featPrev');
  const next  = document.getElementById('featNext');
  if (!track || !prev || !next) return;

  function stepSize() {
    const first = track.querySelector('.feat-slide-card');
    if (!first) return 320;
    return first.getBoundingClientRect().width + 14;
  }

  prev.addEventListener('click', function () {
    track.scrollBy({ left: -stepSize() * 2, behavior: 'smooth' });
  });
  next.addEventListener('click', function () {
    track.scrollBy({ left: stepSize() * 2, behavior: 'smooth' });
  });
})();
</script>
</body>
</html>
