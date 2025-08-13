<?php

// diziportal - DG SPORTS site configuration
// Developer: DiziPortal.Com

// Toggle to 'mysql' on cPanel hosting and fill credentials below
$DB_DRIVER = getenv('DB_DRIVER') ?: 'sqlite';

$CONFIG = [
    'app_name' => 'DG SPORTS',
    'developer_name' => 'DiziPortal.Com',
    'base_url' => getenv('APP_BASE_URL') ?: '', // Optional; auto-detected if empty
    'db' => [
        'driver' => $DB_DRIVER,
        'sqlite_path' => __DIR__ . '/../storage/database.sqlite',
        'mysql' => [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'name' => getenv('DB_NAME') ?: 'dgsports',
            'user' => getenv('DB_USER') ?: 'dgsports_user',
            'pass' => getenv('DB_PASS') ?: 'change-me',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    'security' => [
        'session_name' => 'dgsports_session',
        'password_algo' => PASSWORD_DEFAULT,
        'csrf_key' => 'dgsports_csrf',
    ],
    'uploads' => [
        'path' => __DIR__ . '/../public/uploads',
        'base_url' => '/uploads',
        'max_bytes' => 5 * 1024 * 1024,
        'allowed_mime' => [
            'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'
        ],
    ],
];

// Attempt to create SQLite file if using sqlite
if ($CONFIG['db']['driver'] === 'sqlite') {
    $sqliteDir = dirname($CONFIG['db']['sqlite_path']);
    if (!is_dir($sqliteDir)) {
        @mkdir($sqliteDir, 0775, true);
    }
    if (!file_exists($CONFIG['db']['sqlite_path'])) {
        @touch($CONFIG['db']['sqlite_path']);
        @chmod($CONFIG['db']['sqlite_path'], 0664);
    }
}

return $CONFIG;