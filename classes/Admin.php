<?php
/**
 * DG SPORTS - Admin Model
 * Developer: DiziPortal.Com
 * Admin authentication and management
 */

if (!defined('DG_SPORTS_APP')) {
    die('Direct access forbidden');
}

class Admin {
    private static $db;
    
    public static function init() {
        self::$db = Database::getInstance();
    }
    
    /**
     * Authenticate admin user
     */
    public static function authenticate($username, $password, $remember = false) {
        self::init();
        
        // Check brute force protection
        if (!Security::checkBruteForce($username)) {
            throw new Exception('Too many failed attempts. Please try again later.');
        }
        
        // Find admin user
        $admin = self::$db->selectOne(
            "SELECT * FROM admins WHERE username = :username AND status = 'active'",
            ['username' => $username]
        );
        
        if (!$admin) {
            Security::recordFailedAttempt($username);
            throw new Exception('Invalid credentials');
        }
        
        // Check if account is locked
        if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
            throw new Exception('Account is temporarily locked');
        }
        
        // Verify password
        if (!Security::verifyPassword($password, $admin['password'])) {
            // Update failed attempts
            self::$db->update('admins', 
                ['login_attempts' => $admin['login_attempts'] + 1],
                'id = :id',
                ['id' => $admin['id']]
            );
            
            // Lock account after 5 failed attempts
            if ($admin['login_attempts'] >= 4) {
                self::$db->update('admins',
                    ['locked_until' => date('Y-m-d H:i:s', time() + 900)], // 15 minutes
                    'id = :id',
                    ['id' => $admin['id']]
                );
            }
            
            Security::recordFailedAttempt($username);
            throw new Exception('Invalid credentials');
        }
        
        // Clear failed attempts and update last login
        self::$db->update('admins', [
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $admin['id']]);
        
        Security::clearFailedAttempts($username);
        
        // Log successful login
        self::logLogin($admin['id'], $username, true);
        
        // Set session
        self::setSession($admin, $remember);
        
        return $admin;
    }
    
    /**
     * Set admin session
     */
    private static function setSession($admin, $remember = false) {
        Security::regenerateSession();
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_ip'] = get_client_ip();
        
        if ($remember) {
            $token = Security::generateToken();
            $expires = time() + (7 * 24 * 60 * 60); // 7 days
            
            // Store remember token in database
            self::$db->update('admins', [
                'remember_token' => password_hash($token, PASSWORD_DEFAULT),
                'remember_expires' => date('Y-m-d H:i:s', $expires)
            ], 'id = :id', ['id' => $admin['id']]);
            
            // Set remember cookie
            setcookie('admin_remember', $admin['id'] . ':' . $token, $expires, '/', '', true, true);
        }
    }
    
    /**
     * Check if admin is logged in
     */
    public static function isLoggedIn() {
        if (!isset($_SESSION['admin_id'])) {
            return self::checkRememberToken();
        }
        
        // Check session timeout
        $timeout = config('app.admin_session_timeout', 60) * 60; // Convert to seconds
        if (time() - $_SESSION['admin_login_time'] > $timeout) {
            self::logout();
            return false;
        }
        
        // Check IP change (optional security measure)
        if ($_SESSION['admin_ip'] !== get_client_ip()) {
            self::logout();
            return false;
        }
        
        return true;
    }
    
    /**
     * Check remember token
     */
    private static function checkRememberToken() {
        if (!isset($_COOKIE['admin_remember'])) {
            return false;
        }
        
        list($adminId, $token) = explode(':', $_COOKIE['admin_remember'], 2);
        
        self::init();
        $admin = self::$db->selectOne(
            "SELECT * FROM admins WHERE id = :id AND status = 'active' AND remember_expires > NOW()",
            ['id' => $adminId]
        );
        
        if (!$admin || !$admin['remember_token']) {
            setcookie('admin_remember', '', time() - 3600, '/');
            return false;
        }
        
        if (!Security::verifyPassword($token, $admin['remember_token'])) {
            setcookie('admin_remember', '', time() - 3600, '/');
            return false;
        }
        
        // Regenerate session
        self::setSession($admin, false);
        return true;
    }
    
    /**
     * Get current admin
     */
    public static function getCurrentAdmin() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        self::init();
        return self::$db->selectOne(
            "SELECT id, username, email, full_name, role, last_login FROM admins WHERE id = :id",
            ['id' => $_SESSION['admin_id']]
        );
    }
    
    /**
     * Logout admin
     */
    public static function logout() {
        // Clear remember token
        if (isset($_SESSION['admin_id'])) {
            self::init();
            self::$db->update('admins', [
                'remember_token' => null,
                'remember_expires' => null
            ], 'id = :id', ['id' => $_SESSION['admin_id']]);
        }
        
        // Clear session
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_login_time']);
        unset($_SESSION['admin_ip']);
        
        // Clear remember cookie
        setcookie('admin_remember', '', time() - 3600, '/');
        
        // Regenerate session ID
        Security::regenerateSession();
    }
    
    /**
     * Create new admin
     */
    public static function create($data) {
        self::init();
        
        // Validate required fields
        $required = ['username', 'password', 'email'];
        $errors = validate_required($data, $required);
        
        if (!empty($errors)) {
            throw new Exception('Missing required fields: ' . implode(', ', array_keys($errors)));
        }
        
        // Check if username exists
        $existing = self::$db->selectOne(
            "SELECT id FROM admins WHERE username = :username",
            ['username' => $data['username']]
        );
        
        if ($existing) {
            throw new Exception('Username already exists');
        }
        
        // Hash password
        $data['password'] = Security::hashPassword($data['password']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'active';
        $data['role'] = $data['role'] ?? 'admin';
        
        return self::$db->insert('admins', $data);
    }
    
    /**
     * Update admin
     */
    public static function update($id, $data) {
        self::init();
        
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Security::hashPassword($data['password']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return self::$db->update('admins', $data, 'id = :id', ['id' => $id]);
    }
    
    /**
     * Delete admin
     */
    public static function delete($id) {
        self::init();
        
        // Don't allow deleting the only super admin
        $superAdminCount = self::$db->selectOne(
            "SELECT COUNT(*) as count FROM admins WHERE role = 'super_admin' AND status = 'active'"
        )['count'];
        
        $admin = self::$db->selectOne("SELECT role FROM admins WHERE id = :id", ['id' => $id]);
        
        if ($admin['role'] === 'super_admin' && $superAdminCount <= 1) {
            throw new Exception('Cannot delete the only super admin');
        }
        
        return self::$db->delete('admins', 'id = :id', ['id' => $id]);
    }
    
    /**
     * Get all admins
     */
    public static function getAll($page = 1, $perPage = 20) {
        self::init();
        
        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at 
                FROM admins ORDER BY created_at DESC";
        
        return self::$db->paginate($sql, [], $page, $perPage);
    }
    
    /**
     * Log login attempt
     */
    private static function logLogin($adminId, $username, $success, $failureReason = null) {
        self::init();
        
        self::$db->insert('login_logs', [
            'admin_id' => $adminId,
            'username' => $username,
            'ip_address' => get_client_ip(),
            'user_agent' => get_user_agent(),
            'success' => $success ? 1 : 0,
            'failure_reason' => $failureReason,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get login logs
     */
    public static function getLoginLogs($page = 1, $perPage = 50) {
        self::init();
        
        $sql = "SELECT l.*, a.full_name 
                FROM login_logs l 
                LEFT JOIN admins a ON l.admin_id = a.id 
                ORDER BY l.created_at DESC";
        
        return self::$db->paginate($sql, [], $page, $perPage);
    }
    
    /**
     * Check admin permission
     */
    public static function hasPermission($permission) {
        $admin = self::getCurrentAdmin();
        if (!$admin) {
            return false;
        }
        
        // Super admin has all permissions
        if ($admin['role'] === 'super_admin') {
            return true;
        }
        
        // Define role permissions
        $permissions = [
            'admin' => ['view_dashboard', 'manage_matches', 'manage_channels', 'view_analytics'],
            'moderator' => ['view_dashboard', 'manage_matches', 'manage_channels']
        ];
        
        return in_array($permission, $permissions[$admin['role']] ?? []);
    }
    
    /**
     * Change password
     */
    public static function changePassword($currentPassword, $newPassword) {
        $admin = self::getCurrentAdmin();
        if (!$admin) {
            throw new Exception('Not authenticated');
        }
        
        self::init();
        $adminData = self::$db->selectOne(
            "SELECT password FROM admins WHERE id = :id",
            ['id' => $admin['id']]
        );
        
        if (!Security::verifyPassword($currentPassword, $adminData['password'])) {
            throw new Exception('Current password is incorrect');
        }
        
        if (strlen($newPassword) < config('app.password_min_length', 8)) {
            throw new Exception('Password is too short');
        }
        
        return self::update($admin['id'], ['password' => $newPassword]);
    }
    
    /**
     * Get dashboard statistics
     */
    public static function getDashboardStats() {
        self::init();
        
        $stats = [];
        
        // Total matches
        $stats['total_matches'] = self::$db->selectOne(
            "SELECT COUNT(*) as count FROM matches"
        )['count'];
        
        // Live matches
        $stats['live_matches'] = self::$db->selectOne(
            "SELECT COUNT(*) as count FROM matches WHERE status = 'live'"
        )['count'];
        
        // Total channels
        $stats['total_channels'] = self::$db->selectOne(
            "SELECT COUNT(*) as count FROM channels WHERE status = 'active'"
        )['count'];
        
        // Total viewers (sum of all current viewers)
        $stats['total_viewers'] = self::$db->selectOne(
            "SELECT (
                SELECT COALESCE(SUM(viewers), 0) FROM matches WHERE status = 'live'
            ) + (
                SELECT COALESCE(SUM(viewers), 0) FROM channels WHERE status = 'active'
            ) as total"
        )['total'];
        
        return $stats;
    }
}