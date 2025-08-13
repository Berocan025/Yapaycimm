<?php
// diziportal - channels API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/init_db.php';
allow_cors();

$pdo = get_pdo();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = [];
    foreach ($pdo->query('SELECT id, name, logo_url, stream_url, is_active FROM channels ORDER BY id DESC') as $r) {
        $rows[] = $r;
    }
    json_response($rows);
}

require_admin();
$data = $_POST + read_json_input();
$token = $data['csrf'] ?? '';
if (!verify_csrf($token)) {
    json_response(['error' => 'CSRF'], 400);
}

if ($method === 'POST') {
    $name = sanitize_text($data['name'] ?? '');
    $logo = trim($data['logo_url'] ?? '');
    $stream = trim($data['stream_url'] ?? '');
    $active = (int)($data['is_active'] ?? 1);
    if ($name === '' || $stream === '') {
        json_response(['error' => 'Eksik alanlar'], 400);
    }
    $now = now_utc();
    $stmt = $pdo->prepare('INSERT INTO channels (name, logo_url, stream_url, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $logo, $stream, $active, $now, $now]);
    json_response(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
}

if ($method === 'PUT' || $method === 'PATCH') {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) json_response(['error' => 'ID gerekli'], 400);
    $name = sanitize_text($data['name'] ?? '');
    $logo = trim($data['logo_url'] ?? '');
    $stream = trim($data['stream_url'] ?? '');
    $active = (int)($data['is_active'] ?? 1);
    $stmt = $pdo->prepare('UPDATE channels SET name=?, logo_url=?, stream_url=?, is_active=?, updated_at=? WHERE id=?');
    $stmt->execute([$name, $logo, $stream, $active, now_utc(), $id]);
    json_response(['ok' => true]);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $data['id'] ?? 0);
    if ($id <= 0) json_response(['error' => 'ID gerekli'], 400);
    $pdo->prepare('DELETE FROM channels WHERE id=?')->execute([$id]);
    json_response(['ok' => true]);
}

json_response(['error' => 'Unsupported'], 405);