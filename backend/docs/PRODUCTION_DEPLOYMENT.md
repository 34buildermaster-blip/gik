# Production deployment runbook

The active application is a single Laravel deployment. Public pages, admin,
customer portal, and APIs use one domain and one PHP application. The preserved
`frontend/` Next.js project is a rollback reference and is not deployed.

## Host requirements

- PHP 8.3 or newer with PDO MySQL, cURL, GD, fileinfo, mbstring, OpenSSL, and XML
- MySQL 8 or MariaDB 10.6 or newer
- Composer 2
- Apache with `mod_rewrite` or Nginx
- HTTPS certificate
- Cron access for `php artisan schedule:run` when scheduled jobs are enabled
- 512 MB PHP memory recommended

The web document root must point to `backend/public`. Never expose the project
root, `.env`, `storage`, database backups, or Google credentials publicly.

## Production environment

Create a new production `APP_KEY` and production-only credentials. Never copy
the local SQLite database or commit `.env`.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
FRONTEND_URL=https://example.com
APP_DISPLAY_TIMEZONE=Asia/Bangkok

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=buildmaster
DB_USERNAME=buildmaster
DB_PASSWORD=replace-with-a-strong-password

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_LIFETIME=60

MEDIA_STORAGE_DRIVER=google
GOOGLE_DRIVE_AUTH=oauth
GOOGLE_DRIVE_FOLDER_ID=replace-with-folder-id
GOOGLE_DRIVE_SCOPE=https://www.googleapis.com/auth/drive.file
GOOGLE_DRIVE_CLIENT_ID=replace-with-client-id
GOOGLE_DRIVE_CLIENT_SECRET=replace-with-client-secret
GOOGLE_DRIVE_REFRESH_TOKEN=replace-with-refresh-token

SECURITY_STAFF_2FA_REQUIRED=true
SECURITY_UPLOAD_SCAN_ENABLED=true
SECURITY_UPLOAD_SCAN_REQUIRED=true
SECURITY_UPLOAD_SCAN_FAIL_CLOSED=true
SECURITY_REQUIRE_CLEAN_FILES=true
SECURITY_CLAMAV_BINARY=/usr/bin/clamscan
SECURITY_HSTS_ENABLED=true

MAIL_MAILER=smtp
MAIL_HOST=replace-with-smtp-host
MAIL_PORT=587
MAIL_USERNAME=replace-with-smtp-user
MAIL_PASSWORD=replace-with-smtp-password
MAIL_FROM_ADDRESS=replace-with-verified-sender
MAIL_FROM_NAME="34 Build Master"
```

If the selected shared host cannot install or run ClamAV, keep upload scanning
disabled only for staging and use the VPS plan before accepting sensitive
customer documents.

## Upload limits

The application accepts large project media. Keep PHP and the web server at the
same limit:

```ini
upload_max_filesize = 100M
post_max_size = 110M
memory_limit = 512M
max_execution_time = 180
```

For Nginx also set `client_max_body_size 110M;`.

## Release commands

```bash
cd /path/to/project/backend
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan storage:link
php artisan security:scan-stored-files
php artisan optimize
```

Ensure `storage` and `bootstrap/cache` are writable by PHP. No `npm install`,
Node.js service, PM2 process, or frontend reverse proxy is required.

## Launch verification

1. Confirm `/`, `/about`, `/house-designs`, `/blog`, `/api/site-settings`,
   `/login/admin`, `/admin`, and `/my-projects` over HTTPS.
2. Confirm `APP_DEBUG=false` and test the custom 404/500 pages.
3. Enroll every staff account in 2FA and store recovery codes offline.
4. Upload a harmless image and confirm WebP conversion, malware status, Drive
   storage, public rendering, and deletion from the admin interface.
5. Send a password-reset email and verify delivery.
6. Create an inspector update, approve it as admin, and verify the customer sees
   and is notified about only the approved update.
7. Submit the public contact and article comment forms, then verify moderation.
8. Verify database and Drive backup restoration before accepting customer data.
