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
ensureProductWeightColumn();
ensureProductFreeDeliveryColumn();
ensureDeliveryRatesTable();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request format.']);
    exit;
}

$district = trim((string)($data['district'] ?? ''));
$items = $data['cart'] ?? [];
if (!$district || !is_array($items) || !$items) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'District and cart are required.']);
    exit;
}

$db = getDB();
$missingWeightIds = [];
$missingWeightNames = [];
$productMetaById = [];
$productMetaByName = [];
foreach ($items as $item) {
    $pid = (int)($item['productId'] ?? $item['product_id'] ?? 0);
    $w = (float)($item['weight_kg'] ?? $item['weight'] ?? 0);
    if ($pid > 0) $missingWeightIds[$pid] = true;
    $name = trim((string)($item['name'] ?? ''));
    if ($name !== '') $missingWeightNames[$name] = true;
    if ($w > 0) continue;
}

$dbWeightById = [];
if ($missingWeightIds) {
    $ids = array_keys($missingWeightIds);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $st = $db->prepare("SELECT id, weight_kg, free_delivery FROM products WHERE id IN ($ph)");
    $st->execute($ids);
    foreach ($st->fetchAll() as $r) {
        $dbWeightById[(int)$r['id']] = max(0.0, (float)$r['weight_kg']);
        $productMetaById[(int)$r['id']] = ['free_delivery' => (int)($r['free_delivery'] ?? 0)];
    }
}

$dbWeightByName = [];
if ($missingWeightNames) {
    $names = array_keys($missingWeightNames);
    $ph = implode(',', array_fill(0, count($names), '?'));
    $st = $db->prepare("SELECT name, weight_kg, free_delivery FROM products WHERE name IN ($ph)");
    $st->execute($names);
    foreach ($st->fetchAll() as $r) {
        $dbWeightByName[(string)$r['name']] = max(0.0, (float)$r['weight_kg']);
        $productMetaByName[(string)$r['name']] = ['free_delivery' => (int)($r['free_delivery'] ?? 0)];
    }
}

$cleanItems = [];
$subtotal = 0.0;
foreach ($items as $item) {
    $qty = max(1, min(100, (int)($item['qty'] ?? 1)));
    $price = abs((float)($item['price'] ?? 0));
    $pid = (int)($item['productId'] ?? $item['product_id'] ?? 0);
    $name = trim((string)($item['name'] ?? 'Unknown'));
    $weightKg = max(0.0, (float)($item['weight_kg'] ?? $item['weight'] ?? 0));
    $freeDelivery = !empty($item['free_delivery']);
    if ($weightKg <= 0 && $pid > 0 && isset($dbWeightById[$pid])) $weightKg = $dbWeightById[$pid];
    if ($weightKg <= 0 && isset($dbWeightByName[$name])) $weightKg = $dbWeightByName[$name];
    if (!$freeDelivery && $pid > 0 && !empty($productMetaById[$pid]['free_delivery'])) $freeDelivery = true;
    if (!$freeDelivery && !empty($productMetaByName[$name]['free_delivery'])) $freeDelivery = true;

    $cleanItems[] = [
        'product_id' => $pid ?: null,
        'name' => $name,
        'price' => $price,
        'qty' => $qty,
        'weight_kg' => $weightKg,
        'free_delivery' => $freeDelivery ? 1 : 0,
    ];
    $subtotal += $price * $qty;
}

$meta = calculateDeliveryFee($district, $cleanItems, $subtotal);
echo json_encode([
    'success' => true,
    'subtotal' => round($subtotal, 2),
    'delivery_fee' => (float)$meta['delivery_fee'],
    'chargeable_kg' => (int)$meta['chargeable_kg'],
    'total_weight_kg' => (float)$meta['total_weight_kg'],
    'total' => round($subtotal + (float)$meta['delivery_fee'], 2),
    'has_rate' => (int)$meta['has_rate'],
]);
