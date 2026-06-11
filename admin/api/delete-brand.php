<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

$id = (int)(json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'Invalid ID']); exit; }

$db = getDB();
ensureBrandsTable();

$stmt = $db->prepare('SELECT logo_path FROM brands WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { echo json_encode(['success' => false, 'error' => 'Brand not found']); exit; }

$logo = $row['logo_path'] ?? '';
if ($logo && str_starts_with($logo, 'uploads/') && file_exists(ROOT_PATH . $logo)) {
    @unlink(ROOT_PATH . $logo);
}

$db->prepare('DELETE FROM brands WHERE id = ?')->execute([$id]);
echo json_encode(['success' => true, 'message' => 'Brand deleted']);