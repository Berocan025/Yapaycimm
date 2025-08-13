<?php
// diziportal - settings API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/init_db.php';
allow_cors();

$pdo = get_pdo();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $out = [];
    foreach ($pdo->query('SELECT id, value FROM settings') as $row) {
        $out[$row['id']] = $row['value'];
    }
    json_response($out);
}

// write requires admin and csrf
require_admin();
$data = $_POST + read_json_input();
$token = $data['csrf'] ?? '';
if (!verify_csrf($token)) {
    json_response(['error' => 'CSRF'], 400);
}
unset($data['csrf']);

$stmtUp = $pdo->prepare('UPDATE settings SET value = ? WHERE id = ?');
$stmtInsSql = 'INSERT INTO settings (id, value) VALUES (?, ?)';
try { $pdo->beginTransaction(); } catch (Throwable $e) {}
foreach ($data as $k => $v) {
    $ok = $stmtUp->execute([(string)$v, (string)$k]);
    if ($stmtUp->rowCount() === 0) {
        try {
            $pdo->prepare($stmtInsSql)->execute([(string)$k, (string)$v]);
        } catch (Throwable $e) {}
    }
}
try { $pdo->commit(); } catch (Throwable $e) {}
json_response(['ok' => true]);