<?php
/**
 * DG SPORTS - Application Bootstrap
 * Developer: DiziPortal.Com
 * Initialize the application
 */

// Define application constants
define('DG_SPORTS_APP', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH);
define('CONFIG_PATH', ROOT_PATH . DS . 'config');
define('CLASSES_PATH', ROOT_PATH . DS . 'classes');
define('INCLUDES_PATH', ROOT_PATH . DS . 'includes');
define('UPLOADS_PATH', ROOT_PATH . DS . 'uploads');
define('LOGS_PATH', ROOT_PATH . DS . 'logs');

// Set error reporting
if (file_exists(ROOT_PATH . DS . '.env')) {
    $env_content = file_get_contents(ROOT_PATH . DS . '.env');
    $env_lines = explode("\n", $env_content);
    foreach ($env_lines as $line) {
        if (trim($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
        }
    }
}

// Set timezone
date_default_timezone_set('Europe/Istanbul');

// Start session with secure settings
if (!session_id()) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . DS . 'php_errors.log');

// Create required directories
$required_dirs = [UPLOADS_PATH, LOGS_PATH];
foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Autoloader
spl_autoload_register(function ($class) {
    $file = CLASSES_PATH . DS . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Load core functions
require_once INCLUDES_PATH . DS . 'functions.php';

// Load configuration
$config = [];
$config_files = ['app', 'database'];
foreach ($config_files as $config_file) {
    $config_path = CONFIG_PATH . DS . $config_file . '.php';
    if (file_exists($config_path)) {
        $config[$config_file] = require $config_path;
    }
}

// Initialize application
try {
    // Initialize database
    Database::getInstance($config['database'] ?? []);
    
    // Initialize cache
    Cache::getInstance();
    
    // Initialize security
    Security::initialize();
    
    // Check maintenance mode
    if (($config['app']['features']['maintenance_mode'] ?? false) && !Admin::isLoggedIn()) {
        http_response_code(503);
        include ROOT_PATH . DS . 'maintenance.php';
        exit;
    }
    
} catch (Exception $e) {
    // Log error
    error_log('Bootstrap Error: ' . $e->getMessage());
    
    // Show friendly error page
    if ($_ENV['APP_DEBUG'] ?? false) {
        die('Bootstrap Error: ' . $e->getMessage());
    } else {
        http_response_code(500);
        include ROOT_PATH . DS . 'error.php';
        exit;
    }
}

// Set global configuration
$GLOBALS['config'] = $config;