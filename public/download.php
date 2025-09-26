<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); echo 'File not found'; exit; }

$stmt = $pdo->prepare('SELECT * FROM files WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) { http_response_code(404); echo 'File not found'; exit; }

$path = $UPLOAD_PATH . '/' . $file['stored_name'];
if (!is_file($path)) { http_response_code(404); echo 'File missing on server'; exit; }

$pdo->prepare('UPDATE files SET download_count = download_count + 1 WHERE id = ?')->execute([$id]);

header('Content-Description: File Transfer');
header('Content-Type: ' . $file['mime_type']);
header('Content-Disposition: attachment; filename="' . rawurlencode($file['original_name']) . '"');
header('Content-Length: ' . (string)filesize($path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');
readfile($path);
exit;
