-- DG SPORTS Database Schema
-- Developer: DiziPortal.Com
-- MySQL Database Structure

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Table structure for table `admins`
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `matches`
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `home_team` varchar(100) NOT NULL,
  `away_team` varchar(100) NOT NULL,
  `home_logo` text DEFAULT NULL,
  `away_logo` text DEFAULT NULL,
  `match_time` datetime NOT NULL,
  `location` varchar(200) NOT NULL,
  `stream_url` text NOT NULL,
  `backup_stream_url` text DEFAULT NULL,
  `status` enum('upcoming','live','ended','cancelled') NOT NULL DEFAULT 'upcoming',
  `viewers` int(11) NOT NULL DEFAULT 0,
  `max_viewers` int(11) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `category` varchar(50) DEFAULT 'football',
  `league` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `match_time` (`match_time`),
  KEY `featured` (`featured`),
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `channels`
CREATE TABLE IF NOT EXISTS `channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `logo` text DEFAULT NULL,
  `stream_url` text NOT NULL,
  `backup_stream_url` text DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `language` varchar(10) NOT NULL DEFAULT 'tr',
  `country` varchar(10) NOT NULL DEFAULT 'TR',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `viewers` int(11) NOT NULL DEFAULT 0,
  `max_viewers` int(11) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `quality` enum('sd','hd','fhd','4k') NOT NULL DEFAULT 'hd',
  `is_live` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `featured` (`featured`),
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `settings`
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json','text') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `category` (`category`),
  KEY `updated_by` (`updated_by`),
  FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `viewer_logs`
CREATE TABLE IF NOT EXISTS `viewer_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` enum('match','channel') NOT NULL,
  `content_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `country` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `session_duration` int(11) DEFAULT NULL,
  `quality_requested` varchar(10) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','smart_tv') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_type_id` (`content_type`, `content_id`),
  KEY `ip_address` (`ip_address`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `login_logs`
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `failure_reason` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `ip_address` (`ip_address`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `cache`
CREATE TABLE IF NOT EXISTS `cache` (
  `cache_key` varchar(255) NOT NULL,
  `cache_value` longtext NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cache_key`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Insert default admin user
INSERT IGNORE INTO `admins` (`username`, `password`, `email`, `full_name`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@dgsports.com', 'System Administrator', 'super_admin', 'active');

-- Insert default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `category`, `is_public`) VALUES
('site_title', 'DG SPORTS', 'string', 'Site title', 'general', 1),
('site_description', 'Kaliteli HD yayın ile tüm spor müsabakalarını canlı izleyin', 'string', 'Site description', 'general', 1),
('site_keywords', 'canlı maç, spor yayını, futbol, basketbol, HD yayın', 'string', 'Site keywords', 'seo', 1),
('maintenance_mode', '0', 'boolean', 'Maintenance mode status', 'general', 0),
('max_viewers_per_stream', '10000', 'integer', 'Maximum viewers per stream', 'limits', 0),
('cache_duration', '300', 'integer', 'Cache duration in seconds', 'performance', 0),
('telegram_url', '', 'string', 'Telegram channel URL', 'social', 1),
('instagram_url', '', 'string', 'Instagram profile URL', 'social', 1),
('twitter_url', '', 'string', 'Twitter profile URL', 'social', 1),
('tiktok_url', '', 'string', 'TikTok profile URL', 'social', 1),
('upload_max_size', '5242880', 'integer', 'Maximum upload size in bytes (5MB)', 'uploads', 0),
('allowed_file_types', 'jpg,jpeg,png,gif,svg', 'string', 'Allowed file types for upload', 'uploads', 0);

-- Insert default channels
INSERT IGNORE INTO `channels` (`name`, `slug`, `logo`, `stream_url`, `category`, `description`, `status`, `featured`) VALUES
('beIN SPORTS 1 HD', 'bein-sports-1-hd', 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Bein_sports_1.png/512px-Bein_sports_1.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 'football', '24/7 Canlı Spor Yayını', 'active', 1),
('TRT SPOR', 'trt-spor', 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/TRT_Spor_logo.png/512px-TRT_Spor_logo.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4', 'general', 'Türkiye\'nin Spor Kanalı', 'active', 1),
('SPORTS TV', 'sports-tv', 'https://via.placeholder.com/150x150/dc2626/ffffff?text=SPORTS', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4', 'general', 'Genel Spor Kanalı', 'active', 0);

-- Insert default matches
INSERT IGNORE INTO `matches` (`home_team`, `away_team`, `home_logo`, `away_logo`, `match_time`, `location`, `stream_url`, `status`, `featured`, `category`, `league`) VALUES
('Galatasaray', 'Fenerbahçe', 'https://logoeps.com/wp-content/uploads/2013/03/galatasaray-vector-logo.png', 'https://logoeps.com/wp-content/uploads/2013/03/fenerbahce-vector-logo.png', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'Türk Telekom Stadyumu', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', 'live', 1, 'football', 'Süper Lig'),
('Real Madrid', 'Barcelona', 'https://logoeps.com/wp-content/uploads/2013/03/real-madrid-vector-logo.png', 'https://logoeps.com/wp-content/uploads/2013/03/barcelona-vector-logo.png', DATE_ADD(NOW(), INTERVAL 4 HOUR), 'Santiago Bernabéu', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 'upcoming', 1, 'football', 'La Liga'),
('Manchester United', 'Liverpool', 'https://via.placeholder.com/100x100/dc2626/ffffff?text=MU', 'https://via.placeholder.com/100x100/c41e3a/ffffff?text=LIV', DATE_ADD(NOW(), INTERVAL 1 DAY), 'Old Trafford', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4', 'upcoming', 1, 'football', 'Premier League');

COMMIT;

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_matches_status_time ON matches(status, match_time);
CREATE INDEX IF NOT EXISTS idx_channels_status_featured ON channels(status, featured);
CREATE INDEX IF NOT EXISTS idx_viewer_logs_date ON viewer_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_settings_category ON settings(category);

-- Views for easy data access
CREATE OR REPLACE VIEW v_live_matches AS
SELECT * FROM matches 
WHERE status = 'live' 
ORDER BY viewers DESC, match_time ASC;

CREATE OR REPLACE VIEW v_upcoming_matches AS
SELECT * FROM matches 
WHERE status = 'upcoming' AND match_time > NOW()
ORDER BY match_time ASC;

CREATE OR REPLACE VIEW v_active_channels AS
SELECT * FROM channels 
WHERE status = 'active' 
ORDER BY featured DESC, sort_order ASC, name ASC;

CREATE OR REPLACE VIEW v_featured_content AS
SELECT 'match' as content_type, id, home_team as title, away_team as subtitle, 
       home_logo as logo, stream_url, viewers, created_at
FROM matches WHERE featured = 1 AND status IN ('live', 'upcoming')
UNION ALL
SELECT 'channel' as content_type, id, name as title, description as subtitle, 
       logo, stream_url, viewers, created_at
FROM channels WHERE featured = 1 AND status = 'active'
ORDER BY content_type, viewers DESC;