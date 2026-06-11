<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

$id = (int)(json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'Invalid ID']); exit; }

$db = getDB();
ensureHeroSlidesTable();

$stmt = $db->prepare('SELECT desktop_image, mobile_image FROM hero_slides WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { echo json_encode(['success' => false, 'error' => 'Slide not found']); exit; }

foreach (['desktop_image', 'mobile_image'] as $k) {
    $p = $row[$k] ?? '';
    if ($p && str_starts_with($p, 'uploads/') && file_exists(ROOT_PATH . $p)) @unlink(ROOT_PATH . $p);
}

$db->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([$id]);
echo json_encode(['success' => true, 'message' => 'Slide deleted']);