# Production deployment runbook

This project must run on a VPS with Next.js, Laravel/PHP-FPM, a production
database, and Nginx. Do not use `php artisan serve` in production.

## Required services

- Nginx with HTTPS and HTTP-to-HTTPS redirect
- PHP 8.3 FPM with PDO MySQL, cURL, GD, fileinfo, mbstring, OpenSSL, and XML
- MySQL or MariaDB
- Node.js 22 LTS with a process manager such as systemd or PM2
- Composer
- ClamAV with current signatures

## Backend environment

Use a new production `APP_KEY` and production-only credentials. Never copy the
local SQLite database or commit `.env`.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
FRONTEND_URL=https://example.com
APP_DISPLAY_TIMEZONE=Asia/Bangkok
TRUSTED_PROXIES=127.0.0.1

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
```

## Frontend environment

`BACKEND_URL` must resolve to the Laravel HTTP upstream from the Next.js
process. Leave `NEXT_PUBLIC_API_URL` empty when Nginx exposes Laravel `/api`
routes on the same public domain.

```dotenv
NEXT_PUBLIC_SITE_URL=https://example.com
NEXT_PUBLIC_API_URL=
BACKEND_URL=http://127.0.0.1:8000
```

Configure a private Nginx listener on `127.0.0.1:8000` with its document root at
`backend/public` and PHP requests handled by PHP-FPM. The public HTTPS server
must send these prefixes to that Laravel listener:

```text
/api /admin /login /logout /register /forgot-password /reset-password
/change-password /two-factor-challenge /my-projects /notifications /media
/project-documents /project-issue-media /project-media /uploads /css
```

Send all other routes to the managed Next.js process on `127.0.0.1:3000`.

## Upload limits

Keep all layers at the same limit. The application currently communicates a
100 MB request limit, so configure at least:

```ini
; php.ini
upload_max_filesize = 100M
post_max_size = 110M
```

```nginx
client_max_body_size 110M;
```

## Release commands

```bash
cd /var/www/gik/backend
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan security:scan-stored-files
php artisan optimize

cd /var/www/gik/frontend
npm ci
npm run build:admin-css
npm run build
```

Restart PHP-FPM and the managed Next.js process after a successful build.

## Launch verification

1. Confirm `/`, `/api/site-settings`, `/login/admin`, `/admin`, and
   `/my-projects` through the public HTTPS domain.
2. Enroll every staff account in 2FA and store recovery codes offline.
3. Upload a harmless image and confirm it is scanned, stored in Drive, rendered
   on the frontend, and removable from the admin interface.
4. Send a password-reset email and verify delivery.
5. Create an inspector update, approve it as admin, and verify the customer sees
   only the approved update.
6. Verify database and Drive backup restoration before accepting customer data.
