# HostAtom Go deployment

This profile deploys the Laravel application to shared hosting without exposing
the application root inside `public_html`.

## Required PHP extensions

Select PHP 8.3 or newer in DirectAdmin and enable `curl`, `fileinfo`, `gd`,
`mbstring`, `openssl`, `pdo_mysql`, `xml`, and `zip`.

## Build the upload package

Run on the development computer from the `backend` directory:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/build-hostatom-release.ps1
```

The generated ZIP contains two sibling directories:

- `buildmaster`: private application code, dependencies, configuration, and storage.
- `public_html`: the only web-accessible files.

Upload the ZIP to the hosting account home directory and extract it there. Do
not move `buildmaster` into `public_html`.

## Production environment

Create `buildmaster/.env` and use the values supplied by the hosting account:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN
FRONTEND_URL=https://YOUR_DOMAIN
APP_DISPLAY_TIMEZONE=Asia/Bangkok

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=YOUR_DATABASE
DB_USERNAME=YOUR_DATABASE_USER
DB_PASSWORD=YOUR_DATABASE_PASSWORD

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

MEDIA_STORAGE_DRIVER=google
GOOGLE_DRIVE_AUTH=oauth
GOOGLE_DRIVE_FOLDER_ID=YOUR_FOLDER_ID
GOOGLE_DRIVE_SCOPE=https://www.googleapis.com/auth/drive.file
GOOGLE_DRIVE_CLIENT_ID=YOUR_CLIENT_ID
GOOGLE_DRIVE_CLIENT_SECRET=YOUR_CLIENT_SECRET
GOOGLE_DRIVE_REFRESH_TOKEN=YOUR_REFRESH_TOKEN

SECURITY_STAFF_2FA_REQUIRED=true
SECURITY_UPLOAD_SCAN_ENABLED=true
SECURITY_UPLOAD_SCAN_DRIVER=builtin
SECURITY_UPLOAD_SCAN_REQUIRED=true
SECURITY_UPLOAD_SCAN_FAIL_CLOSED=true
SECURITY_REQUIRE_CLEAN_FILES=true
SECURITY_HSTS_ENABLED=true

MAIL_MAILER=smtp
MAIL_HOST=YOUR_SMTP_HOST
MAIL_PORT=587
MAIL_USERNAME=YOUR_SMTP_USER
MAIL_PASSWORD=YOUR_SMTP_PASSWORD
MAIL_FROM_ADDRESS=YOUR_VERIFIED_EMAIL
MAIL_FROM_NAME="34 Build Master"

PROJECT_EMAIL_NOTIFICATIONS=true
PROJECT_LINE_NOTIFICATIONS=false
```

Generate a new production `APP_KEY`; never copy the local `.env` file to the
host. Add LINE credentials only after the domain and HTTPS are working.

## Run Artisan without SSH

When Terminal access is unavailable, create one-time DirectAdmin Cron Jobs for
the commands below. Replace `ACCOUNT` and the PHP path with the values shown by
the hosting control panel.

```bash
/usr/local/bin/php /home/ACCOUNT/buildmaster/artisan migrate --force
/usr/local/bin/php /home/ACCOUNT/buildmaster/artisan optimize
```

Run each job once, review its email output, and remove it immediately. Do not
leave migration jobs scheduled.

## Upload security profile

The `builtin` driver rejects executable signatures, PHP payloads, the EICAR
test signature, active PDF content, malformed Office archives, Office macros,
and embedded files. It records the result as `validated`, while ClamAV records
the stronger result as `clean`.

Keep the hosting malware scanner and WAF enabled. The built-in driver is a
strict application validation layer, not a full antivirus engine. Move to the
`clamav` driver when a VPS or a callable ClamAV binary is available.

## Final checks

1. Confirm the domain redirects to HTTPS and `APP_DEBUG` is false.
2. Confirm `public_html/index.php` loads and `/login/admin` opens.
3. Create the first Admin securely, change the temporary password, and enroll 2FA.
4. Upload one image, one PDF, and one DOCX, then confirm each reaches Google Drive.
5. Test password reset and an Admin notification through SMTP.
6. Complete LINE webhook setup after HTTPS is working.
