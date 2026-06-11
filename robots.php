<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/plain; charset=UTF-8');

$lines = [
    'User-agent: *',
    'Allow: /',
    'Disallow: /admin/',
    'Disallow: /includes/',
    'Disallow: /api/',
    'Disallow: /cart.php',
    'Disallow: /checkout.php',
    'Disallow: /account.php',
    'Disallow: /wishlist.php',
    'Sitemap: ' . BASE_URL . 'sitemap.xml',
];

$custom = trim(getSetting('seo_robots_custom', ''));
if ($custom !== '') {
    $lines[] = '';
    $lines[] = '# Custom directives';
    $lines[] = $custom;
}

echo implode("\n", $lines) . "\n";
