<?php
require_once __DIR__ . '/../app/db.php';
$pdo = get_pdo();
$config = load_config();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(404); exit('Not found'); }

$stmt = $pdo->prepare('SELECT * FROM files WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) { http_response_code(404); exit('Not found'); }

$path = rtrim($config['app']['upload_dir'], '/').'/'.$file['stored_name'];
if (!is_file($path)) { http_response_code(404); exit('File missing'); }

$pdo->prepare('UPDATE files SET download_count = download_count + 1 WHERE id = ?')->execute([$id]);

header('Content-Description: File Transfer');
header('Content-Type: '.$file['mime_type']);
header('Content-Disposition: attachment; filename="'.basename($file['original_name']).'"');
header('Content-Length: '.$file['file_size']);
readfile($path);
exit;

