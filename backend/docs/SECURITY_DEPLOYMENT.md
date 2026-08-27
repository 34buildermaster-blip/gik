# Production security checklist

## Application

Set these values in the production `.env` file. Never commit that file.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.example.com

SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_LIFETIME=60
SECURITY_STAFF_2FA_REQUIRED=true
SECURITY_HSTS_ENABLED=true
```

Run the deployment caches after every release:

```bash
php artisan migrate --force
php artisan optimize
composer audit
```

## Malware scanning

Install and update ClamAV on the VPS before enabling fail-closed scanning:

```bash
sudo apt update
sudo apt install -y clamav clamav-daemon
sudo systemctl stop clamav-freshclam
sudo freshclam
sudo systemctl enable --now clamav-freshclam
```

Then configure Laravel:

```dotenv
SECURITY_UPLOAD_SCAN_ENABLED=true
SECURITY_UPLOAD_SCAN_REQUIRED=true
SECURITY_UPLOAD_SCAN_FAIL_CLOSED=true
SECURITY_REQUIRE_CLEAN_FILES=true
SECURITY_CLAMAV_BINARY=/usr/bin/clamscan
SECURITY_CLAMAV_TIMEOUT=120
SECURITY_QUARANTINE_DISK=local
SECURITY_QUARANTINE_PATH=quarantine
```

Confirm that `storage/app/private/quarantine` is writable by the PHP user and is
not served by Nginx or Apache.

Before enabling customer traffic, rescan every existing managed file and require
a successful exit code:

```bash
php artisan security:scan-stored-files
```

Files that are missing, infected, or could not be scanned remain unavailable
while `SECURITY_REQUIRE_CLEAN_FILES=true`.

## Initial staff accounts

Never place staff passwords in source code. For a controlled first provision,
set `SEED_STAFF_ACCOUNTS=true` and provide strong values for
`SEED_ADMIN_PASSWORD` and `SEED_INSPECTOR_PASSWORD`, run the seeder once, then
remove those values and disable staff seeding again. Both accounts are forced to
change their temporary password and enroll in 2FA before using management routes.

## Google Drive

Use a dedicated service account and share only the application root folder with
that account. The application requests the narrow `drive.file` scope:

```dotenv
GOOGLE_DRIVE_AUTH=service_account
GOOGLE_DRIVE_SCOPE=https://www.googleapis.com/auth/drive.file
GOOGLE_DRIVE_CREDENTIALS_PATH=storage/app/google/credentials.json
GOOGLE_DRIVE_FOLDER_ID=the_private_application_folder_id
```

If OAuth was previously authorized with the full Drive scope, revoke that grant,
issue a new refresh token with `drive.file`, and rotate the old client secret.

## Server

- Allow public inbound traffic only on ports 80 and 443. Redirect port 80 to HTTPS.
- Allow SSH by key only; disable root and password login.
- Restrict `.env`, Google credentials, storage, and database backups to the PHP/deploy user.
- Keep PHP, Composer dependencies, the OS, Nginx, and ClamAV signatures updated.
- Back up the database and Drive data to a second encrypted destination.
- Test restore procedures and 2FA recovery codes before launch.
