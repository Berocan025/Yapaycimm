# DG SPORTS - diziportal build (Developer: DiziPortal.Com)

Single-page sports streaming site with admin panel and PlayerJS integration. Built for shared cPanel hosting.

- Public SPA: `public/index.php`
- Admin Panel: `admin/index.php` (default admin: admin / admin123)
- APIs: `api/*` with CORS enabled
- Database: SQLite by default; switch to MySQL on cPanel via `includes/config.php`

Setup (local):
1. PHP 8.0+ with PDO SQLite/MySQL
2. Serve `public/` as web root or upload `public/*` to public_html and keep `api/`, `admin/`, `includes/`, `storage/` in the same level.
3. Ensure `storage/` and `public/uploads/` are writable.

MySQL (cPanel):
- Edit `includes/config.php` and set `$DB_DRIVER = 'mysql'` or environment `DB_DRIVER=mysql`
- Set `DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS`

PlayerJS:
- Uses `https://playerjs.com/playerjs.js` and supports HLS, TS, MP4. Player top-right logo is configurable in Admin (Settings -> Player Logo URL).

Security:
- Admin uses CSRF + session. APIs allow CORS for public GET operations.

Note:
- External stream CORS must be allowed by the source server. This app cannot change third-party headers.
