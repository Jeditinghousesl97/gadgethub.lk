<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/mailer.php';
require_once dirname(__DIR__) . '/includes/email-templates.php';
ensureProductWeightColumn();
ensureProductFreeDeliveryColumn();
ensureOrderPaymentColumns();
ensureOrderCustomerColumns();

// Parse body 
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request format.']);
    exit;
}

// Validate 
$name    = trim($data['name']    ?? '');
$phone   = trim($data['phone']   ?? '');
$phoneAlt= trim($data['phone_alt'] ?? '');
$city    = trim($data['city']    ?? '');
$district= trim($data['district'] ?? '');
$email   = trim($data['email']   ?? '');
$address = trim($data['address'] ?? '');
$notes   = trim($data['notes']   ?? '');
$paymentMethod = trim((string)($data['payment_method'] ?? ''));
$items   = $data['cart']         ?? [];

$enabledMethods = getEnabledPaymentMethods();
$enabledMethodKeys = array_keys($enabledMethods);
$payhereMerchantId = normalizeGatewayToken(getSetting('pm_payhere_merchant_id', ''));
$payhereMerchantSecret = normalizeGatewayToken(getSetting('pm_payhere_merchant_secret', ''));
$kokoMerchantId = normalizeGatewayToken(getSetting('pm_koko_merchant_id', ''));
$kokoApiKey = normalizeGatewayToken(getSetting('pm_koko_api_key', ''));
$kokoPrivateKey = trim(getSetting('pm_koko_private_key', ''));
$kokoPublicKey = trim(getSetting('pm_koko_public_key', ''));

$errors = [];
if (!$name)         $errors[] = 'Full name is required.';
if (!$phone)        $errors[] = 'Phone number is required.';
if (!$city)         $errors[] = 'City is required.';
if (!$district)     $errors[] = 'District is required.';
if (empty($items))  $errors[] = 'Your cart is empty.';
if (!$email)        $errors[] = 'Email address is required.';
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (!$paymentMethod) $errors[] = 'Payment method is required.';
if ($paymentMethod && !in_array($paymentMethod, $enabledMethodKeys, true)) $errors[] = 'Selected payment method is not available.';
if ($paymentMethod === 'payhere' && (!$payhereMerchantId || !$payhereMerchantSecret)) $errors[] = 'PayHere is not configured.';
if ($paymentMethod === 'koko' && (!$kokoMerchantId || !$kokoApiKey || !$kokoPrivateKey || !$kokoPublicKey)) $errors[] = 'KOKO is not configured.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// Sanitise items & calculate totals 
$cleanItems = [];
$subtotal   = 0.0;

// Fallback weight map from DB for any cart item that has product ID but no weight in payload.
$missingWeightIds = [];
$missingWeightNames = [];
$productMetaById = [];
$productMetaByName = [];
foreach ($items as $item) {
    $pid = (int)($item['productId'] ?? $item['product_id'] ?? 0);
    $w   = (float)($item['weight_kg'] ?? $item['weight'] ?? 0);
    if ($pid > 0) $missingWeightIds[$pid] = true;
    $itemName = trim((string)($item['name'] ?? ''));
    if ($itemName !== '') $missingWeightNames[$itemName] = true;
    if ($pid > 0 && $w <= 0) $missingWeightIds[$pid] = true;
}
$dbWeightMap = [];
if ($missingWeightIds) {
    $ids = array_keys($missingWeightIds);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $stmtW = getDB()->prepare("SELECT id, weight_kg, free_delivery FROM products WHERE id IN ($ph)");
    $stmtW->execute($ids);
    foreach ($stmtW->fetchAll() as $row) {
        $dbWeightMap[(int)$row['id']] = max(0.0, (float)$row['weight_kg']);
        $productMetaById[(int)$row['id']] = ['free_delivery' => (int)($row['free_delivery'] ?? 0)];
    }
}
$dbWeightByName = [];
if ($missingWeightNames) {
    $names = array_keys($missingWeightNames);
    $phn = implode(',', array_fill(0, count($names), '?'));
    $stmtN = getDB()->prepare("SELECT name, weight_kg, free_delivery FROM products WHERE name IN ($phn)");
    $stmtN->execute($names);
    foreach ($stmtN->fetchAll() as $row) {
        $dbWeightByName[(string)$row['name']] = max(0.0, (float)$row['weight_kg']);
        $productMetaByName[(string)$row['name']] = ['free_delivery' => (int)($row['free_delivery'] ?? 0)];
    }
}

foreach ($items as $item) {
    $qty   = max(1, min(100, (int)($item['qty']   ?? 1)));
    $price = abs((float)($item['price'] ?? 0));
    $pid = isset($item['productId']) ? (int)$item['productId'] : (isset($item['product_id']) ? (int)$item['product_id'] : null);
    $itemName = trim((string)($item['name'] ?? 'Unknown'));
    $weightKg = max(0.0, (float)($item['weight_kg'] ?? $item['weight'] ?? 0));
    $freeDelivery = !empty($item['free_delivery']);
    if ($weightKg <= 0 && $pid && isset($dbWeightMap[$pid])) $weightKg = $dbWeightMap[$pid];
    if ($weightKg <= 0 && isset($dbWeightByName[$itemName])) $weightKg = $dbWeightByName[$itemName];
    if (!$freeDelivery && $pid && !empty($productMetaById[$pid]['free_delivery'])) $freeDelivery = true;
    if (!$freeDelivery && !empty($productMetaByName[$itemName]['free_delivery'])) $freeDelivery = true;

    $cleanItems[] = [
        'product_id'=> $pid,
        'name'     => htmlspecialchars(strip_tags($itemName)),
        'category' => htmlspecialchars(strip_tags(trim($item['category'] ?? ''))),
        'price'    => $price,
        'weight_kg'=> $weightKg,
        'free_delivery' => $freeDelivery ? 1 : 0,
        'qty'      => $qty,
    ];
    $subtotal += $price * $qty;
}

$deliveryMeta = calculateDeliveryFee($district, $cleanItems, $subtotal);
$delivery = (float)$deliveryMeta['delivery_fee'];
$handlingMeta = calculatePaymentHandlingFee($subtotal + $delivery, $paymentMethod);
$handlingFee = (float)$handlingMeta['amount'];
$total    = $subtotal + $delivery + $handlingFee;

$fullAddress = $address;
if ($city || $district) {
    $fullAddress .= "\nCity: " . $city . "\nDistrict: " . $district;
}

$fullNotes = $notes;
if ($phoneAlt) {
    $fullNotes = ($fullNotes ? $fullNotes . "\n" : '') . 'Alternate Phone: ' . $phoneAlt;
}

$paymentMeta = [];
if ($paymentMethod === 'bank_transfer') {
    $paymentMeta = [
        'bank_name' => getSetting('pm_bank_name', ''),
        'account_name' => getSetting('pm_bank_account_name', ''),
        'account_number' => getSetting('pm_bank_account_number', ''),
        'branch' => getSetting('pm_bank_branch', ''),
        'instructions' => getSetting('pm_bank_instructions', ''),
    ];
}
if ($paymentMethod === 'payhere') {
    $paymentMeta = [
        'merchant_id' => getSetting('pm_payhere_merchant_id', ''),
        'sandbox' => getSetting('pm_payhere_sandbox', '1') === '1' ? 1 : 0,
        'handling_fee_percent' => $handlingMeta['percent'],
    ];
}
if ($paymentMethod === 'koko') {
    $paymentMeta = [
        'merchant_id' => $kokoMerchantId,
        'sandbox' => getSetting('pm_koko_sandbox', '1') === '1' ? 1 : 0,
        'plugin_name' => getSetting('pm_koko_plugin_name', 'customapi'),
        'plugin_version' => getSetting('pm_koko_plugin_version', '1.0.1'),
        'handling_fee_percent' => $handlingMeta['percent'],
    ];
}

// Save order 
$db          = getDB();
$orderNumber = generateOrderNumber();

try {
    $db->prepare(
        'INSERT INTO orders
         (order_number, customer_name, customer_phone, customer_phone_alt, customer_email, customer_city, customer_district, customer_address,
          items_json, subtotal, delivery_charge, handling_fee, total, notes, source, payment_method, payment_status, payment_meta, status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        $orderNumber,
        htmlspecialchars($name),
        htmlspecialchars($phone),
        $phoneAlt !== '' ? htmlspecialchars($phoneAlt) : null,
        $email ?: null,
        htmlspecialchars($city),
        htmlspecialchars($district),
        htmlspecialchars($fullAddress),
        json_encode($cleanItems, JSON_UNESCAPED_UNICODE),
        $subtotal,
        $delivery,
        $handlingFee,
        $total,
        htmlspecialchars($fullNotes),
        'website',
        $paymentMethod,
        $paymentMethod === 'cod' ? 'pending' : 'awaiting_payment',
        $paymentMeta ? json_encode($paymentMeta, JSON_UNESCAPED_UNICODE) : null,
        'pending',
    ]);

    // Upsert customer record (unique on phone)
    $db->prepare(
        'INSERT INTO customers (name, phone, email)
         VALUES (?,?,?)
         ON DUPLICATE KEY UPDATE
           name  = VALUES(name),
           email = COALESCE(VALUES(email), email)'
    )->execute([
        htmlspecialchars($name),
        htmlspecialchars($phone),
        $email ?: null,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save your order. Please try again.']);
    exit;
}

// Send emails 
$order = [
    'order_number'     => $orderNumber,
    'customer_name'    => $name,
    'customer_phone'   => $phone,
    'customer_phone_alt' => $phoneAlt,
    'customer_email'   => $email,
    'customer_address' => $fullAddress,
    'customer_city'    => $city,
    'customer_district'=> $district,
    'payment_method'   => $paymentMethod,
    'payment_meta'     => $paymentMeta,
    'delivery_meta'    => $deliveryMeta,
    'subtotal'         => $subtotal,
    'delivery_charge'  => $delivery,
    'handling_fee'     => $handlingFee,
    'handling_fee_percent' => $handlingMeta['percent'],
    'total'            => $total,
    'notes'            => $fullNotes,
    'created_at'       => date('Y-m-d H:i:s'),
];

// Customer confirmation
if ($email) {
    $html = emailOrderConfirmation($order, $cleanItems);
    sendMail($email, $name, 'Order Confirmed – ' . $orderNumber . ' | GADGET HUB', $html);
}

// Admin notification
$adminEmail = getSetting('admin_notify_email') ?: getSetting('store_email');
if ($adminEmail) {
    $html = emailAdminNewOrder($order, $cleanItems);
    sendMail($adminEmail, 'GADGET HUB Admin', 'New Order: ' . $orderNumber . ' from ' . $name, $html);
}

$response = [
    'success'      => true,
    'order_number' => $orderNumber,
    'handling_fee' => $handlingFee,
    'total'        => $total,
];

if ($paymentMethod === 'bank_transfer') {
    $bankTransferLines = [];
    if (!empty($paymentMeta['bank_name'])) $bankTransferLines[] = 'Bank: ' . $paymentMeta['bank_name'];
    if (!empty($paymentMeta['account_name'])) $bankTransferLines[] = 'Account Name: ' . $paymentMeta['account_name'];
    if (!empty($paymentMeta['account_number'])) $bankTransferLines[] = 'Account No: ' . $paymentMeta['account_number'];
    if (!empty($paymentMeta['branch'])) $bankTransferLines[] = 'Branch: ' . $paymentMeta['branch'];
    $bankInstructions = trim(getSetting('pm_bank_instructions', ''));
    if ($bankInstructions !== '') $bankTransferLines[] = $bankInstructions;
    $response['bank_transfer_details'] = $bankTransferLines;
}

if ($paymentMethod === 'payhere') {
    $merchantId = $payhereMerchantId;
    $merchantSecret = $payhereMerchantSecret;
    $isSandbox = getSetting('pm_payhere_sandbox', '1') === '1';
    $currency = strtoupper(trim(getSetting('currency_code', 'LKR') ?: 'LKR'));

    $amount = number_format((float)$total, 2, '.', '');
    $secretHash = strtoupper(md5($merchantSecret));
    $hash = strtoupper(md5($merchantId . $orderNumber . $amount . $currency . $secretHash));

    $response['payhere'] = [
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
            'first_name' => $name,
            'last_name' => '',
            'email' => $email ?: 'no-reply@example.com',
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'country' => 'Sri Lanka',
            'hash' => $hash,
        ],
    ];
}

if ($paymentMethod === 'koko') {
    $isSandbox = getSetting('pm_koko_sandbox', '1') === '1';
    $currency = strtoupper(trim(getSetting('currency_code', 'LKR') ?: 'LKR'));
    $pluginName = trim(getSetting('pm_koko_plugin_name', 'customapi')) ?: 'customapi';
    $pluginVersion = trim(getSetting('pm_koko_plugin_version', '1.0.1')) ?: '1.0.1';
    $gatewayUrl = $isSandbox
        ? 'https://qaapi.paykoko.com/api/merchants/orderCreate'
        : 'https://prodapi.paykoko.com/api/merchants/orderCreate';

    $amount = number_format((float)$total, 2, '.', '');
    $nameParts = preg_split('/\s+/', $name) ?: [];
    $firstName = $nameParts[0] ?? 'Customer';
    $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '.';
    $reference = $orderNumber;
    $returnUrl = BASE_URL . 'koko-return.php?order=' . urlencode($orderNumber);
    $cancelUrl = BASE_URL . 'koko-cancel.php?order=' . urlencode($orderNumber);
    $responseUrl = BASE_URL . 'api/koko-notify.php';
    $description = count($cleanItems) . ' product' . (count($cleanItems) === 1 ? '' : 's');

    $dataString = $kokoMerchantId .
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
        ($email ?: 'no-reply@example.com') .
        $description .
        $kokoApiKey .
        $responseUrl;

    try {
        $signature = signKokoPayload($dataString, $kokoPrivateKey);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not prepare KOKO payment request.']);
        exit;
    }

    $response['payment_redirect'] = [
        'gateway' => 'koko',
        'url' => $gatewayUrl,
        'fields' => [
            '_mId' => $kokoMerchantId,
            'api_key' => $kokoApiKey,
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
            '_email' => $email ?: 'no-reply@example.com',
            '_description' => $description,
            '_mobileNo' => preg_replace('/\D+/', '', $phone),
            'dataString' => $dataString,
            'signature' => $signature,
        ],
    ];
}

echo json_encode($response);
