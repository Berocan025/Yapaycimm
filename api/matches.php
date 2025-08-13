<?php
// diziportal - matches API
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
    foreach ($pdo->query('SELECT id, team_a_name, team_a_logo, team_b_name, team_b_logo, stadium, match_time, stream_url, is_active FROM matches ORDER BY match_time DESC, id DESC') as $r) {
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
    $a = sanitize_text($data['team_a_name'] ?? '');
    $al = trim($data['team_a_logo'] ?? '');
    $b = sanitize_text($data['team_b_name'] ?? '');
    $bl = trim($data['team_b_logo'] ?? '');
    $st = sanitize_text($data['stadium'] ?? '');
    $time = trim($data['match_time'] ?? '');
    $stream = trim($data['stream_url'] ?? '');
    $active = (int)($data['is_active'] ?? 1);
    if ($a === '' || $b === '' || $time === '') {
        json_response(['error' => 'Eksik alanlar'], 400);
    }
    $now = now_utc();
    $stmt = $pdo->prepare('INSERT INTO matches (team_a_name, team_a_logo, team_b_name, team_b_logo, stadium, match_time, stream_url, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$a, $al, $b, $bl, $st, $time, $stream, $active, $now, $now]);
    json_response(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
}

if ($method === 'PUT' || $method === 'PATCH') {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) json_response(['error' => 'ID gerekli'], 400);
    $a = sanitize_text($data['team_a_name'] ?? '');
    $al = trim($data['team_a_logo'] ?? '');
    $b = sanitize_text($data['team_b_name'] ?? '');
    $bl = trim($data['team_b_logo'] ?? '');
    $st = sanitize_text($data['stadium'] ?? '');
    $time = trim($data['match_time'] ?? '');
    $stream = trim($data['stream_url'] ?? '');
    $active = (int)($data['is_active'] ?? 1);
    $stmt = $pdo->prepare('UPDATE matches SET team_a_name=?, team_a_logo=?, team_b_name=?, team_b_logo=?, stadium=?, match_time=?, stream_url=?, is_active=?, updated_at=? WHERE id=?');
    $stmt->execute([$a, $al, $b, $bl, $st, $time, $stream, $active, now_utc(), $id]);
    json_response(['ok' => true]);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $data['id'] ?? 0);
    if ($id <= 0) json_response(['error' => 'ID gerekli'], 400);
    $pdo->prepare('DELETE FROM matches WHERE id=?')->execute([$id]);
    json_response(['ok' => true]);
}

json_response(['error' => 'Unsupported'], 405);