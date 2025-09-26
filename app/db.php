<?php

function load_config(): array {
    $configPath = __DIR__ . '/../config/env.php';
    if (!file_exists($configPath)) {
        $configPath = __DIR__ . '/../config/env.example.php';
    }
    /** @var array $config */
    $config = require $configPath;
    return $config;
}

function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $config = load_config();
    $db = $config['db'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['database'], $db['charset']);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $db['username'], $db['password'], $options);
    return $pdo;
}

function ensure_upload_dir(): void {
    $config = load_config();
    $dir = $config['app']['upload_dir'];
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

