# DG SPORTS - Professional Sports Streaming Platform (PHP Version)

> **Developer:** DiziPortal.Com  
> **Version:** 2.0.0 PHP  
> **License:** Proprietary  

## 🚀 SUPER EASY INSTALLATION

**One-Click Installation:** Upload files to your server and open `install.php` in your browser. Automatic setup in minutes!

```bash
1. Upload files to server
2. Open: yoursite.com/install.php
3. Follow 4 simple steps - Done! 🎉
```

Professional sports streaming platform built with **PHP**, **MySQL**, and modern web technologies. Features live match streaming, 24/7 channels, comprehensive admin panel, and mobile-responsive design.

## 🚀 Features

### 🎯 Core Features
- **Live Match Streaming** - Real-time sports matches with HD quality
- **24/7 Sport Channels** - Continuous streaming channels
- **Professional Admin Panel** - Complete content management system
- **Mobile Responsive** - Perfect experience on all devices
- **PlayerJS Integration** - Advanced video player with HLS support
- **Real-time Analytics** - Live viewer counts and statistics

### 👑 Advanced Features
- **Database-Driven** - MySQL database with professional schema
- **Secure Authentication** - PHP session management with security
- **Caching System** - File-based caching for optimal performance
- **Logo Management** - Automatic fallback system for broken images
- **CSRF Protection** - Cross-site request forgery protection
- **Rate Limiting** - API and login attempt protection
- **Activity Logging** - Comprehensive activity tracking

### 🛡️ Security Features
- **Admin Authentication** - Secure login with remember me
- **Session Management** - Secure PHP sessions with timeout
- **Input Validation** - Server-side validation and sanitization
- **SQL Injection Protection** - PDO prepared statements
- **XSS Protection** - HTML entity escaping
- **Brute Force Protection** - Login attempt limiting

## 📁 Project Structure

```
DG-SPORTS-PHP/
├── 📂 admin/                     # Admin Panel
│   ├── index.php                 # Main admin dashboard
│   ├── login.php                 # Admin login page
│   ├── admin-dashboard.css       # Admin panel styles
│   └── admin-dashboard.js        # Admin panel JavaScript
├── 📂 api/                       # API Endpoints (Future)
├── 📂 assets/                    # Frontend Assets
│   ├── 📂 css/
│   │   └── diziportal-styles.css # Main stylesheet
│   ├── 📂 js/
│   │   ├── diziportal-app.js     # Main application
│   │   └── diziportal-player.js  # Player management
│   └── 📂 images/                # Images and logos
├── 📂 cache/                     # Cache directory (auto-created)
├── 📂 classes/                   # PHP Classes
│   ├── Admin.php                 # Admin model
│   ├── Cache.php                 # Caching system
│   ├── Database.php              # Database wrapper
│   └── Security.php              # Security utilities
├── 📂 config/                    # Configuration
│   ├── app.php                   # Application config
│   └── database.php              # Database config
├── 📂 includes/                  # Core includes
│   ├── bootstrap.php             # Application bootstrap
│   └── functions.php             # Helper functions
├── 📂 logs/                      # Log files (auto-created)
├── 📂 uploads/                   # Uploaded files (auto-created)
├── 📄 .env                       # Environment variables
├── 📄 .htaccess                  # Apache configuration
├── 📄 database.sql               # Database schema
├── 📄 index.php                  # Main entry point
└── 📄 README.md                  # This file
```

## 🛠️ Installation

### Prerequisites
- **PHP 7.4+** with extensions:
  - PDO MySQL
  - OpenSSL
  - mbstring
  - fileinfo
  - GD or Imagick (for image processing)
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Apache/Nginx** web server
- **mod_rewrite** enabled (Apache)

### Step 1: Database Setup
```sql
-- Import database schema
mysql -u username -p < database.sql

-- Or create database manually:
CREATE DATABASE dg_sports CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 2: Environment Configuration
```bash
# Copy and configure environment file
cp .env.example .env

# Edit configuration
nano .env
```

**Example .env file:**
```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=dg_sports
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application Configuration
APP_NAME="DG SPORTS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.com
APP_KEY=your-secret-key-here

# Cache Configuration
CACHE_DRIVER=file

# Mail Configuration (Optional)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### Step 3: File Permissions
```bash
# Set proper permissions
chmod 755 -R .
chmod 777 cache/ logs/ uploads/
chmod 644 .env
```

### Step 4: Web Server Configuration

**Apache (.htaccess already included):**
```apache
# Already configured in .htaccess
# Ensure mod_rewrite is enabled
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name yoursite.com;
    root /path/to/dg-sports;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location /admin {
        # Add IP restrictions here if needed
        # allow 192.168.1.100;
        # deny all;
    }
}
```

## 🔐 Admin Panel

### Access Information
- **URL:** `https://yoursite.com/admin/`
- **Default Username:** `admin`
- **Default Password:** `secret` (Change immediately!)

### Features
- **Dashboard** - Real-time statistics and quick actions
- **Live Matches** - Add, edit, delete matches with CRUD operations
- **24/7 Channels** - Manage streaming channels
- **Settings** - Site configuration and social media links
- **Activity Logs** - Monitor system activities

### Security Features
- IP-based access control (configure in .htaccess)
- Session timeout protection
- Brute force protection
- CSRF token validation
- Remember me functionality

## 🎮 Usage

### Adding Live Matches
1. Access admin panel at `/admin/`
2. Navigate to "Canlı Maçlar" section
3. Click "Maç Ekle" button
4. Fill in match details:
   - Home and away teams
   - Team logos (URLs)
   - Match date and time
   - Location
   - Stream URL
   - Status (upcoming/live/ended)

### Adding 24/7 Channels
1. Go to "7/24 Kanallar" section
2. Click "Kanal Ekle" button
3. Enter channel information:
   - Channel name
   - Logo URL
   - Stream URL
   - Category
   - Description
   - Status (active/inactive)

### Site Configuration
1. Navigate to "Ayarlar" section
2. Update site settings:
   - Site title and description
   - SEO keywords
   - Social media links
   - Other configurations

## 🔧 Configuration

### Application Settings
Edit `config/app.php` for application-wide settings:

```php
return [
    'name' => 'DG SPORTS',
    'env' => 'production',
    'debug' => false,
    'timezone' => 'Europe/Istanbul',
    'cache_ttl' => 3600,
    'max_upload_size' => 5 * 1024 * 1024, // 5MB
    // ... more settings
];
```

### Database Settings
Configure `config/database.php` for database connections:

```php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_DATABASE'] ?? 'dg_sports',
            // ... more settings
        ]
    ]
];
```

## 🚀 Performance Optimization

### Caching
- **File-based caching** for database queries
- **Browser caching** via .htaccess headers
- **Gzip compression** for faster loading
- **Cache invalidation** on content updates

### Database Optimization
- **Indexed queries** for fast data retrieval
- **Database views** for complex queries
- **Connection pooling** via PDO
- **Query caching** for repeated requests

### Frontend Optimization
- **Minified assets** (CSS/JS)
- **Image optimization** with fallbacks
- **Lazy loading** for better performance
- **CDN ready** structure

## 🛡️ Security Best Practices

### Server Security
```bash
# Hide PHP version
echo "expose_php = Off" >> /etc/php/7.4/apache2/php.ini

# Disable dangerous functions
echo "disable_functions = exec,shell_exec,system" >> /etc/php/7.4/apache2/php.ini

# Set secure session settings
echo "session.cookie_httponly = 1" >> /etc/php/7.4/apache2/php.ini
echo "session.cookie_secure = 1" >> /etc/php/7.4/apache2/php.ini
```

### Admin Protection
```apache
# Add to .htaccess in /admin/ directory
<RequireAll>
    Require ip 192.168.1.100  # Your IP
    Require ip 10.0.0.0/8     # Your network
</RequireAll>

# OR use basic auth
AuthType Basic
AuthName "DG SPORTS Admin"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

### Database Security
```sql
-- Create dedicated database user
CREATE USER 'dgsports'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON dg_sports.* TO 'dgsports'@'localhost';
FLUSH PRIVILEGES;
```

## 📊 API Documentation (Future)

### Planned Endpoints
```
GET /api/matches          # Get live matches
GET /api/channels         # Get active channels
GET /api/stats            # Get statistics
POST /api/log-view        # Log viewer activity
```

### Authentication
API will use token-based authentication with rate limiting.

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error:**
```
Error: Database connection failed
Solution: Check database credentials in .env file
```

**Permission Denied:**
```
Error: Cannot write to cache directory
Solution: chmod 777 cache/ logs/ uploads/
```

**Admin Login Issues:**
```
Error: Invalid credentials
Solution: Check default credentials or reset via database
```

**Player Not Loading:**
```
Error: Stream URL not accessible
Solution: Check CORS settings and stream URL validity
```

### Debug Mode
Enable debug mode for development:
```env
APP_DEBUG=true
APP_ENV=development
```

### Logging
Check logs in `/logs/` directory:
```bash
tail -f logs/activity_$(date +%Y-%m-%d).log
tail -f logs/php_errors.log
```

## 🔄 Updates & Maintenance

### Regular Maintenance
```bash
# Clear cache
rm -rf cache/*

# Clear old logs (keep last 30 days)
find logs/ -name "*.log" -mtime +30 -delete

# Backup database
mysqldump -u username -p dg_sports > backup_$(date +%Y%m%d).sql
```

### Database Maintenance
```sql
-- Optimize tables
OPTIMIZE TABLE matches, channels, settings, viewer_logs;

-- Clean old viewer logs (keep last 30 days)
DELETE FROM viewer_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

## 📝 Changelog

### Version 2.0.0 PHP (Current)
- ✅ Complete PHP rewrite
- ✅ MySQL database integration
- ✅ Secure admin authentication
- ✅ File-based caching system
- ✅ CRUD operations for content
- ✅ Enhanced security features
- ✅ Professional admin dashboard
- ✅ Mobile-responsive design
- ✅ Logo fallback system
- ✅ Activity logging

### Previous Versions
- **v1.x**: JavaScript-based version with localStorage

## 🤝 Support

For technical support and customization:
- **Developer:** DiziPortal.Com
- **Documentation:** See this README
- **Issues:** Check troubleshooting section

## 📄 License

This project is proprietary software developed by DiziPortal.Com. All rights reserved.

## 🎯 Future Enhancements

- RESTful API implementation
- WebSocket real-time updates
- User registration system
- Comment and rating system
- Advanced analytics dashboard
- Multi-language support
- CDN integration
- Redis caching support

---

**⚡ DG SPORTS - Professional Sports Streaming Platform**  
*Developed with ❤️ by DiziPortal.Com*
