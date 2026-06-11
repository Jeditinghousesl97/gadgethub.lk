<?php
require_once __DIR__ . '/db.php';

// Formatting 
function fmtPrice(float $n): string {
    return 'Rs. ' . number_format($n, 2);
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 0)      return date('d M Y', strtotime($datetime));
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M Y', strtotime($datetime));
}

function slugify(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    return trim($text, '-');
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Request helpers 
function isPost(): bool  { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function isAjax(): bool  { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }
function jsonOk(string $msg, array $extra = []): never  { while (ob_get_level() > 0) ob_end_clean(); header('Content-Type: application/json'); echo json_encode(['success' => true,  'message' => $msg] + $extra); exit; }
function jsonErr(string $msg): never                    { while (ob_get_level() > 0) ob_end_clean(); header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $msg]); exit; }
function post(string $k, string $d = ''): string { return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function get(string  $k, string $d = ''): string { return isset($_GET[$k])  ? trim($_GET[$k])  : $d; }

function redirect(string $url): never {
    while (ob_get_level() > 0) ob_end_clean();
    header('Location: ' . $url);
    exit;
}

function writeGatewayDebugLog(string $gateway, string $event, array $data = []): void {
    $gateway = preg_replace('/[^a-z0-9_-]/i', '', strtolower(trim($gateway))) ?: 'gateway';
    $dir = ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $payload = [
        'timestamp' => date('c'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'data' => $data,
    ];

    $line = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($line === false) {
        $line = '{"timestamp":"' . date('c') . '","event":"' . addslashes($event) . '","data":"log_encode_failed"}';
    }

    @file_put_contents($dir . $gateway . '.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Settings 
function ensureSettingsTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    getDB()->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(190) NOT NULL UNIQUE,
            setting_value LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

function getSetting(string $key, string $default = ''): string {
    ensureSettingsTable();
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $stmt = getDB()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row !== false ? (string)($row['setting_value'] ?? '') : $default;
    } catch (Throwable) {
        $cache[$key] = $default;
    }
    return $cache[$key];
}

function setSetting(string $key, string $value): void {
    ensureSettingsTable();
    getDB()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?)
                      ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
           ->execute([$key, $value]);
}

function normalizePemKey(string $key): string {
    $key = trim(str_replace(["\r\n", "\r"], "\n", $key));
    return preg_replace("/\n{3,}/", "\n\n", $key) ?? $key;
}

function normalizeGatewayToken(string $value): string {
    return preg_replace('/\s+/', '', trim($value)) ?? trim($value);
}

function signKokoPayload(string $dataString, string $privateKey): string {
    if (!function_exists('openssl_pkey_get_private') || !function_exists('openssl_sign')) {
        throw new RuntimeException('OpenSSL extension is required for KOKO payments.');
    }

    $normalizedKey = normalizePemKey($privateKey);
    if ($normalizedKey === '') {
        throw new RuntimeException('Missing KOKO private key.');
    }

    $resource = openssl_pkey_get_private($normalizedKey);
    if ($resource === false) {
        throw new RuntimeException('Invalid KOKO private key.');
    }

    $signature = '';
    $signed = openssl_sign($dataString, $signature, $resource, OPENSSL_ALGO_SHA256);
    if (PHP_VERSION_ID < 80000 && is_resource($resource)) {
        openssl_free_key($resource);
    }

    if (!$signed) {
        throw new RuntimeException('Could not sign KOKO request payload.');
    }

    return base64_encode($signature);
}

function verifyKokoSignature(string $dataString, string $signature, string $publicKey): bool {
    if (!function_exists('openssl_pkey_get_public') || !function_exists('openssl_verify')) {
        return false;
    }

    $normalizedKey = normalizePemKey($publicKey);
    if ($normalizedKey === '') {
        return false;
    }

    $decodedSignature = base64_decode($signature, true);
    if ($decodedSignature === false) {
        return false;
    }

    $resource = openssl_pkey_get_public($normalizedKey);
    if ($resource === false) {
        return false;
    }

    $verified = openssl_verify($dataString, $decodedSignature, $resource, OPENSSL_ALGO_SHA256);
    if (PHP_VERSION_ID < 80000 && is_resource($resource)) {
        openssl_free_key($resource);
    }

    return $verified === 1;
}

function mapKokoStatusToPaymentStatus(string $status): string {
    return match (strtoupper(trim($status))) {
        'SUCCESS' => 'paid',
        'PENDING' => 'awaiting_payment',
        'FAILED' => 'failed',
        'CANCELED', 'CANCELLED' => 'cancelled',
        default => 'awaiting_payment',
    };
}

function mergePaymentStatus(string $currentStatus, string $incomingStatus): string {
    $currentStatus = strtolower(trim($currentStatus));
    $incomingStatus = strtolower(trim($incomingStatus));

    if ($incomingStatus === '') {
        return $currentStatus !== '' ? $currentStatus : 'pending';
    }

    // Never downgrade a confirmed paid status back to a pending-like state
    // from a later reconciliation check unless the gateway reports a terminal
    // negative state explicitly.
    if ($currentStatus === 'paid' && in_array($incomingStatus, ['pending', 'awaiting_payment'], true)) {
        return 'paid';
    }

    return $incomingStatus;
}

function mapPayhereStatusToPaymentStatus(int $statusCode): string {
    return match ($statusCode) {
        2 => 'paid',
        0 => 'pending',
        -1 => 'cancelled',
        default => 'failed',
    };
}

function processPayherePaymentPayload(array $payload): array {
    ensureOrderPaymentColumns();

    $merchantId = trim((string)getSetting('pm_payhere_merchant_id', ''));
    $merchantSecret = trim((string)getSetting('pm_payhere_merchant_secret', ''));
    if ($merchantId === '' || $merchantSecret === '') {
        throw new RuntimeException('PayHere not configured');
    }

    $postedMerchant = trim((string)($payload['merchant_id'] ?? ''));
    $orderId = trim((string)($payload['order_id'] ?? ''));
    $payhereAmount = trim((string)($payload['payhere_amount'] ?? $payload['amount'] ?? ''));
    $payhereCurrency = strtoupper(trim((string)($payload['payhere_currency'] ?? $payload['currency'] ?? '')));
    $statusCode = (int)($payload['status_code'] ?? 0);
    $receivedSig = strtoupper(trim((string)($payload['md5sig'] ?? '')));
    $paymentId = trim((string)($payload['payment_id'] ?? ''));

    if ($orderId === '' || $receivedSig === '' || $postedMerchant !== $merchantId) {
        throw new InvalidArgumentException('Invalid payload');
    }

    $localMd5 = strtoupper(md5(
        $merchantId .
        $orderId .
        $payhereAmount .
        $payhereCurrency .
        $statusCode .
        strtoupper(md5($merchantSecret))
    ));

    if (!hash_equals($localMd5, $receivedSig)) {
        throw new RuntimeException('Invalid signature');
    }

    $stmt = getDB()->prepare('SELECT id, payment_method, payment_status, payment_meta FROM orders WHERE order_number = ? LIMIT 1');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) {
        throw new RuntimeException('Order not found');
    }

    $incomingPaymentStatus = mapPayhereStatusToPaymentStatus($statusCode);
    $currentPaymentStatus = (string)($order['payment_status'] ?? '');
    $newPaymentStatus = mergePaymentStatus($currentPaymentStatus, $incomingPaymentStatus);

    $meta = [];
    if (!empty($order['payment_meta'])) {
        $meta = json_decode($order['payment_meta'], true);
        if (!is_array($meta)) {
            $meta = [];
        }
    }
    $meta['payhere'] = [
        'status_code' => $statusCode,
        'payment_id' => $paymentId,
        'currency' => $payhereCurrency,
        'amount' => $payhereAmount,
        'updated_at' => date('c'),
    ];

    getDB()->prepare('UPDATE orders SET payment_status = ?, payment_meta = ? WHERE id = ?')
        ->execute([$newPaymentStatus, json_encode($meta, JSON_UNESCAPED_UNICODE), $order['id']]);

    return [
        'order' => $order,
        'payment_status' => $newPaymentStatus,
        'status_code' => $statusCode,
        'payment_id' => $paymentId,
        'order_id' => $orderId,
    ];
}

function getKokoReadiness(): array {
    $missing = [];
    if (!function_exists('openssl_sign') || !function_exists('openssl_verify')) {
        $missing[] = 'PHP OpenSSL extension';
    }
    if (trim(getSetting('pm_koko_merchant_id', '')) === '') {
        $missing[] = 'Merchant ID';
    }
    if (trim(getSetting('pm_koko_api_key', '')) === '') {
        $missing[] = 'API Key';
    }
    if (trim(getSetting('pm_koko_public_key', '')) === '') {
        $missing[] = 'KOKO public key';
    }
    if (trim(getSetting('pm_koko_private_key', '')) === '') {
        $missing[] = 'Merchant private key';
    }
    if (!function_exists('curl_init') && !ini_get('allow_url_fopen')) {
        $missing[] = 'HTTP client support (cURL or allow_url_fopen)';
    }

    return [
        'ready' => empty($missing),
        'missing' => $missing,
        'warnings' => array_values(array_filter([
            trim(getSetting('pm_koko_merchant_id', '')) !== normalizeGatewayToken(getSetting('pm_koko_merchant_id', '')) ? 'Merchant ID contains whitespace' : '',
            trim(getSetting('pm_koko_api_key', '')) !== normalizeGatewayToken(getSetting('pm_koko_api_key', '')) ? 'API Key contains whitespace' : '',
        ])),
    ];
}

function postKokoForm(string $url, array $fields): array {
    $body = http_build_query($fields);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);
        $responseBody = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false) {
            throw new RuntimeException($curlError ?: 'KOKO request failed.');
        }

        return ['status_code' => $httpCode, 'body' => (string)$responseBody];
    }

    if (!ini_get('allow_url_fopen')) {
        throw new RuntimeException('No supported HTTP client is available for KOKO requests.');
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $body,
            'timeout' => 30,
            'ignore_errors' => true,
        ],
    ]);
    $responseBody = @file_get_contents($url, false, $context);
    if ($responseBody === false) {
        throw new RuntimeException('KOKO request failed.');
    }

    $statusCode = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/HTTP\/\S+\s+(\d{3})/', $header, $m)) {
            $statusCode = (int)$m[1];
            break;
        }
    }

    return ['status_code' => $statusCode, 'body' => (string)$responseBody];
}

function parseKokoApiResponse(string $body): array {
    $body = trim($body);
    if ($body === '') {
        return [];
    }

    $json = json_decode($body, true);
    if (is_array($json)) {
        return $json;
    }

    parse_str($body, $parsed);
    if (is_array($parsed) && !empty($parsed)) {
        return $parsed;
    }

    return ['raw' => $body];
}

function ensureBrandsTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    getDB()->exec("
        CREATE TABLE IF NOT EXISTS brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL UNIQUE,
            description TEXT NULL,
            filter_tags VARCHAR(255) NOT NULL DEFAULT 'other',
            logo_path VARCHAR(255) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

function ensureHeroSlidesTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    getDB()->exec("
        CREATE TABLE IF NOT EXISTS hero_slides (
            id INT AUTO_INCREMENT PRIMARY KEY,
            desktop_image VARCHAR(255) NOT NULL,
            mobile_image VARCHAR(255) NOT NULL,
            link_url VARCHAR(500) NULL,
            open_in_new_tab TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Backward-compatible migrations for existing installs
    try {
        getDB()->exec("ALTER TABLE hero_slides ADD COLUMN open_in_new_tab TINYINT(1) NOT NULL DEFAULT 0 AFTER link_url");
    } catch (Throwable) {}
}

function ensurePromoBannersTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    getDB()->exec("
        CREATE TABLE IF NOT EXISTS promo_banners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_path VARCHAR(255) NOT NULL,
            link_url VARCHAR(500) NULL,
            open_in_new_tab TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    try {
        getDB()->exec("ALTER TABLE promo_banners ADD COLUMN open_in_new_tab TINYINT(1) NOT NULL DEFAULT 0 AFTER link_url");
    } catch (Throwable) {}
}

function getSriLankaDistricts(): array {
    return [
        'Ampara','Anuradhapura','Badulla','Batticaloa','Colombo','Galle','Gampaha',
        'Hambantota','Jaffna','Kalutara','Kandy','Kegalle','Kilinochchi','Kurunegala',
        'Mannar','Matale','Matara','Monaragala','Mullaitivu','Nuwara Eliya',
        'Polonnaruwa','Puttalam','Ratnapura','Trincomalee','Vavuniya',
    ];
}

function ensureProductWeightColumn(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    try {
        getDB()->exec("ALTER TABLE products ADD COLUMN weight_kg DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER stock_qty");
    } catch (Throwable) {}
}

function ensureProductFreeDeliveryColumn(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    try {
        getDB()->exec("ALTER TABLE products ADD COLUMN free_delivery TINYINT(1) NOT NULL DEFAULT 0 AFTER weight_kg");
    } catch (Throwable) {}
}

function ensureProductCategoriesTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    $sqlWithForeignKeys = "
        CREATE TABLE IF NOT EXISTS product_categories (
            product_id INT NOT NULL,
            category_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (product_id, category_id),
            KEY idx_product_categories_category (category_id),
            CONSTRAINT fk_product_categories_product
                FOREIGN KEY (product_id) REFERENCES products(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_product_categories_category
                FOREIGN KEY (category_id) REFERENCES categories(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $sqlFallback = "
        CREATE TABLE IF NOT EXISTS product_categories (
            product_id INT NOT NULL,
            category_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (product_id, category_id),
            KEY idx_product_categories_category (category_id),
            KEY idx_product_categories_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    try {
        getDB()->exec($sqlWithForeignKeys);
    } catch (Throwable) {
        getDB()->exec($sqlFallback);
    }

    try {
        getDB()->exec("
            INSERT IGNORE INTO product_categories (product_id, category_id)
            SELECT id, category_id
            FROM products
            WHERE category_id IS NOT NULL
        ");
    } catch (Throwable) {}
}

function getProductCategoryIds(int $productId): array {
    ensureProductCategoriesTable();

    $stmt = getDB()->prepare('SELECT category_id FROM product_categories WHERE product_id = ? ORDER BY category_id ASC');
    $stmt->execute([$productId]);
    $ids = array_map('intval', array_column($stmt->fetchAll(), 'category_id'));

    if ($ids) {
        return $ids;
    }

    $fallback = getDB()->prepare('SELECT category_id FROM products WHERE id = ? LIMIT 1');
    $fallback->execute([$productId]);
    $primary = (int)$fallback->fetchColumn();

    return $primary > 0 ? [$primary] : [];
}

function syncProductCategories(int $productId, array $categoryIds): void {
    ensureProductCategoriesTable();

    $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds), fn($id) => $id > 0)));

    $delete = getDB()->prepare('DELETE FROM product_categories WHERE product_id = ?');
    $delete->execute([$productId]);

    if (!$categoryIds) {
        return;
    }

    $insert = getDB()->prepare('INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)');
    foreach ($categoryIds as $categoryId) {
        $insert->execute([$productId, $categoryId]);
    }
}

function ensureDeliveryRatesTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    getDB()->exec("
        CREATE TABLE IF NOT EXISTS delivery_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            district VARCHAR(120) NOT NULL UNIQUE,
            first_kg_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            additional_kg_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

function ensureOrderPaymentColumns(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    try { getDB()->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER source"); } catch (Throwable) {}
    try { getDB()->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER payment_method"); } catch (Throwable) {}
    try { getDB()->exec("ALTER TABLE orders ADD COLUMN payment_meta TEXT NULL AFTER payment_status"); } catch (Throwable) {}
    try { getDB()->exec("ALTER TABLE orders ADD COLUMN handling_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER delivery_charge"); } catch (Throwable) {}
}

function ensureOrderCustomerColumns(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    try { getDB()->exec("ALTER TABLE orders ADD COLUMN customer_phone_alt VARCHAR(50) NULL AFTER customer_phone"); } catch (Throwable) {}
    try { getDB()->exec("ALTER TABLE orders ADD COLUMN customer_city VARCHAR(120) NULL AFTER customer_email"); } catch (Throwable) {}
    try { getDB()->exec("ALTER TABLE orders ADD COLUMN customer_district VARCHAR(120) NULL AFTER customer_city"); } catch (Throwable) {}
}

function getPaymentMethodPresentation(string $paymentMethod): array {
    $map = [
        'cod' => [
            'label' => 'Cash on Delivery',
            'image' => 'images/payments/cod-logo.png',
            'alt' => 'Cash on Delivery',
        ],
        'bank_transfer' => [
            'label' => 'Bank Transfer',
            'image' => 'images/payments/bank-transfer-logo.png',
            'alt' => 'Bank Transfer',
        ],
        'whatsapp' => [
            'label' => 'WhatsApp Order',
            'image' => 'images/payments/whatsapp-logo.png',
            'alt' => 'WhatsApp Order',
        ],
        'payhere' => [
            'label' => 'PayHere',
            'image' => 'images/payments/payhere-logo.png',
            'alt' => 'PayHere',
        ],
        'koko' => [
            'label' => 'KOKO',
            'image' => 'images/payments/koko-logo.png',
            'alt' => 'KOKO',
        ],
    ];

    return $map[$paymentMethod] ?? [
        'label' => $paymentMethod !== '' ? ucfirst(str_replace('_', ' ', $paymentMethod)) : '-',
        'image' => '',
        'alt' => $paymentMethod !== '' ? ucfirst(str_replace('_', ' ', $paymentMethod)) : 'Payment method',
    ];
}

function normalizeOrderDisplayData(array $order): array {
    $address = (string)($order['customer_address'] ?? '');
    $notes = (string)($order['notes'] ?? '');

    $city = trim((string)($order['customer_city'] ?? ''));
    if ($city === '' && preg_match('/(?:^|\R)City:\s*(.+)$/mi', $address, $m)) {
        $city = trim($m[1]);
    }

    $district = trim((string)($order['customer_district'] ?? ''));
    if ($district === '' && preg_match('/(?:^|\R)District:\s*(.+)$/mi', $address, $m)) {
        $district = trim($m[1]);
    }

    $phoneAlt = trim((string)($order['customer_phone_alt'] ?? ''));
    if ($phoneAlt === '' && preg_match('/(?:^|\R)Alternate Phone:\s*(.+)$/mi', $notes, $m)) {
        $phoneAlt = trim($m[1]);
    }

    $addressDisplay = preg_replace('/\R?City:\s*.+$/mi', '', $address) ?? $address;
    $addressDisplay = preg_replace('/\R?District:\s*.+$/mi', '', $addressDisplay) ?? $addressDisplay;
    $addressDisplay = trim($addressDisplay);

    $notesDisplay = preg_replace('/(?:^|\R)Alternate Phone:\s*.+$/mi', '', $notes) ?? $notes;
    $notesDisplay = trim($notesDisplay);

    $paymentMeta = $order['payment_meta'] ?? null;
    if (is_string($paymentMeta) && $paymentMeta !== '') {
        $decoded = json_decode($paymentMeta, true);
        if (is_array($decoded)) {
            $paymentMeta = $decoded;
        }
    }

    return $order + [
        'customer_city_display' => $city,
        'customer_district_display' => $district,
        'customer_phone_alt_display' => $phoneAlt,
        'customer_address_display' => $addressDisplay,
        'notes_display' => $notesDisplay,
        'payment_meta_array' => is_array($paymentMeta) ? $paymentMeta : [],
    ];
}

function getPaymentMethodsConfig(): array {
    return [
        'cod' => [
            'key' => 'cod',
            'label' => 'Cash on Delivery',
            'enabled' => getSetting('pm_cod_enabled', '1') === '1',
            'description' => getSetting('pm_cod_desc', 'Pay in cash when your order arrives.'),
        ],
        'bank_transfer' => [
            'key' => 'bank_transfer',
            'label' => 'Bank Transfer',
            'enabled' => getSetting('pm_bank_enabled', '0') === '1',
            'description' => getSetting('pm_bank_desc', 'Transfer to our bank account and share payment reference.'),
            'bank_name' => getSetting('pm_bank_name', ''),
            'account_name' => getSetting('pm_bank_account_name', ''),
            'account_number' => getSetting('pm_bank_account_number', ''),
            'branch' => getSetting('pm_bank_branch', ''),
            'instructions' => getSetting('pm_bank_instructions', ''),
        ],
        'whatsapp' => [
            'key' => 'whatsapp',
            'label' => 'WhatsApp Order',
            'enabled' => getSetting('pm_whatsapp_enabled', '1') === '1',
            'description' => getSetting('pm_whatsapp_desc', 'Finalize your order details with our team via WhatsApp.'),
        ],
        'payhere' => [
            'key' => 'payhere',
            'label' => 'PayHere (Card Payment)',
            'enabled' => getSetting('pm_payhere_enabled', '0') === '1',
            'description' => getSetting('pm_payhere_desc', 'Pay securely online using Visa, Mastercard, or other supported methods.'),
            'merchant_id' => getSetting('pm_payhere_merchant_id', ''),
            'sandbox' => getSetting('pm_payhere_sandbox', '1') === '1',
            'notes' => getSetting('pm_payhere_notes', ''),
            'handling_fee_percent' => getPaymentHandlingFeePercent('payhere'),
        ],
        'koko' => [
            'key' => 'koko',
            'label' => 'KOKO (Buy Now Pay Later)',
            'enabled' => getSetting('pm_koko_enabled', '0') === '1',
            'description' => getSetting('pm_koko_desc', 'Pay in 3 interest free instalments with KOKO.'),
            'merchant_id' => getSetting('pm_koko_merchant_id', ''),
            'api_key' => getSetting('pm_koko_api_key', ''),
            'plugin_name' => getSetting('pm_koko_plugin_name', 'customapi'),
            'plugin_version' => getSetting('pm_koko_plugin_version', '1.0.1'),
            'public_key' => getSetting('pm_koko_public_key', ''),
            'private_key' => getSetting('pm_koko_private_key', ''),
            'sandbox' => getSetting('pm_koko_sandbox', '1') === '1',
            'notes' => getSetting('pm_koko_notes', ''),
            'handling_fee_percent' => getPaymentHandlingFeePercent('koko'),
        ],
    ];
}

function getPaymentHandlingFeePercent(string $paymentMethod): float {
    $settingKey = match ($paymentMethod) {
        'payhere' => 'pm_payhere_handling_fee_percent',
        'koko' => 'pm_koko_handling_fee_percent',
        default => '',
    };

    if ($settingKey === '') {
        return 0.0;
    }

    $raw = trim(getSetting($settingKey, '0'));
    $percent = is_numeric($raw) ? (float)$raw : 0.0;
    return max(0.0, min(100.0, $percent));
}

function calculatePaymentHandlingFee(float $baseAmount, string $paymentMethod): array {
    $percent = getPaymentHandlingFeePercent($paymentMethod);
    $baseAmount = max(0.0, $baseAmount);
    $fee = $percent > 0 ? round(($baseAmount * $percent) / 100, 2) : 0.0;

    return [
        'percent' => $percent,
        'amount' => $fee,
        'base_amount' => round($baseAmount, 2),
    ];
}

function getEnabledPaymentMethods(): array {
    $all = getPaymentMethodsConfig();
    $enabled = [];
    foreach ($all as $k => $cfg) {
        if (!empty($cfg['enabled'])) $enabled[$k] = $cfg;
    }
    return $enabled;
}

function getDeliveryRatesMap(): array {
    ensureDeliveryRatesTable();
    $rows = getDB()->query("SELECT district, first_kg_fee, additional_kg_fee, is_active FROM delivery_rates")->fetchAll();
    $map = [];
    foreach ($rows as $r) {
        $map[$r['district']] = [
            'first_kg_fee' => (float)$r['first_kg_fee'],
            'additional_kg_fee' => (float)$r['additional_kg_fee'],
            'is_active' => (int)$r['is_active'],
        ];
    }
    return $map;
}

function calculateDeliveryFee(string $district, array $cartItems, float $subtotal = 0.0): array {
    $district = trim($district);
    $ratesMap = getDeliveryRatesMap();
    $rate = $ratesMap[$district] ?? null;

    $totalWeight = 0.0;
    $chargeableItemCount = 0;
    foreach ($cartItems as $it) {
        $isFreeDelivery = !empty($it['free_delivery']);
        if ($isFreeDelivery) {
            continue;
        }
        $qty = max(1, (int)($it['qty'] ?? 1));
        $w = max(0.0, (float)($it['weight_kg'] ?? $it['weight'] ?? 0));
        $totalWeight += ($w * $qty);
        $chargeableItemCount += $qty;
    }
    $totalWeight = round($totalWeight, 3);
    $chargeableKg = $chargeableItemCount > 0
        ? max(1, (int)ceil($totalWeight > 0 ? $totalWeight : 1))
        : 0;

    $delivery = 0.0;
    if ($chargeableKg > 0 && $rate && (int)$rate['is_active'] === 1) {
        $first = max(0.0, (float)$rate['first_kg_fee']);
        $add   = max(0.0, (float)$rate['additional_kg_fee']);
        $delivery = $first + max(0, $chargeableKg - 1) * $add;
    }

    $enableFree = getSetting('enable_free_delivery_min', '0') === '1';
    if ($enableFree) {
        $freeMin = (float)getSetting('free_delivery_min', '0');
        if ($freeMin > 0 && $subtotal >= $freeMin) {
            $delivery = 0.0;
        }
    }

    return [
        'district' => $district,
        'total_weight_kg' => $totalWeight,
        'chargeable_kg' => $chargeableKg,
        'delivery_fee' => round($delivery, 2),
        'has_rate' => $rate ? 1 : 0,
    ];
}

// Order number 
function generateOrderNumber(): string {
    $year = date('Y');
    $stmt = getDB()->prepare(
        "SELECT MAX(CAST(SUBSTRING_INDEX(order_number, '-', -1) AS UNSIGNED))
         FROM orders WHERE order_number LIKE ?"
    );
    $stmt->execute(["GNX-{$year}-%"]);
    $next = (int)$stmt->fetchColumn() + 1;

    do {
        $num    = 'GNX-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
        $exists = getDB()->prepare('SELECT id FROM orders WHERE order_number = ?');
        $exists->execute([$num]);
        if (!$exists->fetch()) break;
        $next++;
    } while (true);

    return $num;
}

// Image upload 
function uploadImage(array $file, string $subdir = 'products'): ?string {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if ($file['error'] !== UPLOAD_ERR_OK)          return null;
    if (!in_array($file['type'], $allowed, true))  return null;
    if ($file['size'] > 5 * 1024 * 1024)           return null;

    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = uniqid('img_', true) . '.' . $ext;
    $dir  = UPLOAD_DIR . $subdir . DIRECTORY_SEPARATOR;

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return 'uploads/' . $subdir . '/' . $name;
    }
    return null;
}

function buildGatewayRedirectForExistingOrder(array $order): array {
    ensureOrderPaymentColumns();
    $paymentMethod = trim((string)($order['payment_method'] ?? ''));
    if (!in_array($paymentMethod, ['payhere', 'koko'], true)) {
        throw new RuntimeException('Payment retry is only available for online gateway orders.');
    }

    $enabledMethods = getEnabledPaymentMethods();
    if (!isset($enabledMethods[$paymentMethod])) {
        throw new RuntimeException('This payment method is currently disabled.');
    }

    $order = normalizeOrderDisplayData($order);
    $orderNumber = trim((string)($order['order_number'] ?? ''));
    if ($orderNumber === '') {
        throw new RuntimeException('Missing order number.');
    }

    if (strtolower(trim((string)($order['payment_status'] ?? ''))) === 'paid') {
        throw new RuntimeException('This order is already paid.');
    }

    $currency = strtoupper(trim(getSetting('currency_code', 'LKR') ?: 'LKR'));
    $amount = number_format((float)($order['total'] ?? 0), 2, '.', '');
    $customerName = trim((string)($order['customer_name'] ?? 'Customer'));
    $email = trim((string)($order['customer_email'] ?? ''));
    $phone = trim((string)($order['customer_phone'] ?? ''));
    $city = trim((string)($order['customer_city_display'] ?? ''));
    $address = trim((string)($order['customer_address_display'] ?? ''));

    if ($paymentMethod === 'payhere') {
        $merchantId = normalizeGatewayToken(getSetting('pm_payhere_merchant_id', ''));
        $merchantSecret = normalizeGatewayToken(getSetting('pm_payhere_merchant_secret', ''));
        if ($merchantId === '' || $merchantSecret === '') {
            throw new RuntimeException('PayHere is not configured.');
        }

        $isSandbox = getSetting('pm_payhere_sandbox', '1') === '1';
        $secretHash = strtoupper(md5($merchantSecret));
        $hash = strtoupper(md5($merchantId . $orderNumber . $amount . $currency . $secretHash));

        return [
            'gateway' => 'payhere',
            'url' => $isSandbox ? 'https://sandbox.payhere.lk/pay/checkout' : 'https://www.payhere.lk/pay/checkout',
            'fields' => [
                'merchant_id' => $merchantId,
                'return_url' => BASE_URL . 'payhere-return.php?order=' . urlencode($orderNumber),
                'cancel_url' => BASE_URL . 'payhere-cancel.php?order=' . urlencode($orderNumber),
                'notify_url' => BASE_URL . 'api/payhere-notify.php',
                'order_id' => $orderNumber,
                'items' => 'Order ' . $orderNumber,
                'currency' => $currency,
                'amount' => $amount,
                'first_name' => $customerName,
                'last_name' => '',
                'email' => $email !== '' ? $email : 'no-reply@example.com',
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'country' => 'Sri Lanka',
                'hash' => $hash,
            ],
        ];
    }

    $merchantId = normalizeGatewayToken(getSetting('pm_koko_merchant_id', ''));
    $apiKey = normalizeGatewayToken(getSetting('pm_koko_api_key', ''));
    $privateKey = trim(getSetting('pm_koko_private_key', ''));
    $publicKey = trim(getSetting('pm_koko_public_key', ''));
    $pluginName = trim(getSetting('pm_koko_plugin_name', 'customapi')) ?: 'customapi';
    $pluginVersion = trim(getSetting('pm_koko_plugin_version', '1.0.1')) ?: '1.0.1';
    if ($merchantId === '' || $apiKey === '' || $privateKey === '' || $publicKey === '') {
        throw new RuntimeException('KOKO is not configured.');
    }

    $isSandbox = getSetting('pm_koko_sandbox', '1') === '1';
    $gatewayUrl = $isSandbox
        ? 'https://qaapi.paykoko.com/api/merchants/orderCreate'
        : 'https://prodapi.paykoko.com/api/merchants/orderCreate';

    $nameParts = preg_split('/\s+/', $customerName) ?: [];
    $firstName = $nameParts[0] ?? 'Customer';
    $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '.';
    $reference = $orderNumber;
    $returnUrl = BASE_URL . 'koko-return.php?order=' . urlencode($orderNumber);
    $cancelUrl = BASE_URL . 'koko-cancel.php?order=' . urlencode($orderNumber);
    $responseUrl = BASE_URL . 'api/koko-notify.php';
    $items = json_decode((string)($order['items_json'] ?? '[]'), true);
    $itemCount = is_array($items) ? count($items) : 0;
    $description = $itemCount > 0
        ? $itemCount . ' product' . ($itemCount === 1 ? '' : 's')
        : ('Order ' . $orderNumber);

    $dataString = $merchantId .
        $amount .
        $currency .
        $pluginName .
        $pluginVersion .
        $returnUrl .
        $cancelUrl .
        $orderNumber .
        $reference .
        $firstName .
        $lastName .
        ($email !== '' ? $email : 'no-reply@example.com') .
        $description .
        $apiKey .
        $responseUrl;

    $signature = signKokoPayload($dataString, $privateKey);

    return [
        'gateway' => 'koko',
        'url' => $gatewayUrl,
        'fields' => [
            '_mId' => $merchantId,
            'api_key' => $apiKey,
            '_returnUrl' => $returnUrl,
            '_cancelUrl' => $cancelUrl,
            '_responseUrl' => $responseUrl,
            '_currency' => $currency,
            '_amount' => $amount,
            '_reference' => $reference,
            '_pluginName' => $pluginName,
            '_pluginVersion' => $pluginVersion,
            '_orderId' => $orderNumber,
            '_firstName' => $firstName,
            '_lastName' => $lastName,
            '_email' => $email !== '' ? $email : 'no-reply@example.com',
            '_description' => $description,
            '_mobileNo' => preg_replace('/\D+/', '', $phone),
            'dataString' => $dataString,
            'signature' => $signature,
        ],
    ];
}

function deleteUploadedFile(string $path): void {
    $path = trim($path);
    if ($path === '' || !str_starts_with($path, 'uploads/')) return;
    $full = ROOT_PATH . $path;
    if (file_exists($full)) {
        @unlink($full);
    }
}

// Status badge HTML 
function orderStatusBadge(string $status): string {
    $map = [
        'pending'    => ['#f59e0b', 'Pending'],
        'confirmed'  => ['#3b82f6', 'Confirmed'],
        'processing' => ['#8b5cf6', 'Processing'],
        'dispatched' => ['#06b6d4', 'Dispatched'],
        'delivered'  => ['#10b981', 'Delivered'],
        'cancelled'  => ['#ef4444', 'Cancelled'],
    ];
    [$color, $label] = $map[$status] ?? ['#6b7280', ucfirst($status)];
    return "<span style='background:{$color}22;color:{$color};padding:3px 10px;border-radius:50px;font-size:12px;font-weight:600'>{$label}</span>";
}

// Pagination 
function paginate(int $total, int $perPage, int $current): array {
    $pages = (int)ceil($total / $perPage);
    return [
        'total'    => $total,
        'per_page' => $perPage,
        'current'  => $current,
        'pages'    => $pages,
        'offset'   => ($current - 1) * $perPage,
        'has_prev' => $current > 1,
        'has_next' => $current < $pages,
    ];
}
