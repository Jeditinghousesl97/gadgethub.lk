<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: application/xml; charset=UTF-8');

$db = getDB();
ensureProductCategoriesTable();
ensureBrandsTable();

$urls = [];

$addUrl = static function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.6') use (&$urls): void {
    $urls[] = [
        'loc' => seoPathToUrl($loc),
        'lastmod' => $lastmod,
        'changefreq' => $changefreq,
        'priority' => $priority,
    ];
};

$fileLastmod = static function (string $path): ?string {
    $full = ROOT_PATH . ltrim($path, '/');
    if (!file_exists($full)) return null;
    return gmdate('c', (int)filemtime($full));
};

$addUrl('', $fileLastmod('index.php'), 'daily', '1.0');
$addUrl('shop.php', $fileLastmod('shop.php'), 'daily', '0.9');
$addUrl('categories.php', $fileLastmod('categories.php'), 'weekly', '0.8');
$addUrl('brands.php', $fileLastmod('brands.php'), 'weekly', '0.8');
$addUrl('about.php', $fileLastmod('about.php'), 'monthly', '0.7');
$addUrl('contact.php', $fileLastmod('contact.php'), 'monthly', '0.7');
$addUrl('wholesale.php', $fileLastmod('wholesale.php'), 'weekly', '0.8');
$addUrl('faq.php', $fileLastmod('faq.php'), 'monthly', '0.6');
$addUrl('shipping.php', $fileLastmod('shipping.php'), 'yearly', '0.4');
$addUrl('returns.php', $fileLastmod('returns.php'), 'yearly', '0.4');
$addUrl('privacy.php', $fileLastmod('privacy.php'), 'yearly', '0.3');
$addUrl('terms.php', $fileLastmod('terms.php'), 'yearly', '0.3');

try {
    $categories = $db->query("
        SELECT slug
        FROM categories
        WHERE is_active = 1
        ORDER BY sort_order ASC, name ASC
    ")->fetchAll();
    foreach ($categories as $category) {
        $addUrl('shop.php?cat=' . urlencode((string)$category['slug']), null, 'weekly', '0.7');
    }
} catch (Throwable) {
    // Keep sitemap available even if category schema differs on hosting.
}

try {
    $products = $db->query("
        SELECT id
        FROM products
        WHERE is_active = 1
        ORDER BY id DESC
    ")->fetchAll();
    foreach ($products as $product) {
        $addUrl('product.php?id=' . (int)$product['id'], null, 'weekly', '0.8');
    }
} catch (Throwable) {
    // Keep sitemap available even if product schema differs on hosting.
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?= htmlspecialchars($url['loc'], ENT_XML1) ?></loc>
    <?php if (!empty($url['lastmod'])): ?><lastmod><?= htmlspecialchars($url['lastmod'], ENT_XML1) ?></lastmod><?php endif; ?>
    <changefreq><?= htmlspecialchars($url['changefreq'], ENT_XML1) ?></changefreq>
    <priority><?= htmlspecialchars($url['priority'], ENT_XML1) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
