<?php
/**
 * DG SPORTS - Database Test Script
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 DG SPORTS Database Test</h2>";

// Load .env if exists
if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    $env_lines = explode("\n", $env_content);
    foreach ($env_lines as $line) {
        if (trim($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
        }
    }
    echo "✅ .env file loaded<br>";
} else {
    echo "❌ .env file not found<br>";
}

// Database connection test
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'dg_sports';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    echo "<br><strong>Database Config:</strong><br>";
    echo "Host: $host:$port<br>";
    echo "Database: $database<br>";
    echo "Username: $username<br>";
    
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<br>✅ Database connection successful<br>";
    
    // Check tables
    $tables = [
        'admins', 'matches', 'channels', 'settings', 
        'viewer_logs', 'login_logs', 'cache'
    ];
    
    echo "<br><strong>Table Check:</strong><br>";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $result = $stmt->fetch();
            echo "✅ Table '$table' exists (Records: {$result['count']})<br>";
        } catch (PDOException $e) {
            echo "❌ Table '$table' missing or error: " . $e->getMessage() . "<br>";
        }
    }
    
    // Check admin user
    try {
        $stmt = $pdo->query("SELECT username, email, role FROM admins LIMIT 1");
        $admin = $stmt->fetch();
        if ($admin) {
            echo "<br>✅ Admin user found: {$admin['username']} ({$admin['role']})<br>";
        } else {
            echo "<br>❌ No admin user found<br>";
        }
    } catch (PDOException $e) {
        echo "<br>❌ Admin check failed: " . $e->getMessage() . "<br>";
    }
    
    // Check views
    echo "<br><strong>View Check:</strong><br>";
    $views = ['v_live_matches', 'v_featured_content'];
    foreach ($views as $view) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$view`");
            $result = $stmt->fetch();
            echo "✅ View '$view' exists (Records: {$result['count']})<br>";
        } catch (PDOException $e) {
            echo "❌ View '$view' missing: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (PDOException $e) {
    echo "<br>❌ Database connection failed: " . $e->getMessage() . "<br>";
}

echo "<br><strong>PHP Info:</strong><br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Extensions: PDO=" . (extension_loaded('pdo') ? 'Yes' : 'No') . 
     ", PDO_MySQL=" . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";

echo "<br><a href='index.php'>Test Main Page</a> | <a href='admin/'>Test Admin</a> | <a href='install.php'>Re-install</a>";
?>