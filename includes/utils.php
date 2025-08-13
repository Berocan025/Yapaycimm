<?php

// diziportal - shared utilities
// Developer: DiziPortal.Com

function app_config(): array {
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/config.php';
    }
    return $cfg;
}

function base_url(string $path = ''): string {
    $cfg = app_config();
    if (!empty($cfg['base_url'])) {
        return rtrim($cfg['base_url'], '/') . '/' . ltrim($path, '/');
    }
    // Auto-detect from server vars
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    return $scheme . '://' . $host . ($base ? $base : '') . '/' . ltrim($path, '/');
}

function json_response($data, int $status = 200): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function allow_cors(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function read_json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function is_valid_url(string $url): bool {
    return (bool)filter_var($url, FILTER_VALIDATE_URL);
}

function sanitize_text(string $text): string {
    return trim(filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
}

function now_utc(): string {
    return gmdate('Y-m-d H:i:s');
}

function client_ip(): string {
    foreach ([
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = explode(',', $ip);
                return trim($parts[0]);
            }
            return $ip;
        }
    }
    return '0.0.0.0';
}