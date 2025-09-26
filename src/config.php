<?php
declare(strict_types=1);

// Core application bootstrap: sessions, DB connection, paths, helpers, schema, seed

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();
date_default_timezone_set('UTC');

$ROOT_PATH = dirname(__DIR__);
$PUBLIC_PATH = $ROOT_PATH . '/public';
$UPLOAD_PATH = $PUBLIC_PATH . '/uploads';
if (!is_dir($UPLOAD_PATH)) {
  @mkdir($UPLOAD_PATH, 0755, true);
}

// Adjust when deploying behind a subdirectory
$BASE_URL = '/';

// Database config (use env vars if available)
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_PORT = getenv('DB_PORT') ?: '3306';
$DB_NAME = getenv('DB_NAME') ?: 'university_it_library';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

try {
  $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
  exit;
}

// ---------- Helpers ----------
function e(string $value): string {
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
  header('Location: ' . $path);
  exit;
}

function isLoggedIn(): bool {
  return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function currentUser(): ?array {
  return $_SESSION['user'] ?? null;
}

function isAdmin(): bool {
  $user = currentUser();
  return $user !== null && ($user['role'] ?? '') === 'admin';
}

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_validate(?string $token): bool {
  return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function ensureDatabase(PDO $pdo): void {
  // Create users table
  $pdo->exec(
    'CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      full_name VARCHAR(120) NOT NULL,
      email VARCHAR(190) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      role ENUM("admin","user") NOT NULL DEFAULT "user",
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
  );

  // Create files table
  $pdo->exec(
    'CREATE TABLE IF NOT EXISTS files (
      id INT AUTO_INCREMENT PRIMARY KEY,
      title VARCHAR(255) NOT NULL,
      subject VARCHAR(160) NOT NULL,
      level ENUM("HND","Diploma","Bachelor") NOT NULL,
      year SMALLINT NULL,
      description TEXT NULL,
      original_name VARCHAR(255) NOT NULL,
      stored_name VARCHAR(255) NOT NULL,
      mime_type VARCHAR(120) NOT NULL,
      file_size BIGINT NOT NULL,
      download_count INT NOT NULL DEFAULT 0,
      uploaded_by INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_level (level),
      INDEX idx_subject (subject),
      INDEX idx_title (title),
      CONSTRAINT fk_files_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
  );
}

function seedAdminIfMissing(PDO $pdo): void {
  try {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn();
  } catch (Throwable $e) {
    // If the query fails, tables may not exist yet; create them
    ensureDatabase($pdo);
    $count = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn();
  }

  if ($count === 0) {
    $passwordHash = password_hash('Admin@123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, "admin")');
    $stmt->execute(['Administrator', 'admin@itlibrary.local', $passwordHash]);
  }
}

ensureDatabase($pdo);
seedAdminIfMissing($pdo);

// Upload constraints
$MAX_UPLOAD_BYTES = 50 * 1024 * 1024; // 50 MB
$ALLOWED_EXTENSIONS = [
  'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'
];
$DISALLOWED_MIME_PREFIXES = [
  'image/',
];

function isDisallowedMime(string $mime, array $disallowedPrefixes): bool {
  foreach ($disallowedPrefixes as $prefix) {
    if (stripos($mime, $prefix) === 0) {
      return true;
    }
  }
  return false;
}

function generateStoredFileName(string $originalName): string {
  $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
  $random = bin2hex(random_bytes(16));
  return $random . ($ext ? ('.' . $ext) : '');
}

?>
