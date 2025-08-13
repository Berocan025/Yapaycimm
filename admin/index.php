<?php
/**
 * DG SPORTS - Admin Panel
 * Developer: DiziPortal.Com
 * Complete admin management system
 */

require_once '../includes/bootstrap.php';

// Handle logout
if (isset($_GET['logout'])) {
    Admin::logout();
    redirect('index.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    try {
        // Validate CSRF token
        if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token');
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            throw new Exception('Please fill in all fields');
        }
        
        $admin = Admin::authenticate($username, $password, $remember);
        
        log_activity('Admin login successful', 'info', ['admin_id' => $admin['id']]);
        redirect('index.php');
        
    } catch (Exception $e) {
        $loginError = $e->getMessage();
        log_activity('Admin login failed', 'warning', ['username' => $username ?? '', 'error' => $loginError]);
    }
}

// Check if admin is logged in
if (!Admin::isLoggedIn()) {
    include 'login.php';
    exit;
}

// Get current admin
$currentAdmin = Admin::getCurrentAdmin();

// Get dashboard data
$stats = Admin::getDashboardStats();

// Handle AJAX requests
if (is_ajax_request()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    try {
        switch ($action) {
            case 'get_matches':
                $db = Database::getInstance();
                $matches = $db->select("SELECT * FROM matches ORDER BY created_at DESC LIMIT 50");
                json_response(['success' => true, 'data' => $matches]);
                break;
                
            case 'get_channels':
                $db = Database::getInstance();
                $channels = $db->select("SELECT * FROM channels ORDER BY created_at DESC LIMIT 50");
                json_response(['success' => true, 'data' => $channels]);
                break;
                
            case 'add_match':
                if (!Admin::hasPermission('manage_matches')) {
                    throw new Exception('Insufficient permissions');
                }
                
                if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }
                
                $data = [
                    'home_team' => trim($_POST['home_team'] ?? ''),
                    'away_team' => trim($_POST['away_team'] ?? ''),
                    'home_logo' => trim($_POST['home_logo'] ?? ''),
                    'away_logo' => trim($_POST['away_logo'] ?? ''),
                    'match_time' => $_POST['match_time'] ?? '',
                    'location' => trim($_POST['location'] ?? ''),
                    'stream_url' => trim($_POST['stream_url'] ?? ''),
                    'status' => $_POST['status'] ?? 'upcoming',
                    'league' => trim($_POST['league'] ?? ''),
                    'created_by' => $currentAdmin['id']
                ];
                
                // Validate required fields
                $required = ['home_team', 'away_team', 'match_time', 'location', 'stream_url'];
                $errors = validate_required($data, $required);
                
                if (!empty($errors)) {
                    throw new Exception('Missing required fields: ' . implode(', ', array_keys($errors)));
                }
                
                $db = Database::getInstance();
                $matchId = $db->insert('matches', $data);
                
                // Clear cache
                Cache::delete('live_matches');
                Cache::delete('upcoming_matches');
                
                log_activity('Match added', 'info', ['match_id' => $matchId, 'admin_id' => $currentAdmin['id']]);
                json_response(['success' => true, 'message' => 'Match added successfully', 'id' => $matchId]);
                break;
                
            case 'edit_match':
                if (!Admin::hasPermission('manage_matches')) {
                    throw new Exception('Insufficient permissions');
                }
                
                if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }
                
                $matchId = (int)($_POST['id'] ?? 0);
                if (!$matchId) {
                    throw new Exception('Invalid match ID');
                }
                
                $data = [
                    'home_team' => trim($_POST['home_team'] ?? ''),
                    'away_team' => trim($_POST['away_team'] ?? ''),
                    'home_logo' => trim($_POST['home_logo'] ?? ''),
                    'away_logo' => trim($_POST['away_logo'] ?? ''),
                    'match_time' => $_POST['match_time'] ?? '',
                    'location' => trim($_POST['location'] ?? ''),
                    'stream_url' => trim($_POST['stream_url'] ?? ''),
                    'status' => $_POST['status'] ?? 'upcoming',
                    'league' => trim($_POST['league'] ?? '')
                ];
                
                $db = Database::getInstance();
                $updated = $db->update('matches', $data, 'id = :id', ['id' => $matchId]);
                
                // Clear cache
                Cache::delete('live_matches');
                Cache::delete('upcoming_matches');
                
                log_activity('Match updated', 'info', ['match_id' => $matchId, 'admin_id' => $currentAdmin['id']]);
                json_response(['success' => true, 'message' => 'Match updated successfully']);
                break;
                
            case 'delete_match':
                if (!Admin::hasPermission('manage_matches')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $matchId = (int)($_POST['id'] ?? 0);
                if (!$matchId) {
                    throw new Exception('Invalid match ID');
                }
                
                $db = Database::getInstance();
                $deleted = $db->delete('matches', 'id = :id', ['id' => $matchId]);
                
                // Clear cache
                Cache::delete('live_matches');
                Cache::delete('upcoming_matches');
                
                log_activity('Match deleted', 'info', ['match_id' => $matchId, 'admin_id' => $currentAdmin['id']]);
                json_response(['success' => true, 'message' => 'Match deleted successfully']);
                break;
                
            case 'add_channel':
                if (!Admin::hasPermission('manage_channels')) {
                    throw new Exception('Insufficient permissions');
                }
                
                if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }
                
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'slug' => slugify(trim($_POST['name'] ?? '')),
                    'logo' => trim($_POST['logo'] ?? ''),
                    'stream_url' => trim($_POST['stream_url'] ?? ''),
                    'category' => trim($_POST['category'] ?? 'general'),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? 'active',
                    'created_by' => $currentAdmin['id']
                ];
                
                // Validate required fields
                $required = ['name', 'stream_url'];
                $errors = validate_required($data, $required);
                
                if (!empty($errors)) {
                    throw new Exception('Missing required fields: ' . implode(', ', array_keys($errors)));
                }
                
                $db = Database::getInstance();
                $channelId = $db->insert('channels', $data);
                
                // Clear cache
                Cache::delete('active_channels');
                
                log_activity('Channel added', 'info', ['channel_id' => $channelId, 'admin_id' => $currentAdmin['id']]);
                json_response(['success' => true, 'message' => 'Channel added successfully', 'id' => $channelId]);
                break;
                
            case 'edit_channel':
                if (!Admin::hasPermission('manage_channels')) {
                    throw new Exception('Insufficient permissions');
                }
                
                if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }
                
                $channelId = (int)($_POST['id'] ?? 0);
                if (!$channelId) {
                    throw new Exception('Invalid channel ID');
                }
                
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'slug' => slugify(trim($_POST['name'] ?? '')),
                    'logo' => trim($_POST['logo'] ?? ''),
                    'stream_url' => trim($_POST['stream_url'] ?? ''),
                    'category' => trim($_POST['category'] ?? 'general'),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                $db = Database::getInstance();
                $updated = $db->update('channels', $data, 'id = :id', ['id' => $channelId]);
                
                // Clear cache
                Cache::delete('active_channels');
                
                log_activity('Channel updated', 'info', ['channel_id' => $channelId, 'admin_id' => $currentAdmin['id']]);
                json_response(['success' => true, 'message' => 'Channel updated successfully']);
                break;
                
            case 'delete_channel':
                if (!Admin::hasPermission('manage_channels')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $channelId = (int)($_POST['id'] ?? 0);
                if (!$channelId) {
                    throw new Exception('Invalid channel ID');
                }
                
                $db = Database::getInstance();
                $deleted = $db->delete('channels', 'id = :id', ['id' => $channelId]);
                
                // Clear cache
                Cache::delete('active_channels');
                
                log_activity('Channel deleted', 'info', ['channel_id' => $channelId, 'admin_id' => $currentAdmin['id']]);
                json_response(['success' => true, 'message' => 'Channel deleted successfully']);
                break;
                
            case 'update_settings':
                if (!Admin::hasPermission('manage_settings')) {
                    throw new Exception('Insufficient permissions');
                }
                
                if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }
                
                $db = Database::getInstance();
                $updated = 0;
                
                foreach ($_POST as $key => $value) {
                    if ($key !== 'action' && $key !== 'csrf_token') {
                        $existing = $db->selectOne(
                            "SELECT id FROM settings WHERE setting_key = :key",
                            ['key' => $key]
                        );
                        
                        if ($existing) {
                            $db->update('settings', 
                                ['setting_value' => $value, 'updated_by' => $currentAdmin['id']], 
                                'setting_key = :key', 
                                ['key' => $key]
                            );
                        } else {
                            $db->insert('settings', [
                                'setting_key' => $key,
                                'setting_value' => $value,
                                'updated_by' => $currentAdmin['id']
                            ]);
                        }
                        $updated++;
                    }
                }
                
                // Clear cache
                Cache::delete('site_settings');
                
                log_activity('Settings updated', 'info', ['updated_count' => $updated, 'admin_id' => $currentAdmin['id']]);
                json_response(['success' => true, 'message' => 'Settings updated successfully']);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        json_response(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DG SPORTS Admin Dashboard - DiziPortal.Com</title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="admin-dashboard.css">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body class="admin-body">
    <!-- Admin Dashboard -->
    <div id="admin-dashboard" class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-futbol"></i>
                </div>
                <h2 class="sidebar-title">DG SPORTS</h2>
            </div>
            <nav class="sidebar-nav">
                <button class="nav-item active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </button>
                <button class="nav-item" data-section="matches">
                    <i class="fas fa-futbol"></i>
                    <span>Canlı Maçlar</span>
                </button>
                <button class="nav-item" data-section="channels">
                    <i class="fas fa-tv"></i>
                    <span>7/24 Kanallar</span>
                </button>
                <button class="nav-item" data-section="settings">
                    <i class="fas fa-cog"></i>
                    <span>Ayarlar</span>
                </button>
                <button class="nav-item" data-section="logs">
                    <i class="fas fa-file-alt"></i>
                    <span>Loglar</span>
                </button>
            </nav>
            <div class="sidebar-footer">
                <div class="admin-info">
                    <div class="admin-avatar">
                        <?= strtoupper(substr($currentAdmin['username'], 0, 1)) ?>
                    </div>
                    <div class="admin-details">
                        <span class="admin-name"><?= e($currentAdmin['full_name'] ?? $currentAdmin['username']) ?></span>
                        <span class="admin-role"><?= e($currentAdmin['role']) ?></span>
                    </div>
                </div>
                <a href="?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Çıkış Yap
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="breadcrumb">
                        <span>Admin</span>
                        <span class="separator">›</span>
                        <span id="current-section">Dashboard</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="window.open('/', '_blank')">
                            <i class="fas fa-external-link-alt"></i>
                            Siteyi Görüntüle
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Dashboard Section -->
                <section id="dashboard-section" class="content-section active">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </div>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-futbol"></i>
                            </div>
                            <div class="stat-details">
                                <h3><?= $stats['total_matches'] ?></h3>
                                <p>Toplam Maç</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-play-circle"></i>
                            </div>
                            <div class="stat-details">
                                <h3><?= $stats['live_matches'] ?></h3>
                                <p>Canlı Maç</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-tv"></i>
                            </div>
                            <div class="stat-details">
                                <h3><?= $stats['total_channels'] ?></h3>
                                <p>Toplam Kanal</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-details">
                                <h3><?= format_number($stats['total_viewers']) ?></h3>
                                <p>Toplam İzleyici</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-line"></i>
                                        Hoş Geldiniz, <?= e($currentAdmin['full_name'] ?? $currentAdmin['username']) ?>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3">DG SPORTS Admin Panel'e hoş geldiniz. Buradan sitenizi yönetebilirsiniz.</p>
                                    <div class="quick-actions">
                                        <button class="btn btn-primary" onclick="showSection('matches')">
                                            <i class="fas fa-plus"></i>
                                            Yeni Maç Ekle
                                        </button>
                                        <button class="btn btn-success" onclick="showSection('channels')">
                                            <i class="fas fa-plus"></i>
                                            Yeni Kanal Ekle
                                        </button>
                                        <button class="btn btn-info" onclick="showSection('settings')">
                                            <i class="fas fa-cog"></i>
                                            Ayarları Düzenle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Matches Section -->
                <section id="matches-section" class="content-section">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-futbol"></i>
                            Canlı Maçlar
                        </div>
                        <div class="section-actions">
                            <button class="btn btn-primary" id="add-match-btn">
                                <i class="fas fa-plus"></i>
                                Maç Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="matches-table">
                                    <thead>
                                        <tr>
                                            <th>Takımlar</th>
                                            <th>Konum</th>
                                            <th>Tarih & Saat</th>
                                            <th>Durum</th>
                                            <th>İzleyici</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Matches will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Channels Section -->
                <section id="channels-section" class="content-section">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-tv"></i>
                            7/24 Kanallar
                        </div>
                        <div class="section-actions">
                            <button class="btn btn-primary" id="add-channel-btn">
                                <i class="fas fa-plus"></i>
                                Kanal Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="channels-table">
                                    <thead>
                                        <tr>
                                            <th>Kanal</th>
                                            <th>Kategori</th>
                                            <th>Durum</th>
                                            <th>İzleyici</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Channels will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Settings Section -->
                <section id="settings-section" class="content-section">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-cog"></i>
                            Site Ayarları
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form id="settings-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="site_title">Site Başlığı</label>
                                            <input type="text" class="form-control" name="site_title" id="site_title" 
                                                   value="DG SPORTS" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="site_description">Site Açıklaması</label>
                                            <input type="text" class="form-control" name="site_description" id="site_description" 
                                                   value="Kaliteli HD yayın ile tüm spor müsabakalarını canlı izleyin" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_keywords">Site Anahtar Kelimeleri</label>
                                    <input type="text" class="form-control" name="site_keywords" id="site_keywords" 
                                           value="canlı maç, spor yayını, futbol, basketbol, HD yayın">
                                </div>
                                
                                <h4 class="mt-4 mb-3">Sosyal Medya Bağlantıları</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="telegram_url">Telegram URL</label>
                                            <input type="url" class="form-control" name="telegram_url" id="telegram_url">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="instagram_url">Instagram URL</label>
                                            <input type="url" class="form-control" name="instagram_url" id="instagram_url">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="twitter_url">Twitter URL</label>
                                            <input type="url" class="form-control" name="twitter_url" id="twitter_url">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tiktok_url">TikTok URL</label>
                                            <input type="url" class="form-control" name="tiktok_url" id="tiktok_url">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Ayarları Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Logs Section -->
                <section id="logs-section" class="content-section">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-file-alt"></i>
                            Sistem Logları
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p>Sistem logları geliştirilme aşamasında...</p>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <!-- Add/Edit Match Modal -->
    <div id="match-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="match-modal-title">
                    <i class="fas fa-plus"></i>
                    Yeni Maç Ekle
                </h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="match-form">
                    <input type="hidden" name="id" id="match-id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="home_team">Ev Sahibi Takım <span class="required">*</span></label>
                                <input type="text" name="home_team" id="home_team" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="away_team">Deplasman Takımı <span class="required">*</span></label>
                                <input type="text" name="away_team" id="away_team" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="home_logo">Ev Sahibi Logo URL</label>
                                <input type="url" name="home_logo" id="home_logo" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="away_logo">Deplasman Logo URL</label>
                                <input type="url" name="away_logo" id="away_logo" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="match_time">Maç Tarihi & Saati <span class="required">*</span></label>
                                <input type="datetime-local" name="match_time" id="match_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Konum <span class="required">*</span></label>
                                <input type="text" name="location" id="location" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="league">Lig</label>
                                <input type="text" name="league" id="league" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Durum</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="upcoming">Yakında</option>
                                    <option value="live">Canlı</option>
                                    <option value="ended">Bitti</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="stream_url">Yayın URL'si <span class="required">*</span></label>
                        <input type="url" name="stream_url" id="stream_url" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('match-modal')">İptal</button>
                <button type="submit" form="match-form" class="btn btn-primary">Kaydet</button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Channel Modal -->
    <div id="channel-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="channel-modal-title">
                    <i class="fas fa-plus"></i>
                    Yeni Kanal Ekle
                </h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="channel-form">
                    <input type="hidden" name="id" id="channel-id">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="channel_name">Kanal Adı <span class="required">*</span></label>
                                <input type="text" name="name" id="channel_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category">Kategori</label>
                                <select name="category" id="category" class="form-control">
                                    <option value="general">Genel Spor</option>
                                    <option value="football">Futbol</option>
                                    <option value="basketball">Basketbol</option>
                                    <option value="volleyball">Voleybol</option>
                                    <option value="news">Spor Haberleri</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="channel_logo">Logo URL</label>
                        <input type="url" name="logo" id="channel_logo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="channel_description">Açıklama</label>
                        <textarea name="description" id="channel_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="channel_stream_url">Yayın URL'si <span class="required">*</span></label>
                                <input type="url" name="stream_url" id="channel_stream_url" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="channel_status">Durum</label>
                                <select name="status" id="channel_status" class="form-control">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Pasif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('channel-modal')">İptal</button>
                <button type="submit" form="channel-form" class="btn btn-primary">Kaydet</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Global variables
        window.csrfToken = '<?= csrf_token() ?>';
        window.currentAdmin = <?= json_encode($currentAdmin) ?>;
    </script>
    <script src="admin-dashboard.js"></script>
</body>
</html>