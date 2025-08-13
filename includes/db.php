<?php

// diziportal - PDO connection helper
// Developer: DiziPortal.Com

function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $CONFIG = require __DIR__ . '/config.php';
    $driver = $CONFIG['db']['driver'];

    try {
        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . $CONFIG['db']['sqlite_path'];
            $pdo = new PDO($dsn);
        } else {
            $mysql = $CONFIG['db']['mysql'];
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $mysql['host'], $mysql['port'], $mysql['name'], $mysql['charset']
            );
            $pdo = new PDO($dsn, $mysql['user'], $mysql['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            // Ensure collation
            $pdo->exec('SET NAMES ' . $mysql['charset'] . ' COLLATE ' . $mysql['collation']);
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Throwable $e) {
        http_response_code(500);
        echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
        exit;
    }
}