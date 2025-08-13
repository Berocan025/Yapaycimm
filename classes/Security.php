<?php
/**
 * DG SPORTS - Security Class
 * Developer: DiziPortal.Com
 * Security and authentication system
 */

if (!defined('DG_SPORTS_APP')) {
    die('Direct access forbidden');
}

class Security {
    
    public static function initialize() {
        // Set security headers
        self::setSecurityHeaders();
        
        // Start rate limiting
        self::initRateLimiting();
        
        // Clean old sessions
        self::cleanOldSessions();
    }
    
    /**
     * Set security headers
     */
    private static function setSecurityHeaders() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            if (isset($_SERVER['HTTPS'])) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
    }
    
    /**
     * Initialize rate limiting
     */
    private static function initRateLimiting() {
        $ip = get_client_ip();
        $key = 'rate_limit_' . $ip;
        
        if (!check_rate_limit($key, config('app.api_rate_limit', 60), 3600)) {
            http_response_code(429);
            json_response(['error' => 'Too many requests'], 429);
        }
    }
    
    /**
     * Clean old sessions
     */
    private static function cleanOldSessions() {
        // Clean expired sessions periodically
        if (rand(1, 100) <= 5) { // 5% chance
            $sessionPath = session_save_path();
            if ($sessionPath && is_dir($sessionPath)) {
                $files = glob($sessionPath . DS . 'sess_*');
                foreach ($files as $file) {
                    if (filemtime($file) < time() - 3600) { // 1 hour old
                        unlink($file);
                    }
                }
            }
        }
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Sanitize input
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token) {
        return csrf_verify($token);
    }
    
    /**
     * Check if IP is blocked
     */
    public static function isIPBlocked($ip) {
        $blockedIPs = Cache::get('blocked_ips', []);
        return in_array($ip, $blockedIPs);
    }
    
    /**
     * Block IP address
     */
    public static function blockIP($ip, $duration = 3600) {
        $blockedIPs = Cache::get('blocked_ips', []);
        $blockedIPs[] = $ip;
        Cache::set('blocked_ips', array_unique($blockedIPs), $duration);
        
        log_activity("IP blocked: {$ip}", 'warning', ['duration' => $duration]);
    }
    
    /**
     * Check for SQL injection patterns
     */
    public static function detectSQLInjection($input) {
        $patterns = [
            '/(union|select|insert|update|delete|drop|create|alter|exec|execute)/i',
            '/(\-\-|\#|\/\*|\*\/)/i',
            '/(script|javascript|vbscript|onload|onerror|onclick)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for XSS patterns
     */
    public static function detectXSS($input) {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/si',
            '/<iframe[^>]*>.*?<\/iframe>/si',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Invalid file upload');
        }
        
        // Check file size
        $maxSize = config('app.max_upload_size', 5242880); // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('File too large');
        }
        
        // Check file type
        $allowedTypes = config('app.allowed_image_types', ['jpg', 'jpeg', 'png', 'gif']);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }
        
        // Check MIME type
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!isset($allowedMimes[$fileExtension]) || 
            $mimeType !== $allowedMimes[$fileExtension]) {
            throw new Exception('Invalid file format');
        }
        
        return true;
    }
    
    /**
     * Encrypt data
     */
    public static function encrypt($data, $key = null) {
        $key = $key ?? config('app.key');
        $cipher = config('app.cipher', 'AES-256-CBC');
        
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     */
    public static function decrypt($encryptedData, $key = null) {
        $key = $key ?? config('app.key');
        $cipher = config('app.cipher', 'AES-256-CBC');
        
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($cipher);
        
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    }
    
    /**
     * Generate API key
     */
    public static function generateApiKey() {
        return 'dgs_' . self::generateToken(40);
    }
    
    /**
     * Validate API key
     */
    public static function validateApiKey($apiKey) {
        // Check format
        if (!preg_match('/^dgs_[a-f0-9]{80}$/', $apiKey)) {
            return false;
        }
        
        // Check in database (implement as needed)
        // This is a placeholder - in real implementation, check against database
        return true;
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $level = 'warning', $context = []) {
        $logData = array_merge($context, [
            'ip' => get_client_ip(),
            'user_agent' => get_user_agent(),
            'url' => current_url(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        log_activity("Security Event: {$event}", $level, $logData);
    }
    
    /**
     * Check for brute force attempts
     */
    public static function checkBruteForce($identifier, $maxAttempts = 5, $timeWindow = 900) {
        $key = "brute_force_{$identifier}";
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Record failed login attempt
     */
    public static function recordFailedAttempt($identifier) {
        $key = "brute_force_{$identifier}";
        $attempts = Cache::get($key, 0);
        Cache::set($key, $attempts + 1, 900); // 15 minutes
        
        if ($attempts >= 4) { // Will be 5 after increment
            self::logSecurityEvent("Brute force detected for {$identifier}");
            self::blockIP(get_client_ip(), 3600); // Block for 1 hour
        }
    }
    
    /**
     * Clear failed attempts
     */
    public static function clearFailedAttempts($identifier) {
        $key = "brute_force_{$identifier}";
        Cache::delete($key);
    }
    
    /**
     * Generate secure session ID
     */
    public static function generateSecureSessionId() {
        return session_create_id(bin2hex(random_bytes(16)));
    }
    
    /**
     * Regenerate session ID securely
     */
    public static function regenerateSession() {
        session_regenerate_id(true);
    }
}