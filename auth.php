<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_current_user_record(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    if (!is_logged_in()) return false;
    $user = get_current_user_record();
    return $user && $user['role'] === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied';
        exit;
    }
}

function handle_login(string $email, string $password): bool {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function handle_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
?>
