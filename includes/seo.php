<?php
require_once __DIR__ . '/functions.php';

function seoPathToUrl(string $path): string {
    if ($path === '') {
        return BASE_URL;
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return BASE_URL . ltrim($path, '/');
}

function seoCurrentUrl(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}

function seoCanonicalUrl(?string $path = null): string {
    if ($path === null || $path === '') {
        return seoCurrentUrl();
    }
    return seoPathToUrl($path);
}

function seoTrimText(string $text, int $max = 160): string {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    if ($text === '') return '';
    if (mb_strlen($text) <= $max) return $text;
    return rtrim(mb_substr($text, 0, $max - 1)) . '…';
}

function seoDefaultImageUrl(): string {
    $custom = trim(getSetting('seo_default_image', ''));
    if ($custom !== '' && file_exists(ROOT_PATH . $custom)) {
        return seoPathToUrl($custom);
    }

    $fallbacks = ['images/logo.jpg', 'images/logo.png'];
    foreach ($fallbacks as $path) {
        if (file_exists(ROOT_PATH . $path)) {
            return seoPathToUrl($path);
        }
    }

    return '';
}

function seoFaviconPath(): string {
    $custom = trim(getSetting('site_favicon', ''));
    if ($custom !== '' && file_exists(ROOT_PATH . $custom)) {
        return $custom;
    }
    $fallbacks = ['favicon.ico', 'images/logo.jpg', 'images/logo.png'];
    foreach ($fallbacks as $path) {
        if (file_exists(ROOT_PATH . $path)) {
            return $path;
        }
    }
    return '';
}

function seoOrganizationJsonLd(): array {
    $social = array_values(array_filter([
        trim(getSetting('facebook_url')),
        trim(getSetting('instagram_url')),
        trim(getSetting('youtube_url')),
        trim(getSetting('tiktok_url')),
    ]));

    $org = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => getSetting('store_name', 'Gadget Hub'),
        'url' => BASE_URL,
    ];

    $logoUrl = seoDefaultImageUrl();
    if ($logoUrl !== '') {
        $org['logo'] = $logoUrl;
        $org['image'] = $logoUrl;
    }

    $phone = trim(getSetting('store_phone', ''));
    if ($phone !== '') {
        $org['contactPoint'] = [[
            '@type' => 'ContactPoint',
            'telephone' => $phone,
            'contactType' => 'customer support',
            'areaServed' => 'LK',
            'availableLanguage' => ['en'],
        ]];
    }

    if ($social) {
        $org['sameAs'] = $social;
    }

    return $org;
}

function seoWebsiteJsonLd(): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => getSetting('seo_site_title', getSetting('store_name', 'Gadget Hub')),
        'url' => BASE_URL,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => seoPathToUrl('shop.php?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

function renderSeoHead(array $options = []): string {
    $siteTitle = trim(getSetting('seo_site_title', getSetting('store_name', 'Gadget Hub')));
    $defaultDescription = trim(getSetting('seo_meta_description', getSetting('store_tagline', 'Premium computer parts, electronics and accessories in Sri Lanka.')));
    $title = trim($options['title'] ?? $siteTitle);
    $description = seoTrimText((string)($options['description'] ?? $defaultDescription), 170);
    $canonical = seoCanonicalUrl($options['canonical'] ?? null);
    $robots = trim((string)($options['robots'] ?? 'index,follow'));
    $ogType = trim((string)($options['og_type'] ?? 'website'));
    $image = trim((string)($options['image'] ?? seoDefaultImageUrl()));
    $image = $image !== '' ? seoPathToUrl($image) : '';
    $faviconPath = seoFaviconPath();
    $faviconUrl = $faviconPath !== '' ? seoPathToUrl($faviconPath) : '';
    $googleVerification = trim(getSetting('seo_google_verification', ''));
    $twitterHandle = trim(getSetting('seo_twitter_handle', ''));
    $jsonLd = $options['json_ld'] ?? [];
    if (!is_array($jsonLd)) {
        $jsonLd = [$jsonLd];
    }
    if (($options['include_site_jsonld'] ?? true) === true) {
        array_unshift($jsonLd, seoWebsiteJsonLd());
        array_unshift($jsonLd, seoOrganizationJsonLd());
    }

    ob_start();
    ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($description) ?>">
  <meta name="robots" content="<?= htmlspecialchars($robots) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
  <meta property="og:locale" content="en_LK">
  <meta property="og:type" content="<?= htmlspecialchars($ogType) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
  <meta property="og:site_name" content="<?= htmlspecialchars($siteTitle) ?>">
  <?php if ($image !== ''): ?>
  <meta property="og:image" content="<?= htmlspecialchars($image) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($image) ?>">
  <?php endif; ?>
  <meta name="twitter:card" content="<?= $image !== '' ? 'summary_large_image' : 'summary' ?>">
  <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
  <?php if ($twitterHandle !== ''): ?>
  <meta name="twitter:site" content="<?= htmlspecialchars($twitterHandle) ?>">
  <?php endif; ?>
  <?php if ($faviconUrl !== ''): ?>
  <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>">
  <link rel="shortcut icon" href="<?= htmlspecialchars($faviconUrl) ?>">
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($faviconUrl) ?>">
  <?php endif; ?>
  <?php if ($googleVerification !== ''): ?>
  <meta name="google-site-verification" content="<?= htmlspecialchars($googleVerification) ?>">
  <?php endif; ?>
  <?php foreach ($jsonLd as $schema): ?>
    <?php if (is_array($schema) && !empty($schema)): ?>
  <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
  <?php endforeach; ?>
    <?php
    return (string)ob_get_clean();
}
