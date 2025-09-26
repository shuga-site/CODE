<?php
// Copy this file to env.php and set your credentials
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'university_it_library',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'http://localhost',
        'upload_dir' => __DIR__ . '/../storage/uploads',
        'max_upload_mb' => 25,
        'allowed_mime_types' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ],
    ],
];
