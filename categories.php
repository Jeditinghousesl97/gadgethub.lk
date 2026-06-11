<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';
$db = getDB();
ensureProductCategoriesTable();

$categories = $db->query('
    SELECT c.*, COUNT(DISTINCT p.id) AS product_count
    FROM categories c
    LEFT JOIN product_categories pc ON pc.category_id = c.id
    LEFT JOIN products p ON p.id = pc.product_id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?= renderSeoHead([
      'title' => 'Categories | Gadget Hub',
      'description' => 'Browse all product categories at Gadget Hub and find computer parts, electronics, and accessories by category.',
      'canonical' => 'categories.php',
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
      <span class="section-tag">Browse Fast</span>
      <h1>All <em>Categories</em></h1>
      <p>Jump straight into the category you need and explore matching products quickly.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Categories</span>
    </nav>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cat-page-grid">
      <?php if ($categories): ?>
        <?php foreach ($categories as $i => $cat): ?>
          <a href="shop.php?cat=<?= urlencode($cat['slug']) ?>" class="cat-page-card" data-anim="up" data-delay="<?= ($i % 5) + 1 ?>">
            <div class="cat-page-ico"><i class="<?= htmlspecialchars($cat['icon'] ?: 'fas fa-box') ?>"></i></div>
            <div class="cat-page-txt">
              <h3><?= htmlspecialchars($cat['name']) ?></h3>
              <p><?= (int)$cat['product_count'] ?> item<?= (int)$cat['product_count'] !== 1 ? 's' : '' ?></p>
            </div>
            <i class="fas fa-chevron-right cat-page-arr"></i>
          </a>
        <?php endforeach ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">
          <i class="fas fa-th-large" style="font-size:32px;margin-bottom:12px;display:block;opacity:.4"></i>
          <p>No categories found.</p>
        </div>
      <?php endif ?>
    </div>
  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>

<button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top">
  <i class="fas fa-chevron-up"></i>
</button>

<script src="assets/js/main.js"></script>
</body>
</html>
