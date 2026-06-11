<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true) ?: [];
$id   = (int)($data['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid ID']); exit; }

$db = getDB();
ensureBrandsTable();

$stmt = $db->prepare('SELECT is_active FROM brands WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found']); exit; }

$new = ((int)$row['is_active'] === 1) ? 0 : 1;
$db->prepare('UPDATE brands SET is_active = ? WHERE id = ?')->execute([$new, $id]);

echo json_encode(['success'=>true, 'is_active'=>$new]);