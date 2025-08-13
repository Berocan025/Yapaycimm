<?php
/**
 * DG SPORTS - Application Configuration
 * Developer: DiziPortal.Com
 * Main application settings
 */

// Security check
if (!defined('DG_SPORTS_APP')) {
    die('Direct access forbidden');
}

return [
    // Application settings
    'name' => $_ENV['APP_NAME'] ?? 'DG SPORTS',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'https://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Europe/Istanbul',
    'locale' => 'tr',
    'fallback_locale' => 'en',
    
    // Security settings
    'key' => $_ENV['APP_KEY'] ?? 'dg-sports-secret-key-change-in-production',
    'cipher' => 'AES-256-CBC',
    'password_min_length' => 8,
    'session_lifetime' => 120, // minutes
    'remember_me_lifetime' => 10080, // minutes (7 days)
    
    // Cache settings
    'cache_enabled' => true,
    'cache_driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
    'cache_ttl' => 3600, // seconds
    
    // Upload settings
    'upload_path' => '/uploads',
    'max_upload_size' => 5 * 1024 * 1024, // 5MB
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
    'allowed_video_types' => ['mp4', 'webm', 'ogg'],
    
    // Streaming settings
    'max_viewers_per_stream' => 10000,
    'viewer_count_update_interval' => 30, // seconds
    'stream_quality_check_interval' => 300, // seconds
    
    // API settings
    'api_rate_limit' => 60, // requests per minute
    'api_timeout' => 30, // seconds
    'cors_allowed_origins' => ['*'],
    
    // Admin settings
    'admin_path' => '/admin',
    'admin_session_timeout' => 60, // minutes
    'max_login_attempts' => 5,
    'login_lockout_duration' => 15, // minutes
    
    // Social media
    'social_links' => [
        'telegram' => '',
        'instagram' => '',
        'twitter' => '',
        'tiktok' => '',
        'youtube' => '',
        'facebook' => ''
    ],
    
    // SEO settings
    'meta' => [
        'title' => 'DG SPORTS - Canlı Maç İzle',
        'description' => 'Kaliteli HD yayın ile tüm spor müsabakalarını canlı izleyin. Futbol, basketbol ve daha fazlası.',
        'keywords' => 'canlı maç, spor yayını, futbol, basketbol, HD yayın, canli izle',
        'author' => 'DiziPortal.Com',
        'robots' => 'index, follow',
        'og_image' => '/assets/images/og-image.jpg'
    ],
    
    // Email settings
    'mail' => [
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
        'port' => $_ENV['MAIL_PORT'] ?? 587,
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@dgsports.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'DG SPORTS'
    ],
    
    // Logging
    'log' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        'max_files' => 30,
        'max_size' => 10 * 1024 * 1024, // 10MB
        'path' => '/logs'
    ],
    
    // Feature flags
    'features' => [
        'registration_enabled' => false,
        'comments_enabled' => false,
        'rating_enabled' => true,
        'social_login_enabled' => false,
        'maintenance_mode' => false,
        'analytics_enabled' => true,
        'cdn_enabled' => false
    ],
    
    // Third-party services
    'services' => [
        'google_analytics' => $_ENV['GOOGLE_ANALYTICS_ID'] ?? '',
        'facebook_pixel' => $_ENV['FACEBOOK_PIXEL_ID'] ?? '',
        'cloudflare_zone' => $_ENV['CLOUDFLARE_ZONE_ID'] ?? '',
        'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
        'recaptcha_secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? ''
    ]
];