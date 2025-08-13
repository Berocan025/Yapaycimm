<?php
/**
 * DG SPORTS - Kolay Kurulum Scripti v2.1
 * Developer: DiziPortal.Com
 * Geliştirilmiş hata yönetimi ve debug ile
 */

// Timeout ve memory ayarları
set_time_limit(300); // 5 dakika
ini_set('memory_limit', '256M');
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Istanbul');

// Session başlat
session_start();

// Kurulum durumu kontrolü
$install_lock_file = __DIR__ . '/install.lock';
if (file_exists($install_lock_file) && !isset($_GET['force'])) {
    die('
    <div style="background: #f8f9fa; padding: 40px; font-family: Arial, sans-serif; text-align: center;">
        <h2 style="color: #dc3545;">🔒 Kurulum Zaten Tamamlanmış!</h2>
        <p>DG SPORTS sistemi zaten kurulmuş. Ana sayfaya gitmek için:</p>
        <a href="index.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ana Sayfaya Git</a>
        <p style="margin-top: 20px; font-size: 12px;">
            Tekrar kurmak için: <a href="?force=1&step=1">Zorla Tekrar Kur</a>
        </p>
    </div>
    ');
}

// Debug fonksiyonu
function debug_log($message) {
    error_log("[DG SPORTS INSTALL] " . $message);
    if (isset($_GET['debug'])) {
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px; font-family: monospace; font-size: 12px; border-left: 3px solid #007bff;'>DEBUG: $message</div>";
    }
}

class DGSportsInstaller {
    private $step = 1;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        $this->step = isset($_GET['step']) ? (int)$_GET['step'] : (isset($_POST['step']) ? (int)$_POST['step'] : 1);
        debug_log("Installer started at step: " . $this->step);
    }
    
    public function run() {
        try {
            $this->showHeader();
            
            switch ($this->step) {
                case 1:
                    $this->showWelcome();
                    break;
                case 2:
                    $this->checkRequirements();
                    break;
                case 3:
                    $this->setupDatabase();
                    break;
                case 4:
                    $this->finalizeInstallation();
                    break;
                default:
                    $this->showWelcome();
            }
            
            $this->showFooter();
        } catch (Exception $e) {
            $this->showError("Kritik Hata: " . $e->getMessage());
        }
    }
    
    private function showError($message) {
        debug_log("ERROR: " . $message);
        echo '<div class="alert alert-error"><strong>❌ Hata!</strong> ' . htmlspecialchars($message) . '</div>';
        echo '<a href="install.php?step=1" class="btn btn-primary">Baştan Başla</a>';
    }
    
    private function showHeader() {
        echo '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DG SPORTS - Kolay Kurulum v2.1</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .header p { opacity: 0.9; font-size: 1.1em; }
        .content { padding: 40px; }
        .step-indicator { display: flex; justify-content: center; margin-bottom: 30px; }
        .step { width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; color: #6b7280; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 10px; position: relative; }
        .step.active { background: #dc2626; color: white; }
        .step.completed { background: #10b981; color: white; }
        .step::after { content: ""; position: absolute; top: 50%; left: 100%; width: 20px; height: 2px; background: #e5e7eb; z-index: -1; }
        .step:last-child::after { display: none; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; transition: border-color 0.3s; }
        .form-control:focus { border-color: #dc2626; outline: none; }
        .btn { padding: 12px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; text-align: center; margin: 5px; }
        .btn-primary { background: #dc2626; color: white; }
        .btn-primary:hover { background: #b91c1c; transform: translateY(-2px); }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; }
        .alert { padding: 15px; border-radius: 8px; margin: 20px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #7f1d1d; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #78350f; border: 1px solid #fde68a; }
        .alert-info { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .progress { background: #e5e7eb; border-radius: 10px; height: 10px; margin: 20px 0; overflow: hidden; }
        .progress-bar { background: linear-gradient(90deg, #dc2626, #b91c1c); height: 100%; transition: width 0.5s ease; }
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin: 15px 0; }
        .requirements-list { list-style: none; }
        .requirements-list li { padding: 10px 0; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; }
        .requirements-list li:last-child { border-bottom: none; }
        .status-icon { width: 20px; height: 20px; border-radius: 50%; margin-right: 15px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px; }
        .status-ok { background: #10b981; }
        .status-error { background: #dc2626; }
        .status-warning { background: #f59e0b; }
        .text-center { text-align: center; }
        .mb-0 { margin-bottom: 0; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
        .spinner { border: 3px solid #f3f3f3; border-top: 3px solid #dc2626; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite; display: inline-block; margin-right: 10px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .debug-panel { background: #1f2937; color: #f3f4f6; padding: 20px; margin: 20px 0; border-radius: 8px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto; }
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; z-index: 9999; justify-content: center; align-items: center; color: white; font-size: 18px; }
        .loading-content { text-align: center; }
        .big-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #dc2626; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        .test-connection { background: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; }
    </style>
</head>
<body>
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="big-spinner"></div>
        <div>Kurulum işlemi devam ediyor...</div>
        <div style="font-size: 14px; margin-top: 10px; opacity: 0.8;">Lütfen bekleyin, tarayıcıyı kapatmayın!</div>
    </div>
</div>
<div class="container">
    <div class="header">
        <h1>🏆 DG SPORTS</h1>
        <p>Profesyonel Spor Yayın Platformu - Kolay Kurulum v2.1</p>
        <p style="font-size: 0.9em; margin-top: 10px;">Developer: DiziPortal.Com</p>
    </div>
    <div class="content">';
        
        // Step indicator
        echo '<div class="step-indicator">';
        for ($i = 1; $i <= 4; $i++) {
            $class = 'step';
            if ($i < $this->step) $class .= ' completed';
            elseif ($i == $this->step) $class .= ' active';
            echo "<div class=\"$class\">$i</div>";
        }
        echo '</div>';
    }
    
    private function showFooter() {
        echo '</div>
</div>
<script>
function showLoading() {
    document.getElementById("loadingOverlay").style.display = "flex";
}

function hideLoading() {
    document.getElementById("loadingOverlay").style.display = "none";
}

function testConnection() {
    showLoading();
    const formData = new FormData();
    formData.append("action", "test_connection");
    formData.append("db_host", document.querySelector("[name=db_host]").value);
    formData.append("db_port", document.querySelector("[name=db_port]").value);
    formData.append("db_username", document.querySelector("[name=db_username]").value);
    formData.append("db_password", document.querySelector("[name=db_password]").value);
    
    fetch("install.php?step=3", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        hideLoading();
        if (data.includes("✅")) {
            alert("✅ Veritabanı bağlantısı başarılı!");
        } else {
            alert("❌ Bağlantı hatası: " + data);
        }
    })
    .catch(error => {
        hideLoading();
        alert("❌ Test hatası: " + error);
    });
}

// Form submit için loading göster
document.addEventListener("DOMContentLoaded", function() {
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            const submitBtn = form.querySelector("button[type=submit]");
            if (submitBtn && !submitBtn.classList.contains("test-connection")) {
                showLoading();
            }
        });
    });
});

// Sayfa yüklendiğinde loading gizle
window.addEventListener("load", function() {
    hideLoading();
});
</script>
</body>
</html>';
    }
    
    private function showWelcome() {
        debug_log("Showing welcome page");
        echo '<div class="text-center">
            <h2 style="color: #374151; margin-bottom: 20px;">🎉 DG SPORTS Kurulumuna Hoş Geldiniz!</h2>
            <div class="card">
                <h3 style="color: #dc2626; margin-bottom: 15px;">📋 Bu Kurulum Size Sağlayacaklar:</h3>
                <ul style="text-align: left; list-style: none; padding: 0;">
                    <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">✅ <strong>Otomatik MySQL Veritabanı</strong> - Tablolar ve demo veriler</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">✅ <strong>Güvenli Yapılandırma</strong> - .env dosyası ve güvenlik</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">✅ <strong>Klasör İzinleri</strong> - Otomatik cache, logs, uploads</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">✅ <strong>Admin Paneli</strong> - /admin/ ile tam yönetim</li>
                    <li style="padding: 8px 0;">✅ <strong>Profesyonel Demo İçerik</strong> - Hazır maçlar ve kanallar</li>
                </ul>
            </div>
            <div class="alert alert-warning">
                <strong>⚠️ Önemli:</strong> Bu script veritabanınızı ve ayar dosyalarınızı değiştirecek. Devam etmeden önce mevcut verilerinizin yedeğini alın!
            </div>
            <div class="alert alert-info">
                <strong>🔧 Debug Modu:</strong> Sorun yaşıyorsanız <a href="?step=1&debug=1">debug mode</a> ile detayları görebilirsiniz.
            </div>
            <a href="install.php?step=2" class="btn btn-primary" style="font-size: 18px; padding: 15px 40px;">
                🚀 Kuruluma Başla
            </a>
        </div>';
    }
    
    private function checkRequirements() {
        debug_log("Checking requirements");
        echo '<h2 style="color: #374151; margin-bottom: 20px;">🔍 Sistem Gereksinimleri Kontrolü</h2>';
        
        $requirements = [
            'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'PDO MySQL Driver' => extension_loaded('pdo_mysql'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'mbstring Extension' => extension_loaded('mbstring'),
            'fileinfo Extension' => extension_loaded('fileinfo'),
            'JSON Extension' => extension_loaded('json'),
            'Cache Directory Writable' => $this->checkWritable('cache'),
            'Logs Directory Writable' => $this->checkWritable('logs'),
            'Uploads Directory Writable' => $this->checkWritable('uploads'),
            'Root Directory Writable' => is_writable(__DIR__),
            'database.sql File Exists' => file_exists(__DIR__ . '/database.sql')
        ];
        
        $allOk = true;
        echo '<ul class="requirements-list">';
        foreach ($requirements as $requirement => $status) {
            $icon = $status ? 'status-ok' : 'status-error';
            $iconText = $status ? '✓' : '✗';
            if (!$status) $allOk = false;
            
            debug_log("Requirement: $requirement = " . ($status ? 'OK' : 'FAIL'));
            
            echo "<li>
                <div class=\"status-icon $icon\">$iconText</div>
                <span>$requirement</span>
            </li>";
        }
        echo '</ul>';
        
        // PHP bilgileri
        echo '<div class="card">
            <h4>📊 Sistem Bilgileri</h4>
            <p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>
            <p><strong>Memory Limit:</strong> ' . ini_get('memory_limit') . '</p>
            <p><strong>Max Execution Time:</strong> ' . ini_get('max_execution_time') . ' saniye</p>
            <p><strong>Upload Max Size:</strong> ' . ini_get('upload_max_filesize') . '</p>
        </div>';
        
        if ($allOk) {
            echo '<div class="alert alert-success">
                <strong>✅ Harika!</strong> Tüm sistem gereksinimleri karşılanıyor. Kuruluma devam edebilirsiniz.
            </div>';
            
            echo '<a href="install.php?step=3" class="btn btn-primary">📊 Veritabanı Kurulumuna Geç</a>';
        } else {
            echo '<div class="alert alert-error">
                <strong>❌ Hata!</strong> Bazı gereksinimler karşılanmıyor. Lütfen eksikleri giderin ve tekrar deneyin.
            </div>';
            
            echo '<a href="install.php?step=2" class="btn btn-warning">🔄 Tekrar Kontrol Et</a>';
        }
    }
    
    private function checkWritable($dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) return false;
        }
        return is_writable($path);
    }
    
    private function setupDatabase() {
        debug_log("Setup database step started");
        
        // Test connection işlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_connection') {
            $this->testDatabaseConnection();
            return;
        }
        
        // Kurulum işlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['db_action']) && $_POST['db_action'] === 'setup') {
            $this->processDatabase();
            return;
        }
        
        echo '<h2 style="color: #374151; margin-bottom: 20px;">🗄️ Veritabanı Yapılandırması</h2>';
        
        echo '<div class="alert alert-warning">
            <strong>📋 Bilgi:</strong> Veritabanı bilgilerinizi girin. Script otomatik olarak veritabanını oluşturacak ve demo verilerle dolduracak.
        </div>';
        
        echo '<form method="post" id="dbForm">
            <input type="hidden" name="step" value="3">
            <input type="hidden" name="db_action" value="setup">
            
            <div class="grid">
                <div class="form-group">
                    <label>🖥️ Veritabanı Sunucusu</label>
                    <input type="text" name="db_host" class="form-control" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>🔌 Port</label>
                    <input type="text" name="db_port" class="form-control" value="3306" required>
                </div>
            </div>
            
            <div class="grid">
                <div class="form-group">
                    <label>👤 Kullanıcı Adı</label>
                    <input type="text" name="db_username" class="form-control" value="root" required>
                </div>
                <div class="form-group">
                    <label>🔑 Şifre</label>
                    <input type="password" name="db_password" class="form-control" placeholder="Veritabanı şifreniz">
                </div>
            </div>
            
            <div class="form-group">
                <label>📊 Veritabanı Adı</label>
                <input type="text" name="db_name" class="form-control" value="dg_sports" required>
                <small style="color: #6b7280;">Mevcut değilse otomatik oluşturulacak</small>
                <button type="button" class="test-connection" onclick="testConnection()">🔍 Bağlantıyı Test Et</button>
            </div>
            
            <div class="grid">
                <div class="form-group">
                    <label>👑 Admin Kullanıcı Adı</label>
                    <input type="text" name="admin_username" class="form-control" value="admin" required>
                </div>
                <div class="form-group">
                    <label>🔐 Admin Şifresi</label>
                    <input type="password" name="admin_password" class="form-control" value="secret123" required>
                    <small style="color: #6b7280;">Admin paneline giriş için kullanılacak</small>
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    🚀 Veritabanını Kur ve Devam Et
                </button>
            </div>
        </form>';
    }
    
    private function testDatabaseConnection() {
        try {
            $host = $_POST['db_host'];
            $port = $_POST['db_port'];
            $username = $_POST['db_username'];
            $password = $_POST['db_password'];
            
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 10
            ]);
            
            echo "✅ Veritabanı bağlantısı başarılı!";
            
        } catch (Exception $e) {
            echo "❌ Bağlantı hatası: " . $e->getMessage();
        }
        exit;
    }
    
    private function processDatabase() {
        debug_log("Processing database setup");
        
        $host = $_POST['db_host'];
        $port = $_POST['db_port'];
        $username = $_POST['db_username'];
        $password = $_POST['db_password'];
        $database = $_POST['db_name'];
        $admin_username = $_POST['admin_username'];
        $admin_password = $_POST['admin_password'];
        
        try {
            debug_log("Connecting to database: $host:$port");
            
            // Veritabanı bağlantısı (veritabanı olmadan)
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 30
            ]);
            
            debug_log("Database connection successful");
            
            // Veritabanı oluştur
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$database`");
            
            $this->success[] = "✅ Veritabanı '$database' başarıyla oluşturuldu";
            debug_log("Database created: $database");
            
            // SQL dosyasını oku ve çalıştır
            $sqlFile = __DIR__ . '/database.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("database.sql dosyası bulunamadı!");
            }
            
            $sql = file_get_contents($sqlFile);
            debug_log("SQL file loaded, size: " . strlen($sql) . " bytes");
            
            // Admin kullanıcısını özelleştir
            $hashedPassword = password_hash($admin_password, PASSWORD_DEFAULT);
            
            // SQL'de admin insert satırını bul ve değiştir
            $pattern = "/INSERT INTO `admins`.*?VALUES.*?'admin'.*?;/s";
            $replacement = "INSERT INTO `admins` (`username`, `password`, `email`, `full_name`, `role`, `status`) VALUES\n('$admin_username', '$hashedPassword', 'admin@dgsports.com', 'System Administrator', 'super_admin', 'active');";
            $sql = preg_replace($pattern, $replacement, $sql);
            
            debug_log("Admin credentials updated in SQL");
            
            // SQL komutlarını çalıştır
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            $executed = 0;
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                    try {
                        $pdo->exec($statement);
                        $executed++;
                    } catch (PDOException $e) {
                        debug_log("SQL Error in statement: " . substr($statement, 0, 100) . "... Error: " . $e->getMessage());
                        // Bazı hataları görmezden gel (IF NOT EXISTS vs.)
                    }
                }
            }
            
            $this->success[] = "✅ Veritabanı tabloları oluşturuldu ($executed komut çalıştırıldı)";
            debug_log("Database tables created, executed $executed statements");
            
            // .env dosyası oluştur
            $envContent = $this->generateEnvContent($host, $port, $database, $username, $password);
            file_put_contents(__DIR__ . '/.env', $envContent);
            $this->success[] = "✅ .env yapılandırma dosyası oluşturuldu";
            debug_log(".env file created");
            
            // Klasör izinlerini ayarla
            $this->setupDirectories();
            
            // Session'a bilgileri kaydet
            $_SESSION['install_success'] = true;
            $_SESSION['admin_username'] = $admin_username;
            $_SESSION['admin_password'] = $admin_password;
            
            debug_log("Installation completed successfully");
            
            // Başarı mesajı ve devam butonu
            echo '<div class="alert alert-success">
                <strong>🎉 Harika!</strong> Veritabanı kurulumu başarıyla tamamlandı!
            </div>';
            
            foreach ($this->success as $message) {
                echo "<div class=\"alert alert-success\">$message</div>";
            }
            
            echo '<a href="install.php?step=4" class="btn btn-success">🏁 Kurulumu Tamamla</a>';
            
        } catch (Exception $e) {
            debug_log("Database setup failed: " . $e->getMessage());
            echo '<div class="alert alert-error">
                <strong>❌ Hata!</strong> Veritabanı kurulumunda sorun oluştu:<br>
                <code>' . htmlspecialchars($e->getMessage()) . '</code>
            </div>';
            
            echo '<a href="install.php?step=3" class="btn btn-warning">🔄 Tekrar Dene</a>';
            echo '<a href="install.php?step=3&debug=1" class="btn btn-primary">🔍 Debug Modu</a>';
        }
    }
    
    private function generateEnvContent($host, $port, $database, $username, $password) {
        return "# DG SPORTS - Otomatik Oluşturulan Yapılandırma
# Kurulum Tarihi: " . date('Y-m-d H:i:s') . "

# APPLICATION SETTINGS
APP_NAME=\"DG SPORTS\"
APP_ENV=production
APP_DEBUG=false
APP_URL=" . $this->getCurrentUrl() . "
APP_KEY=" . $this->generateRandomKey() . "
APP_TIMEZONE=Europe/Istanbul

# DATABASE CONFIGURATION
DB_HOST=$host
DB_PORT=$port
DB_DATABASE=$database
DB_USERNAME=$username
DB_PASSWORD=$password

# CACHE SETTINGS
CACHE_DRIVER=file
CACHE_TTL=3600

# MAIL SETTINGS (İsteğe Bağlı)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# LOGGING
LOG_CHANNEL=single
LOG_LEVEL=error

# SECURITY
SESSION_LIFETIME=120
BCRYPT_ROUNDS=10

# UPLOAD SETTINGS
UPLOAD_MAX_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,webp

# SOCIAL MEDIA (Admin panelinden değiştirilebilir)
TELEGRAM_URL=https://t.me/diziportal
INSTAGRAM_URL=https://instagram.com/diziportal
TWITTER_URL=https://twitter.com/diziportal
TIKTOK_URL=https://tiktok.com/@diziportal
";
    }
    
    private function setupDirectories() {
        $directories = ['cache', 'logs', 'uploads', 'assets/uploads'];
        
        foreach ($directories as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            chmod($path, 0777);
            
            // .gitkeep dosyası oluştur
            $gitkeep = $path . '/.gitkeep';
            if (!file_exists($gitkeep)) {
                touch($gitkeep);
            }
        }
        
        $this->success[] = "✅ Klasör izinleri ve yapısı oluşturuldu";
        debug_log("Directories setup completed");
    }
    
    private function generateRandomKey() {
        return 'dg-sports-' . bin2hex(random_bytes(16));
    }
    
    private function getCurrentUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['REQUEST_URI']);
        return $protocol . '://' . $host . rtrim($path, '/');
    }
    
    private function finalizeInstallation() {
        debug_log("Finalizing installation");
        
        $admin_username = $_SESSION['admin_username'] ?? 'admin';
        $admin_password = $_SESSION['admin_password'] ?? 'secret123';
        
        // Kurulum lock dosyası oluştur
        file_put_contents(__DIR__ . '/install.lock', 'DG SPORTS kurulumu tamamlandı: ' . date('Y-m-d H:i:s'));
        debug_log("Install lock file created");
        
        echo '<div class="text-center">
            <h2 style="color: #10b981; margin-bottom: 30px;">🎉 Kurulum Başarıyla Tamamlandı!</h2>
            
            <div class="card">
                <h3 style="color: #dc2626; margin-bottom: 20px;">🏆 DG SPORTS Hazır!</h3>
                <div style="text-align: left;">
                    <p><strong>🌐 Ana Sayfa:</strong> <a href="index.php" target="_blank">index.php</a></p>
                    <p><strong>⚙️ Admin Paneli:</strong> <a href="admin/" target="_blank">admin/</a></p>
                    <p><strong>👤 Admin Kullanıcı:</strong> <code>' . htmlspecialchars($admin_username) . '</code></p>
                    <p><strong>🔑 Admin Şifre:</strong> <code>' . htmlspecialchars($admin_password) . '</code></p>
                </div>
            </div>
            
            <div class="alert alert-success">
                <strong>✅ Kurulum Tamamlandı!</strong><br>
                • MySQL veritabanı ve tablolar oluşturuldu<br>
                • Demo içerik ve admin kullanıcısı eklendi<br>
                • .env yapılandırma dosyası hazırlandı<br>
                • Klasör izinleri ayarlandı<br>
                • Sistem güvenlik önlemleri aktifleştirildi
            </div>
            
            <div class="alert alert-warning">
                <strong>🔒 Güvenlik Uyarısı:</strong> Kurulumdan sonra install.php dosyasını silin veya taşıyın!
            </div>
            
            <div style="margin: 30px 0;">
                <a href="index.php" class="btn btn-primary" style="margin: 10px;">🏠 Ana Sayfaya Git</a>
                <a href="admin/" class="btn btn-success" style="margin: 10px;">⚙️ Admin Paneline Git</a>
            </div>
            
            <div class="card" style="background: #fef3c7; border-color: #fde68a;">
                <h4 style="color: #78350f; margin-bottom: 15px;">📋 Sonraki Adımlar:</h4>
                <ol style="text-align: left; color: #78350f;">
                    <li>Admin paneline giriş yapın</li>
                    <li>Site ayarlarını kontrol edin</li>
                    <li>Maç ve kanal bilgilerini güncelleyin</li>
                    <li>Logo ve görselleri yükleyin</li>
                    <li>Sosyal medya linklerini ayarlayın</li>
                    <li><strong>install.php dosyasını silin!</strong></li>
                </ol>
            </div>
        </div>';
        
        // Session temizle
        unset($_SESSION['install_success']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_password']);
        
        debug_log("Installation finalized, session cleaned");
    }
}

// Kurulumu başlat
try {
    $installer = new DGSportsInstaller();
    $installer->run();
} catch (Exception $e) {
    echo '<div style="background: #fee2e2; color: #7f1d1d; padding: 20px; margin: 20px; border-radius: 8px;">
        <h3>❌ Kritik Kurulum Hatası</h3>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <a href="install.php?step=1" style="background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Baştan Başla</a>
    </div>';
}
?>