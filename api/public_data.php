<?php
// diziportal - public data API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/init_db.php';
allow_cors();

$pdo = get_pdo();

// settings
$settings = [];
foreach ($pdo->query('SELECT id, value FROM settings') as $row) {
    $settings[$row['id']] = $row['value'];
}

// viewer counts last 2 minutes
$threshold = gmdate('Y-m-d H:i:s', time() - 120);
$viewerCounts = [
    'channel' => [],
    'match' => [],
];
$stmt = $pdo->prepare('SELECT item_type, item_id, COUNT(*) as c FROM viewer_sessions WHERE last_seen >= ? GROUP BY item_type, item_id');
$stmt->execute([$threshold]);
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $viewerCounts[$r['item_type']][$r['item_id']] = (int)$r['c'];
}

// channels
$channels = [];
foreach ($pdo->query("SELECT id, name, logo_url, stream_url FROM channels WHERE is_active = 1 ORDER BY id DESC") as $c) {
    $cid = (int)$c['id'];
    $channels[] = [
        'id' => $cid,
        'name' => $c['name'],
        'logo_url' => $c['logo_url'],
        'stream_url' => $c['stream_url'],
        'viewers' => $viewerCounts['channel'][$cid] ?? 0,
    ];
}

// matches
$matches = [];
foreach ($pdo->query("SELECT id, team_a_name, team_a_logo, team_b_name, team_b_logo, stadium, match_time, stream_url FROM matches WHERE is_active = 1 ORDER BY match_time ASC") as $m) {
    $mid = (int)$m['id'];
    $matches[] = [
        'id' => $mid,
        'team_a_name' => $m['team_a_name'],
        'team_a_logo' => $m['team_a_logo'],
        'team_b_name' => $m['team_b_name'],
        'team_b_logo' => $m['team_b_logo'],
        'stadium' => $m['stadium'],
        'match_time' => $m['match_time'],
        'stream_url' => $m['stream_url'],
        'viewers' => $viewerCounts['match'][$mid] ?? 0,
    ];
}

json_response([
    'settings' => $settings,
    'channels' => $channels,
    'matches' => $matches,
]);