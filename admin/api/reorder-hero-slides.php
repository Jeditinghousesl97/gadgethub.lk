<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true) ?: [];
$ids  = $data['ids'] ?? [];
if (!is_array($ids) || !$ids) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid payload']); exit; }

$db = getDB();
ensureHeroSlidesTable();

$db->beginTransaction();
try {
    $stmt = $db->prepare('UPDATE hero_slides SET sort_order = ? WHERE id = ?');
    foreach ($ids as $i => $id) {
      $stmt->execute([$i, (int)$id]);
    }
    $db->commit();
    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Could not reorder']);
}