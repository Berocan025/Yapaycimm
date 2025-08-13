<?php
// diziportal - admin login API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/init_db.php';
allow_cors();
start_session_if_needed();

$data = $_POST + read_json_input();
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$token = $data['csrf'] ?? '';
if (!verify_csrf($token)) {
    json_response(['error' => 'CSRF'], 400);
}

$pdo = get_pdo();
$stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(['error' => 'Geçersiz bilgiler'], 401);
}

$_SESSION['admin_user_id'] = (int)$user['id'];
json_response(['ok' => true]);