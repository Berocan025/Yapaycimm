<?php
/**
 * DG SPORTS - Core Functions
 * Developer: DiziPortal.Com
 * Helper functions for the application
 */

// Security check
if (!defined('DG_SPORTS_APP')) {
    die('Direct access forbidden');
}

/**
 * Get configuration value
 */
function config($key, $default = null) {
    $keys = explode('.', $key);
    $value = $GLOBALS['config'] ?? [];
    
    foreach ($keys as $k) {
        if (is_array($value) && isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return $value;
}

/**
 * Escape HTML entities
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get asset URL
 */
function asset($path) {
    return rtrim(config('app.url', ''), '/') . '/' . ltrim($path, '/');
}

/**
 * Generate CSRF token
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function csrf_verify($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Get current URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Format file size
 */
function format_bytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $base = log($size, 1024);
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
}

/**
 * Format number with K/M suffix
 */
function format_number($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return number_format($num);
}

/**
 * Time ago format
 */
function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'şimdi';
    if ($time < 3600) return floor($time/60) . ' dakika önce';
    if ($time < 86400) return floor($time/3600) . ' saat önce';
    if ($time < 2592000) return floor($time/86400) . ' gün önce';
    if ($time < 31536000) return floor($time/2592000) . ' ay önce';
    
    return floor($time/31536000) . ' yıl önce';
}

/**
 * Generate random string
 */
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Clean string for slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return empty($text) ? 'n-a' : $text;
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
               'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get user agent
 */
function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Detect device type
 */
function get_device_type() {
    $userAgent = get_user_agent();
    
    if (preg_match('/tablet|ipad/i', $userAgent)) {
        return 'tablet';
    } elseif (preg_match('/mobile|android|iphone/i', $userAgent)) {
        return 'mobile';
    } elseif (preg_match('/smart-tv|smarttv|googletv|appletv|hbbtv|pov_tv|netcast.tv/i', $userAgent)) {
        return 'smart_tv';
    } else {
        return 'desktop';
    }
}

/**
 * Log activity
 */
function log_activity($message, $level = 'info', $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = get_client_ip();
    $userAgent = get_user_agent();
    
    $logEntry = [
        'timestamp' => $timestamp,
        'level' => $level,
        'message' => $message,
        'ip' => $ip,
        'user_agent' => $userAgent,
        'context' => $context
    ];
    
    $logFile = LOGS_PATH . DS . 'activity_' . date('Y-m-d') . '.log';
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Send JSON response
 */
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validate required fields
 */
function validate_required($data, $required_fields) {
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = ucfirst($field) . ' alanı zorunludur';
        }
    }
    
    return $errors;
}

/**
 * Upload file securely
 */
function upload_file($file, $allowed_types = null, $max_size = null) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception('Geçersiz dosya yüklemesi');
    }
    
    $allowed_types = $allowed_types ?? config('app.allowed_image_types', ['jpg', 'jpeg', 'png', 'gif']);
    $max_size = $max_size ?? config('app.max_upload_size', 5242880); // 5MB
    
    // Check file size
    if ($file['size'] > $max_size) {
        throw new Exception('Dosya boyutu çok büyük');
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Desteklenmeyen dosya türü');
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = UPLOADS_PATH . DS . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Dosya yüklenemedi');
    }
    
    return '/uploads/' . $filename;
}

/**
 * Render template
 */
function render_template($template, $data = []) {
    extract($data);
    
    ob_start();
    $template_path = ROOT_PATH . DS . 'templates' . DS . $template . '.php';
    
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        throw new Exception("Template not found: $template");
    }
    
    return ob_get_clean();
}

/**
 * Check if request is AJAX
 */
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Rate limiting check
 */
function check_rate_limit($key, $limit = 60, $window = 3600) {
    $cache_key = 'rate_limit_' . $key;
    $attempts = Cache::get($cache_key, 0);
    
    if ($attempts >= $limit) {
        return false;
    }
    
    Cache::set($cache_key, $attempts + 1, $window);
    return true;
}