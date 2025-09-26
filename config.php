<?php
// Basic configuration for database and app paths

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'university_it_library');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'University IT Library');

// Absolute path to uploads directory
define('UPLOAD_DIR', __DIR__ . '/uploads');

// Create uploads directory if missing
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0775, true);
}

// Base URL detection (best-effort). Adjust if deploying under subfolder.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(str_replace(basename($scriptName), '', $scriptName), '/');
define('BASE_URL', $scheme . '://' . $host . $basePath);

// Allowed upload MIME types (no images). Primary: PDF; allow common docs.
define('ALLOWED_MIME_TYPES', json_encode([
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain'
]));

// Max file size ~ 25MB
define('MAX_UPLOAD_BYTES', 25 * 1024 * 1024);

?>
