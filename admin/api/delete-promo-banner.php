<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

$id = (int)(json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'Invalid ID']); exit; }

$db = getDB();
ensurePromoBannersTable();

$stmt = $db->prepare('SELECT image_path FROM promo_banners WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { echo json_encode(['success' => false, 'error' => 'Banner not found']); exit; }

$path = $row['image_path'] ?? '';
if ($path && str_starts_with($path, 'uploads/') && file_exists(ROOT_PATH . $path)) @unlink(ROOT_PATH . $path);

$db->prepare('DELETE FROM promo_banners WHERE id = ?')->execute([$id]);
echo json_encode(['success' => true, 'message' => 'Banner deleted']);
