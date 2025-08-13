<?php
/**
 * DG SPORTS - Main Index Page
 * Developer: DiziPortal.Com
 * Professional sports streaming platform
 */

// Check if installation is completed
if (!file_exists(__DIR__ . '/install.lock') && !file_exists(__DIR__ . '/.env')) {
    header('Location: install.php');
    exit('Kurulum gerekli. <a href="install.php">Kurulum sayfasına git</a>');
}

require_once 'includes/bootstrap.php';

// Get page data
$matches = Cache::remember('live_matches', function() {
    $db = Database::getInstance();
    return $db->select("SELECT * FROM v_live_matches LIMIT 10");
}, 300); // Cache for 5 minutes

$upcomingMatches = Cache::remember('upcoming_matches', function() {
    $db = Database::getInstance();
    return $db->select("SELECT * FROM v_upcoming_matches LIMIT 6");
}, 600); // Cache for 10 minutes

$channels = Cache::remember('active_channels', function() {
    $db = Database::getInstance();
    return $db->select("SELECT * FROM v_active_channels LIMIT 12");
}, 300); // Cache for 5 minutes

$settings = Cache::remember('site_settings', function() {
    $db = Database::getInstance();
    $settingsData = $db->select("SELECT setting_key, setting_value FROM settings WHERE is_public = 1");
    $settings = [];
    foreach ($settingsData as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    return $settings;
}, 3600); // Cache for 1 hour

// Update viewer counts periodically
if (rand(1, 10) === 1) {
    $db = Database::getInstance();
    
    // Update match viewers with random realistic numbers
    $db->query("UPDATE matches SET viewers = FLOOR(RAND() * 5000) + 100 WHERE status = 'live'");
    
    // Update channel viewers
    $db->query("UPDATE channels SET viewers = FLOOR(RAND() * 3000) + 50 WHERE status = 'active'");
    
    // Clear related cache
    Cache::delete('live_matches');
    Cache::delete('active_channels');
}

// Log viewer activity
$ip = get_client_ip();
$userAgent = get_user_agent();
$deviceType = get_device_type();

// Don't log bots
if (!preg_match('/bot|crawl|spider/i', $userAgent)) {
    $db = Database::getInstance();
    $db->insert('viewer_logs', [
        'content_type' => 'page',
        'content_id' => 0,
        'ip_address' => $ip,
        'user_agent' => $userAgent,
        'device_type' => $deviceType,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

// Set meta tags
$pageTitle = $settings['site_title'] ?? 'DG SPORTS - Canlı Maç İzle';
$pageDescription = $settings['site_description'] ?? 'Kaliteli HD yayın ile tüm spor müsabakalarını canlı izleyin';
$pageKeywords = $settings['site_keywords'] ?? 'canlı maç, spor yayını, futbol, basketbol, HD yayın';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="keywords" content="<?= e($pageKeywords) ?>">
    <meta name="author" content="DiziPortal.Com">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= asset('assets/images/og-image.jpg') ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= asset('assets/images/og-image.jpg') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('assets/images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= asset('assets/images/apple-touch-icon.png') ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('assets/css/diziportal-styles.css') ?>">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?= e($pageTitle) ?>",
        "description": "<?= e($pageDescription) ?>",
        "url": "<?= current_url() ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= current_url() ?>?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen" class="loading-screen">
        <div class="loading-content">
            <div class="loading-logo">
                <i class="fas fa-futbol"></i>
                <h2>DG SPORTS</h2>
            </div>
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
            <p>Yükleniyor...</p>
            <div class="loading-info">
                <small>DiziPortal.Com</small>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-futbol"></i>
                    </div>
                    <div class="logo-text">
                        <h1>DG SPORTS</h1>
                        <span>HD Yayın</span>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="nav">
                    <div class="nav-menu" id="nav-menu">
                        <a href="#hero" class="nav-link active" data-section="hero">
                            <i class="fas fa-home"></i>
                            <span>Ana Sayfa</span>
                        </a>
                        <a href="#matches" class="nav-link" data-section="matches">
                            <i class="fas fa-futbol"></i>
                            <span>Canlı Maçlar</span>
                        </a>
                        <a href="#channels" class="nav-link" data-section="channels">
                            <i class="fas fa-tv"></i>
                            <span>7/24 Kanallar</span>
                        </a>
                        <a href="#contact" class="nav-link" data-section="contact">
                            <i class="fas fa-envelope"></i>
                            <span>İletişim</span>
                        </a>
                    </div>
                    <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="hero-background">
            <div class="hero-overlay"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Canlı <span class="highlight">Spor</span> Yayınları
                    </h1>
                    <p class="hero-description">
                        <?= e($pageDescription) ?>
                    </p>
                    <div class="hero-buttons">
                        <a href="#matches" class="btn btn-primary">
                            <i class="fas fa-play"></i>
                            Canlı Maçlar
                        </a>
                        <a href="#channels" class="btn btn-secondary">
                            <i class="fas fa-tv"></i>
                            Kanallar
                        </a>
                    </div>
                </div>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-futbol"></i>
                        </div>
                        <div class="stat-details">
                            <h3 id="live-matches-count"><?= count($matches) ?></h3>
                            <p>Canlı Maç</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-tv"></i>
                        </div>
                        <div class="stat-details">
                            <h3 id="channels-count"><?= count($channels) ?></h3>
                            <p>Aktif Kanal</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-details">
                            <h3 id="viewers-count"><?= format_number(array_sum(array_column($matches, 'viewers')) + array_sum(array_column($channels, 'viewers'))) ?></h3>
                            <p>İzleyici</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Matches Section -->
    <section id="matches" class="section matches-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <i class="fas fa-futbol"></i>
                    <h2>Canlı Maçlar</h2>
                </div>
                <div class="section-subtitle">
                    <p>Anlık canlı yayında olan maçları izleyebilirsiniz</p>
                </div>
            </div>

            <!-- Inline Player Container -->
            <div class="inline-player-container" id="inline-player-container" style="display: none;">
                <div class="inline-player-header">
                    <div class="playing-info">
                        <div class="playing-icon">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="playing-details">
                            <h3 id="playing-title">Şu anda İzleniyor</h3>
                            <p id="playing-subtitle">Maç detayları</p>
                        </div>
                    </div>
                    <div class="player-controls-header">
                        <div class="viewer-count-header">
                            <i class="fas fa-eye"></i>
                            <span id="inline-viewer-count">0</span>
                        </div>
                        <button class="player-control-btn fullscreen-btn" onclick="openFullscreen()">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button class="player-control-btn close-btn" onclick="closeInlinePlayer()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="inline-player-wrapper">
                    <div id="inline-player"></div>
                    <div class="player-overlay" id="player-overlay">
                        <div class="overlay-content">
                            <div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>
                            <p>Yayın yükleniyor...</p>
                        </div>
                    </div>
                </div>
                <div class="match-info-inline" id="match-info-inline" style="display: none;">
                    <!-- Team and match details will be populated here -->
                </div>
            </div>

            <div class="matches-grid" id="matches-grid">
                <?php if (empty($matches)): ?>
                    <div class="no-content">
                        <i class="fas fa-futbol"></i>
                        <h3>Şu anda canlı maç bulunmuyor</h3>
                        <p>Yakında başlayacak maçlar için aşağıya bakın</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($matches as $match): ?>
                        <div class="match-card" data-match-id="<?= $match['id'] ?>">
                            <div class="match-header">
                                <div class="match-league"><?= e($match['league'] ?? 'Spor') ?></div>
                                <div class="match-status live">
                                    <i class="fas fa-circle"></i>
                                    CANLI
                                </div>
                            </div>
                            
                            <div class="teams">
                                <div class="team home-team">
                                    <img src="<?= e($match['home_logo']) ?>" alt="<?= e($match['home_team']) ?>" 
                                         class="team-logo" 
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMzAiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZmZmZiIgeD0iMTgiIHk9IjE4Ij4KPHBhdGggZD0iTTEyIDJMMTUgOS41TDIyIDEwTDE1IDE1LjVMMTIgMjJMOSAxNS41TDIgMTBMOSA5LjVMMTIgMloiLz4KPC9zdmc+Cjwvc3ZnPgo='">
                                    <h3><?= e($match['home_team']) ?></h3>
                                </div>
                                
                                <div class="vs">
                                    <span>VS</span>
                                    <div class="match-time">
                                        <?= date('H:i', strtotime($match['match_time'])) ?>
                                    </div>
                                </div>
                                
                                <div class="team away-team">
                                    <img src="<?= e($match['away_logo']) ?>" alt="<?= e($match['away_team']) ?>" 
                                         class="team-logo"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMzAiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZmZmZiIgeD0iMTgiIHk9IjE4Ij4KPHBhdGggZD0iTTEyIDJMMTUgOS41TDIyIDEwTDE1IDE1LjVMMTIgMjJMOSAxNS41TDIgMTBMOSA5LjVMMTIgMloiLz4KPC9zdmc+Cjwvc3ZnPgo='">
                                    <h3><?= e($match['away_team']) ?></h3>
                                </div>
                            </div>
                            
                            <div class="match-info">
                                <div class="match-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= e($match['location']) ?>
                                </div>
                                <div class="match-viewers">
                                    <i class="fas fa-eye"></i>
                                    <?= format_number($match['viewers']) ?> izleyici
                                </div>
                            </div>
                            
                            <div class="match-actions">
                                <button class="btn btn-primary match-watch-btn" 
                                        data-stream-url="<?= e($match['stream_url']) ?>"
                                        data-match-title="<?= e($match['home_team'] . ' vs ' . $match['away_team']) ?>"
                                        data-match-info="<?= e($match['league'] . ' - ' . $match['location']) ?>"
                                        data-viewers="<?= $match['viewers'] ?>">
                                    <i class="fas fa-play"></i>
                                    Canlı İzle
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Upcoming Matches -->
            <?php if (!empty($upcomingMatches)): ?>
                <div class="upcoming-matches">
                    <h3><i class="fas fa-clock"></i> Yakında Başlayacak Maçlar</h3>
                    <div class="upcoming-grid">
                        <?php foreach ($upcomingMatches as $match): ?>
                            <div class="upcoming-match">
                                <div class="upcoming-teams">
                                    <span><?= e($match['home_team']) ?></span>
                                    <span class="vs">vs</span>
                                    <span><?= e($match['away_team']) ?></span>
                                </div>
                                <div class="upcoming-time">
                                    <i class="fas fa-clock"></i>
                                    <?= date('d.m H:i', strtotime($match['match_time'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Channels Section -->
    <section id="channels" class="section channels-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <i class="fas fa-tv"></i>
                    <h2>7/24 Spor Kanalları</h2>
                </div>
                <div class="section-subtitle">
                    <p>Kesintisiz spor yayınları ve en iyi kalitede içerikler</p>
                </div>
            </div>

            <div class="channels-grid" id="channels-grid">
                <?php if (empty($channels)): ?>
                    <div class="no-content">
                        <i class="fas fa-tv"></i>
                        <h3>Aktif kanal bulunmuyor</h3>
                        <p>Kanallar yakında eklenecek</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($channels as $channel): ?>
                        <div class="channel-card" data-channel-id="<?= $channel['id'] ?>">
                            <div class="channel-logo-container">
                                <img src="<?= e($channel['logo']) ?>" alt="<?= e($channel['name']) ?>" 
                                     class="channel-logo"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiByeD0iOCIgZmlsbD0iI2RjMjYyNiIvPgo8cGF0aCBkPSJNMzAgMjRMNTAgNDBMMzAgNTZWMjRaIiBmaWxsPSIjZmZmZmZmIi8+Cjwvc3ZnPgo='">
                                <div class="channel-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            
                            <div class="channel-content">
                                <h3 class="channel-name"><?= e($channel['name']) ?></h3>
                                <p class="channel-description"><?= e($channel['description'] ?? 'Canlı spor yayını') ?></p>
                                
                                <div class="channel-info">
                                    <div class="channel-status live">
                                        <i class="fas fa-circle"></i>
                                        CANLI
                                    </div>
                                    <div class="channel-viewers">
                                        <i class="fas fa-eye"></i>
                                        <?= format_number($channel['viewers']) ?>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary channel-watch-btn"
                                        data-stream-url="<?= e($channel['stream_url']) ?>"
                                        data-channel-title="<?= e($channel['name']) ?>"
                                        data-channel-info="<?= e($channel['description']) ?>"
                                        data-viewers="<?= $channel['viewers'] ?>">
                                    <i class="fas fa-play"></i>
                                    İzle
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section contact-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <i class="fas fa-envelope"></i>
                    <h2>İletişim</h2>
                </div>
                <div class="section-subtitle">
                    <p>Bizimle iletişime geçin ve sosyal medyada takip edin</p>
                </div>
            </div>

            <div class="contact-content">
                <div class="social-links">
                    <?php if (!empty($settings['telegram_url'])): ?>
                        <a href="<?= e($settings['telegram_url']) ?>" class="social-link telegram" target="_blank">
                            <i class="fab fa-telegram"></i>
                            <span>Telegram</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['instagram_url'])): ?>
                        <a href="<?= e($settings['instagram_url']) ?>" class="social-link instagram" target="_blank">
                            <i class="fab fa-instagram"></i>
                            <span>Instagram</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['twitter_url'])): ?>
                        <a href="<?= e($settings['twitter_url']) ?>" class="social-link twitter" target="_blank">
                            <i class="fab fa-twitter"></i>
                            <span>Twitter</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['tiktok_url'])): ?>
                        <a href="<?= e($settings['tiktok_url']) ?>" class="social-link tiktok" target="_blank">
                            <i class="fab fa-tiktok"></i>
                            <span>TikTok</span>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="contact-info">
                    <div class="info-item">
                        <h3>DG SPORTS</h3>
                        <p>Profesyonel spor yayın platformu</p>
                    </div>
                    <div class="info-item">
                        <h3>Geliştirici</h3>
                        <p>DiziPortal.Com</p>
                    </div>
                    <div class="info-item">
                        <h3>Kalite</h3>
                        <p>HD ve 4K yayın desteği</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <div class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-futbol"></i>
                        </div>
                        <div class="logo-text">
                            <h3>DG SPORTS</h3>
                            <span>DiziPortal.Com</span>
                        </div>
                    </div>
                </div>
                
                <div class="footer-text">
                    <p>&copy; <?= date('Y') ?> DG SPORTS. Tüm hakları saklıdır. | Geliştirici: DiziPortal.Com</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/playerjs@1/dist/playerjs.js"></script>
    <script src="<?= asset('assets/js/diziportal-player.js') ?>"></script>
    <script>
        // Initialize app with PHP data
        window.DGSportsData = {
            csrfToken: '<?= csrf_token() ?>',
            apiUrl: '<?= asset('api/') ?>',
            matches: <?= json_encode($matches) ?>,
            channels: <?= json_encode($channels) ?>,
            settings: <?= json_encode($settings) ?>
        };
    </script>
    <script src="<?= asset('assets/js/diziportal-app.js') ?>"></script>
</body>
</html>