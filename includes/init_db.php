<?php

// diziportal - database schema bootstrap
// Developer: DiziPortal.Com

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

function bootstrap_database(): void {
    $pdo = get_pdo();

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $autoInc = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $tsType = $driver === 'sqlite' ? 'TEXT' : 'DATETIME';

    // settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id VARCHAR(64) PRIMARY KEY,
        value TEXT
    )");

    // admin users
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id $autoInc,
        username VARCHAR(64) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at $tsType,
        updated_at $tsType
    )");

    // channels
    $pdo->exec("CREATE TABLE IF NOT EXISTS channels (
        id $autoInc,
        name VARCHAR(120) NOT NULL,
        logo_url TEXT,
        stream_url TEXT NOT NULL,
        is_active TINYINT NOT NULL DEFAULT 1,
        created_at $tsType,
        updated_at $tsType
    )");

    // matches
    $pdo->exec("CREATE TABLE IF NOT EXISTS matches (
        id $autoInc,
        team_a_name VARCHAR(120) NOT NULL,
        team_a_logo TEXT,
        team_b_name VARCHAR(120) NOT NULL,
        team_b_logo TEXT,
        stadium VARCHAR(180),
        match_time $tsType NOT NULL,
        stream_url TEXT,
        is_active TINYINT NOT NULL DEFAULT 1,
        created_at $tsType,
        updated_at $tsType
    )");

    // viewer sessions for realtime counters
    $pdo->exec("CREATE TABLE IF NOT EXISTS viewer_sessions (
        id $autoInc,
        item_type VARCHAR(16) NOT NULL,
        item_id INT NOT NULL,
        session_id VARCHAR(64) NOT NULL,
        ip VARCHAR(64),
        user_agent TEXT,
        last_seen $tsType
    )");

    // indexes (compatible with sqlite/mysql)
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_view_item ON viewer_sessions (item_type, item_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_view_last ON viewer_sessions (last_seen)");

    // default admin user
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM admin_users');
    $stmt->execute();
    $count = (int)$stmt->fetchColumn();
    if ($count === 0) {
        $username = 'admin';
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $now = now_utc();
        $ins = $pdo->prepare('INSERT INTO admin_users (username, password_hash, created_at, updated_at) VALUES (?, ?, ?, ?)');
        $ins->execute([$username, $hash, $now, $now]);
    }

    // default settings
    $defaults = [
        'brand_name' => 'DG SPORTS',
        'player_logo_url' => '',
        'accent_color' => '#b31217',
        'accent_color_2' => '#3a0d11',
        'telegram_url' => '',
        'instagram_url' => '',
        'twitter_url' => '',
        'tiktok_url' => '',
        'hero_title' => 'Canlı Maçlar ve 7/24 Spor Kanalları',
        'hero_subtitle' => 'DG SPORTS ile her zaman, her yerde. Geliştirici: DiziPortal.Com',
    ];
    foreach ($defaults as $k => $v) {
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings (id, value) VALUES (?, ?)');
        try {
            $stmt->execute([$k, $v]);
        } catch (Throwable $e) {
            // For MySQL use INSERT IGNORE
            $stmt2 = $pdo->prepare('INSERT IGNORE INTO settings (id, value) VALUES (?, ?)');
            $stmt2->execute([$k, $v]);
        }
    }
}

bootstrap_database();