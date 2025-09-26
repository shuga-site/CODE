<?php
require_once __DIR__ . '/../app/db.php';
header('Content-Type: application/json');
$pdo = get_pdo();
$stmt = $pdo->query('SELECT f.id, f.title, f.level, s.name as subject_name FROM files f INNER JOIN subjects s ON s.id=f.subject_id ORDER BY f.created_at DESC LIMIT 8');
echo json_encode($stmt->fetchAll());

