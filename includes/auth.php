<?php

// diziportal - admin authentication
// Developer: DiziPortal.Com

require_once __DIR__ . '/utils.php';

function start_session_if_needed(): void {
    $cfg = app_config();
    if (session_status() === PHP_SESSION_NONE) {
        session_name($cfg['security']['session_name']);
        session_start();
    }
}

function csrf_token(): string {
    start_session_if_needed();
    $cfg = app_config();
    if (empty($_SESSION[$cfg['security']['csrf_key']])) {
        $_SESSION[$cfg['security']['csrf_key']] = bin2hex(random_bytes(16));
    }
    return $_SESSION[$cfg['security']['csrf_key']];
}

function verify_csrf(string $token): bool {
    start_session_if_needed();
    $cfg = app_config();
    return hash_equals($_SESSION[$cfg['security']['csrf_key']] ?? '', $token);
}

function is_admin_logged_in(): bool {
    start_session_if_needed();
    return !empty($_SESSION['admin_user_id']);
}

function require_admin(): void {
    if (!is_admin_logged_in()) {
        http_response_code(401);
        json_response(['error' => 'Unauthorized']);
    }
}