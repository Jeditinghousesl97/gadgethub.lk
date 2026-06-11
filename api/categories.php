<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$db   = getDB();
$rows = $db->query('
    SELECT id, name, slug, icon
    FROM categories
    WHERE is_active = 1
    ORDER BY sort_order ASC, name ASC
')->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
