<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
$db = getDB();
ensureProductFreeDeliveryColumn();
ensureProductCategoriesTable();
$isKokoCardPromoEnabled = getSetting('pm_koko_enabled', '0') === '1';
$kokoLogoSrc = BASE_URL . 'images/payments/koko-logo.png';

// Load product 
$id = (int)get('id');
if (!$id) { header('Location: shop.php'); exit; }

$stmt = $db->prepare('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
           (SELECT GROUP_CONCAT(DISTINCT c2.slug ORDER BY c2.name SEPARATOR ",")
            FROM product_categories pc2
            JOIN categories c2 ON c2.id = pc2.category_id
            WHERE pc2.product_id = p.id) AS category_slugs
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.is_active = 1
');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) { header('Location: shop.php'); exit; }

// Specs
$specs = $db->prepare('SELECT * FROM product_specs WHERE product_id = ? ORDER BY sort_order ASC');
$specs->execute([$id]);
$specs = $specs->fetchAll();

// Additional images
$images = $db->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC LIMIT 5');
$images->execute([$id]);
$images = $images->fetchAll();

// Related products (share at least one category, not this one)
$related = $db->prepare('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
           (SELECT GROUP_CONCAT(DISTINCT c2.slug ORDER BY c2.name SEPARATOR ",")
            FROM product_categories pc2
            JOIN categories c2 ON c2.id = pc2.category_id
            WHERE pc2.product_id = p.id) AS category_slugs
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id != ? AND p.is_active = 1
      AND EXISTS (
        SELECT 1
        FROM product_categories pc_rel
        JOIN product_categories pc_cur ON pc_cur.category_id = pc_rel.category_id
        WHERE pc_cur.product_id = ? AND pc_rel.product_id = p.id
      )
    ORDER BY p.is_featured DESC, p.created_at DESC
    LIMIT 4
');
$related->execute([$id, $id]);
$related = $related->fetchAll();

// Derived values 
$price     = (float)$p['price'];
$oldPrice  = $p['old_price'] ? (float)$p['old_price'] : null;
$kokoInstallment = $price > 0 ? $price / 3 : 0;
$discount  = $oldPrice ? round((1 - $price / $oldPrice) * 100) : 0;
$saving    = $oldPrice ? ($oldPrice - $price) : 0;
$inStock   = (bool)($p['in_stock'] ?? true);
$name      = htmlspecialchars($p['name']);
$catName   = htmlspecialchars($p['cat_name']);
$catSlug   = htmlspecialchars($p['cat_slug']);
$brand     = htmlspecialchars($p['brand'] ?? '');
$sku       = htmlspecialchars($p['sku'] ?? '');
$icon      = htmlspecialchars($p['icon'] ?? 'fas fa-box');
$rating    = (float)($p['rating'] ?? 0);
$badge     = $p['badge'] ?? '';
$waNumber  = getSetting('store_whatsapp', '94777237962');
$waText    = urlencode("Hi GADGET HUB, I want to order: $p[name]. Please confirm availability and price. Thank you!");
$waLink    = "https://wa.me/{$waNumber}?text={$waText}";

// Thumbnail
$thumb    = $p['thumbnail'] ?? '';
$hasThumb = $thumb && file_exists(__DIR__ . '/' . $thumb);
$thumbUrl = $hasThumb ? BASE_URL . htmlspecialchars($thumb) : '';
$productJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $p['name'],
    'description' => seoTrimText((string)($p['short_description'] ?: $p['name']), 300),
    'category' => $p['cat_name'],
    'sku' => (string)($p['sku'] ?? ''),
    'brand' => [
        '@type' => 'Brand',
        'name' => (string)($p['brand'] ?: 'Gadget Hub'),
    ],
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => getSetting('currency_code', 'LKR'),
        'price' => number_format($price, 2, '.', ''),
        'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => BASE_URL . 'product.php?id=' . $id,
    ],
];
if ($thumbUrl !== '') {
    $productJsonLd['image'] = [$thumbUrl];
}

// Star rating HTML
function starRating(float $r): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $r >= $i ? '&#9733;' : ($r >= $i - 0.5 ? '&#9734;' : '&#9734;');
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => $p['name'] . ' | Gadget Hub',
      'description' => ($p['short_description'] ?: $p['name']) . ' - Buy genuine ' . $p['cat_name'] . ' at the best price in Sri Lanka from GADGET HUB.',
      'canonical' => 'product.php?id=' . $id,
      'og_type' => 'product',
      'image' => $thumbUrl,
      'json_ld' => [$productJsonLd],
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

<!-- BREADCRUMB -->
<div style="background:var(--bg-2);border-bottom:1px solid var(--border);padding:12px 0">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <a href="shop.php">Shop</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <a href="shop.php?cat=<?= $catSlug ?>"><?= $catName ?></a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span><?= $name ?></span>
    </nav>
  </div>
</div>

<!-- PRODUCT DETAIL -->
<section class="section">
  <div class="container">
    <div class="pd-layout">

      <!-- GALLERY -->
      <div class="pd-gallery">
        <div class="pd-main-img" id="pdMainImg">
          <?php if ($hasThumb): ?>
            <img id="pdMainImgEl" src="<?= $thumbUrl ?>" alt="<?= $name ?>"
                 style="width:100%;height:100%;object-fit:contain"
                 onerror="this.style.display='none';document.getElementById('pdMainPlaceholder').style.display='flex'">
            <div class="pd-main-placeholder" id="pdMainPlaceholder" style="display:none"><i class="<?= $icon ?>"></i></div>
          <?php else: ?>
            <div class="pd-main-placeholder"><i class="<?= $icon ?>"></i></div>
          <?php endif ?>
          <?php if (!empty($images)): ?>
          <button class="pd-gallery-nav prev" id="pdGalleryPrev" type="button" aria-label="Previous image"><i class="fas fa-chevron-left"></i></button>
          <button class="pd-gallery-nav next" id="pdGalleryNext" type="button" aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
          <?php endif ?>
          <span class="pd-zoom-hint"><i class="fas fa-search-plus"></i> Product Image</span>
        </div>

        <?php if (!empty($images)): ?>
        <div class="pd-thumbs">
          <!-- Primary thumbnail -->
          <div class="pd-thumb active" data-img="<?= $thumbUrl ?>">
            <?php if ($hasThumb): ?>
              <img src="<?= $thumbUrl ?>" alt="<?= $name ?>" style="width:100%;height:100%;object-fit:cover;border-radius:4px">
            <?php else: ?>
              <div class="pd-thumb-placeholder"><i class="<?= $icon ?>"></i></div>
            <?php endif ?>
          </div>
          <!-- Additional images -->
          <?php foreach ($images as $img):
            $iUrl = BASE_URL . htmlspecialchars($img['image_path']);
          ?>
          <div class="pd-thumb" data-img="<?= $iUrl ?>">
            <img src="<?= $iUrl ?>" alt="<?= $name ?>" style="width:100%;height:100%;object-fit:cover;border-radius:4px"
                 onerror="this.parentElement.style.display='none'">
          </div>
          <?php endforeach ?>
          <!-- Filler thumbnails if fewer than 3 total -->
          <?php for ($fi = count($images); $fi < 2; $fi++): ?>
          <div class="pd-thumb" data-img="">
            <div class="pd-thumb-placeholder"><i class="<?= $icon ?>"></i></div>
          </div>
          <?php endfor ?>
        </div>
        <?php endif ?>
      </div>

      <!-- INFO PANEL -->
      <div class="pd-info">

        <div class="pd-top-badges">
          <?php if ($discount >= 5): ?>
            <span class="pd-badge sale">-<?= $discount ?>% OFF</span>
          <?php endif ?>
          <?php if ($badge === 'HOT'): ?>
            <span class="pd-badge hot"><i class="fas fa-fire"></i> Hot</span>
          <?php elseif ($badge === 'NEW'): ?>
            <span class="pd-badge new"><i class="fas fa-bolt"></i> New</span>
          <?php endif ?>
          <?php if (!empty($p['free_delivery'])): ?>
            <span class="pd-badge" style="background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff"><i class="fas fa-truck"></i> Free Delivery</span>
          <?php endif ?>
          <span class="pd-badge genuine"><i class="fas fa-shield-alt"></i> Genuine</span>
        </div>

        <h1 class="pd-name"><?= $name ?></h1>

        <div class="pd-meta">
          <?php if ($brand): ?>
          <div class="pd-brand">
            <div class="pd-brand-logo" id="pdBrandLogoWrap">
              <img src="images/brands/<?= strtolower(str_replace([' ','_'], '-', $p['brand'])) ?>.png"
                   alt="<?= $brand ?>"
                   onerror="document.getElementById('pdBrandLogoWrap').style.display='none'">
            </div>
            <div class="pd-brand-text">
              <span>Brand</span>
              <strong><?= $brand ?></strong>
            </div>
          </div>
          <?php endif ?>
          <?php if ($rating > 0): ?>
          <div class="pd-rating">
            <span class="pd-stars"><?= starRating($rating) ?></span>
            <span style="font-size:13.5px;font-weight:700;color:var(--text-2)"><?= number_format($rating, 1) ?></span>
          </div>
          <?php endif ?>
          <?php if ($sku): ?>
            <span style="font-size:12px;color:var(--text-dim)">SKU: <?= $sku ?></span>
          <?php endif ?>
        </div>

        <div class="pd-divider"></div>

        <div class="pd-price-block">
          <span class="pd-price">Rs. <?= number_format($price) ?></span>
          <?php if ($oldPrice): ?>
            <span class="pd-old-price">Rs. <?= number_format($oldPrice) ?></span>
            <span class="pd-discount">Save Rs. <?= number_format($saving) ?></span>
          <?php endif ?>
        </div>
        <?php if ($isKokoCardPromoEnabled && $kokoInstallment > 0): ?>
        <div class="pd-koko-paylater">
          <span>or 3 X Rs. <?= number_format($kokoInstallment, 2) ?> with</span>
          <img src="<?= htmlspecialchars($kokoLogoSrc) ?>" alt="KOKO" loading="lazy">
        </div>
        <?php endif ?>
        <p class="pd-tax"><i class="fas fa-info-circle"></i> Price inclusive of all taxes. Wholesale pricing available.</p>

        <span class="pd-availability <?= $inStock ? 'in-stock' : 'out-stock' ?>">
          <i class="fas fa-circle"></i> <?= $inStock ? 'In Stock - Ready to Ship' : 'Out of Stock' ?>
        </span>

        <?php if ($p['short_description']): ?>
        <p class="pd-short-desc"><?= htmlspecialchars($p['short_description']) ?></p>
        <?php endif ?>

        <!-- Key specs (first 6) -->
        <?php if ($specs): ?>
        <div class="pd-key-specs">
          <?php foreach (array_slice($specs, 0, 6) as $s): ?>
          <div class="pd-spec-row">
            <i class="fas fa-check-circle"></i>
            <span><strong><?= htmlspecialchars($s['spec_key']) ?>:</strong> <?= htmlspecialchars($s['spec_value']) ?></span>
          </div>
          <?php endforeach ?>
        </div>
        <?php endif ?>

        <div class="pd-divider"></div>

        <div class="pd-qty-row">
          <span class="pd-qty-label">Quantity:</span>
          <div class="pd-qty-selector">
            <button class="pd-qty-btn" id="pdQtyMinus" type="button"><i class="fas fa-minus"></i></button>
            <input class="pd-qty-val" id="pdQtyVal" type="text" value="1" readonly>
            <button class="pd-qty-btn" id="pdQtyPlus" type="button"><i class="fas fa-plus"></i></button>
          </div>
          <span style="font-size:12px;color:var(--text-dim)">Max 10 per order</span>
        </div>

        <div class="pd-actions">
          <div class="pd-actions-row">
            <button class="pd-btn-cart <?= $inStock ? '' : 'disabled' ?>" id="pdAddCart" type="button" <?= $inStock ? '' : 'disabled' ?>>
              <i class="fas fa-shopping-cart"></i> <?= $inStock ? 'Add to Cart' : 'Out of Stock' ?>
            </button>
            <button class="pd-btn-wish" id="pdWish" title="Add to Wishlist" type="button">
              <i class="far fa-heart"></i>
            </button>
          </div>
          <a href="<?= $waLink ?>" target="_blank" rel="noopener" class="pd-btn-wa">
            <i class="fab fa-whatsapp"></i> Order via WhatsApp
          </a>
        </div>

        <div class="pd-trust">
          <div class="pd-trust-item"><i class="fas fa-shield-alt"></i><span>100% Genuine</span></div>
          <div class="pd-trust-item"><i class="fas fa-undo-alt"></i><span>7-Day Returns</span></div>
          <div class="pd-trust-item"><i class="fas fa-truck"></i><span>Island-wide Delivery</span></div>
        </div>

      </div>
    </div>

    <!-- TABS -->
    <div class="pd-tabs" data-anim="up">
      <div class="pd-tabs-nav">
        <button class="pd-tab-btn active" data-tab="description">Description</button>
        <?php if ($specs): ?>
          <button class="pd-tab-btn" data-tab="specifications">Specifications</button>
        <?php endif ?>
        <button class="pd-tab-btn" data-tab="warranty">Warranty &amp; Returns</button>
      </div>

      <!-- Description -->
      <div class="pd-tab-panel active" id="tab-description">
        <div class="pd-desc-body">
          <?php if ($p['description']): ?>
            <?= nl2br(htmlspecialchars($p['description'])) ?>
          <?php else: ?>
            <p style="color:var(--text-muted)">No description available for this product.</p>
          <?php endif ?>
        </div>
      </div>

      <!-- Specifications -->
      <?php if ($specs): ?>
      <div class="pd-tab-panel" id="tab-specifications">
        <table class="pd-specs-table">
          <?php if ($brand): ?>
            <tr><td>Brand</td><td><?= $brand ?></td></tr>
          <?php endif ?>
          <?php if ($sku): ?>
            <tr><td>SKU</td><td><?= $sku ?></td></tr>
          <?php endif ?>
          <?php foreach ($specs as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['spec_key']) ?></td>
              <td><?= htmlspecialchars($s['spec_value']) ?></td>
            </tr>
          <?php endforeach ?>
        </table>
      </div>
      <?php endif ?>

      <!-- Warranty -->
      <div class="pd-tab-panel" id="tab-warranty">
        <div class="pd-warranty-box">
          <h4><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:8px"></i>Warranty &amp; Return Policy</h4>
          <div class="pd-warranty-item">
            <i class="fas fa-check-circle"></i>
            <span><strong>Manufacturer Warranty</strong> - All products come with official manufacturer warranty against defects.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fas fa-check-circle"></i>
            <span><strong>7-Day Return Window</strong> - If you receive a defective or incorrect item, contact us within 7 days for a replacement or refund.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fas fa-check-circle"></i>
            <span><strong>Original Packaging Required</strong> - Items must be returned in original packaging unless defective on first use.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fas fa-info-circle"></i>
            <span><strong>Not covered:</strong> Physical damage, improper installation, overclocking damage, or user-caused issues.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fab fa-whatsapp" style="color:#25d366"></i>
            <span>For warranty claims, contact us on <a href="<?= $waLink ?>" target="_blank" style="color:var(--primary)">WhatsApp</a>.</span>
          </div>
        </div>
      </div>
    </div>

    <!-- RELATED PRODUCTS -->
    <?php if ($related): ?>
    <div class="pd-related" data-anim="up">
      <div class="section-header" style="margin-bottom:28px">
        <span class="section-tag">You May Also Like</span>
        <h2 class="section-title">Related <em>Products</em></h2>
      </div>
      <div class="products-grid">
        <?php foreach ($related as $r):
          $rThumb    = $r['thumbnail'] ?? '';
          $rHasThumb = $rThumb && file_exists(__DIR__ . '/' . $rThumb);
          $rIcon     = htmlspecialchars($r['icon'] ?? 'fas fa-box');
          $rPrice    = (float)$r['price'];
          $rOld      = $r['old_price'] ? (float)$r['old_price'] : null;
          $rName     = htmlspecialchars($r['name']);
          $rSlug     = htmlspecialchars($r['cat_slug']);
          $rBadge    = $r['badge'] ?? '';
          $rFreeDelivery = !empty($r['free_delivery']);
          $rFreeDeliveryBadgeSrc = BASE_URL . 'images/free-delivery.png';
          $rBadgeCls = match($rBadge) { 'HOT'=>'hot','NEW'=>'new','SALE'=>'sale', default=>'' };
        ?>
        <div class="product-card"
          onclick="if (event.target.closest('.p-hover-btn, .btn-cart')) return; window.location.href='product.php?id=<?= $r['id'] ?>'"
          style="cursor:pointer"
          data-id="<?= (int)$r['id'] ?>"
          data-category="<?= $rSlug ?>"
          data-categories="<?= htmlspecialchars((string)($r['category_slugs'] ?? $r['cat_slug'] ?? '')) ?>"
          data-category-label="<?= htmlspecialchars($r['cat_name']) ?>"
          data-name="<?= $rName ?>"
          data-price="<?= (int)$rPrice ?>"
          data-weight="<?= htmlspecialchars((string)((float)($r['weight_kg'] ?? 0))) ?>"
          data-free-delivery="<?= $rFreeDelivery ? '1' : '0' ?>">
          <div class="product-img-area <?= $rBadge ? 'has-top-badge' : '' ?>">
            <span class="stock-tag <?= $r['in_stock'] ? 'in-stock' : 'out-stock' ?>">
              <?= $r['in_stock'] ? 'In Stock' : 'Out of Stock' ?>
            </span>
            <?php if ($rBadge): ?>
              <span class="p-badge <?= $rBadgeCls ?>"><?= htmlspecialchars($rBadge) ?></span>
            <?php endif ?>
            <?php if ($rFreeDelivery): ?>
              <span class="free-delivery-badge"><img src="<?= htmlspecialchars($rFreeDeliveryBadgeSrc) ?>" alt="Free Delivery" loading="lazy"></span>
            <?php endif ?>
            <?php if ($rHasThumb): ?>
              <img src="<?= BASE_URL . htmlspecialchars($rThumb) ?>" alt="<?= $rName ?>" loading="lazy"
                   onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <div class="product-img-placeholder" style="display:none"><i class="<?= $rIcon ?>"></i></div>
            <?php else: ?>
              <div class="product-img-placeholder"><i class="<?= $rIcon ?>"></i></div>
            <?php endif ?>
            <div class="p-hover-btns">
              <button class="p-hover-btn" title="Add to Wishlist" type="button"><i class="far fa-heart"></i></button>
            </div>
          </div>
          <div class="product-body">
            <div class="p-cat"><?= htmlspecialchars($r['cat_name']) ?></div>
            <div class="p-name"><?= $rName ?></div>
            <div class="p-pricing">
              <span class="p-price">Rs. <?= number_format($rPrice) ?></span>
              <?php if ($rOld): ?>
                <span class="p-old">Rs. <?= number_format($rOld) ?></span>
              <?php endif ?>
            </div>
            <div class="p-card-actions">
              <button class="btn-cart" type="button"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
            </div>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>
    <?php endif ?>

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
  const productData = {
    productId: <?= (int)$p['id'] ?>,
    name:     <?= json_encode($p['name']) ?>,
    category: <?= json_encode($p['cat_slug']) ?>,
    price:    <?= (int)$price ?>,
    weight_kg: <?= json_encode((float)($p['weight_kg'] ?? 0)) ?>,
    free_delivery: <?= !empty($p['free_delivery']) ? 'true' : 'false' ?>,
    icon:     <?= json_encode($icon) ?>
  };

  /* Thumbnail switching */
  const mainImg   = document.getElementById('pdMainImg');
  const mainEl    = document.getElementById('pdMainImgEl');
  const mainPh    = document.getElementById('pdMainPlaceholder');
  const thumbs    = Array.from(document.querySelectorAll('.pd-thumb'));
  const prevBtn   = document.getElementById('pdGalleryPrev');
  const nextBtn   = document.getElementById('pdGalleryNext');
  const slideSrcs = thumbs.map(t => (t.dataset.img || '').trim()).filter(Boolean);
  let slideIndex  = Math.max(0, thumbs.findIndex(t => t.classList.contains('active')));
  if (slideIndex < 0) slideIndex = 0;

  function setSlide(i) {
    if (!slideSrcs.length || !mainEl) return;
    slideIndex = ((i % slideSrcs.length) + slideSrcs.length) % slideSrcs.length;
    const imgSrc = slideSrcs[slideIndex];
    mainEl.src = imgSrc;
    mainEl.style.display = '';
    if (mainPh) mainPh.style.display = 'none';
    thumbs.forEach((t, idx) => t.classList.toggle('active', idx === slideIndex));
  }

  thumbs.forEach((thumb, idx) => {
    thumb.addEventListener('click', () => {
      if (!slideSrcs.length) return;
      setSlide(idx);
    });
  });

  if (prevBtn && nextBtn && slideSrcs.length > 1) {
    prevBtn.addEventListener('click', () => setSlide(slideIndex - 1));
    nextBtn.addEventListener('click', () => setSlide(slideIndex + 1));

    let touchX = 0;
    mainImg?.addEventListener('touchstart', e => {
      if (!e.touches || !e.touches.length) return;
      touchX = e.touches[0].clientX;
    }, { passive: true });
    mainImg?.addEventListener('touchend', e => {
      if (!e.changedTouches || !e.changedTouches.length) return;
      const dx = touchX - e.changedTouches[0].clientX;
      if (Math.abs(dx) < 40) return;
      if (dx > 0) setSlide(slideIndex + 1);
      else setSlide(slideIndex - 1);
    }, { passive: true });
  }

  /* Quantity selector */
  const qtyVal   = document.getElementById('pdQtyVal');
  const qtyMinus = document.getElementById('pdQtyMinus');
  const qtyPlus  = document.getElementById('pdQtyPlus');

  qtyMinus.addEventListener('click', () => { const v = parseInt(qtyVal.value); if (v > 1) qtyVal.value = v - 1; });
  qtyPlus.addEventListener('click',  () => { const v = parseInt(qtyVal.value); if (v < 10) qtyVal.value = v + 1; });

  /* Add to cart */
  const addBtn = document.getElementById('pdAddCart');
  if (addBtn && !addBtn.disabled) {
    addBtn.addEventListener('click', function () {
      const qty = parseInt(qtyVal.value) || 1;
      if (!window.GadgetHubCart) return;

      for (let i = 0; i < qty; i++) {
        GadgetHubCart.addItem(productData);
      }
      if (window.GadgetHubShowCartPopup) window.GadgetHubShowCartPopup();

      this.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
      this.style.background = '#16a34a';
      setTimeout(() => {
        this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
        this.style.background = '';
      }, 2000);
    });
  }

  /* Wishlist toggle */
  const wishBtn = document.getElementById('pdWish');
  wishBtn.addEventListener('click', function () {
    if (!window.GadgetHubWishlist) return;
    const added = GadgetHubWishlist.toggle(productData);
    const icon = this.querySelector('i');
    icon.className         = added ? 'fas fa-heart' : 'far fa-heart';
    this.style.background  = added ? '#ef4444' : '';
    this.style.color       = added ? '#fff' : '';
    this.style.borderColor = added ? 'transparent' : '';
  });

  // Restore wishlist state on load
  if (window.GadgetHubWishlist && GadgetHubWishlist.has(productData.name)) {
    const icon = wishBtn.querySelector('i');
    icon.className         = 'fas fa-heart';
    wishBtn.style.background  = '#ef4444';
    wishBtn.style.color       = '#fff';
    wishBtn.style.borderColor = 'transparent';
  }

  /* Tabs */
  document.querySelectorAll('.pd-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      document.querySelectorAll('.pd-tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.pd-tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('tab-' + tab).classList.add('active');
    });
  });
})();
</script>
</body>
</html>
