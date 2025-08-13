<?php
// diziportal - viewer ping API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/init_db.php';
allow_cors();

$pdo = get_pdo();
$itemType = $_POST['item_type'] ?? $_GET['item_type'] ?? '';
$itemId = (int)($_POST['item_id'] ?? $_GET['item_id'] ?? 0);

if (!in_array($itemType, ['channel', 'match'], true) || $itemId <= 0) {
    json_response(['error' => 'Bad request'], 400);
}

// clean old sessions >10 minutes
$pdo->prepare('DELETE FROM viewer_sessions WHERE last_seen < ?')->execute([gmdate('Y-m-d H:i:s', time() - 600)]);

$sessionId = substr(hash('sha256', client_ip() . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . session_id()), 0, 40);
$now = now_utc();

// upsert
try {
    $stmt = $pdo->prepare('INSERT INTO viewer_sessions (item_type, item_id, session_id, ip, user_agent, last_seen) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$itemType, $itemId, $sessionId, client_ip(), $_SERVER['HTTP_USER_AGENT'] ?? '', $now]);
} catch (Throwable $e) {
    $stmt = $pdo->prepare('UPDATE viewer_sessions SET last_seen = ? WHERE item_type = ? AND item_id = ? AND session_id = ?');
    $stmt->execute([$now, $itemType, $itemId, $sessionId]);
}

// current count last 2 minutes
$threshold = gmdate('Y-m-d H:i:s', time() - 120);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM viewer_sessions WHERE item_type = ? AND item_id = ? AND last_seen >= ?');
$stmt->execute([$itemType, $itemId, $threshold]);
$count = (int)$stmt->fetchColumn();

json_response(['ok' => true, 'viewers' => $count]);