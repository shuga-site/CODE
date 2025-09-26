<?php
require_once __DIR__ . '/../app/db.php';
header('Content-Type: application/json');
$pdo = get_pdo();
$rows = $pdo->query('SELECT id, name FROM subjects ORDER BY name')->fetchAll();
echo json_encode($rows);

