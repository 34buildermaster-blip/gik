# Google Drive media storage

The application stores uploaded media through one central service. It uses local
private storage by default and can switch to Google Drive without changing the
upload controllers.

## What is covered

- Project update images (private and permission checked)
- User profile images (private and permission checked)
- Article covers and editor media (public through the application)
- Site logo, footer logo, favicon, and social share image

Existing local files remain readable. Only new uploads use the selected driver.

## Recommended setup: Google Workspace Shared Drive

1. Create a Google Cloud project and enable Google Drive API.
2. Create a dedicated service account and download its JSON credentials once.
3. Create a Shared Drive or a dedicated folder named `34BM Website`.
4. Add the service account email as a Shared Drive member with Content manager
   access.
5. Put the JSON file at `storage/app/google/credentials.json`.
6. Copy the Shared Drive or destination folder ID from its Google Drive URL.
7. Set the following values in `.env`:

```dotenv
MEDIA_STORAGE_DRIVER=google
GOOGLE_DRIVE_AUTH=service_account
GOOGLE_DRIVE_CREDENTIALS_PATH=storage/app/google/credentials.json
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
```

The application creates category folders inside the configured folder.

## Alternative setup: regular Google account

For a regular Gmail or Google account, create an OAuth 2.0 web application and
authorize the company account once. Store the resulting refresh token only on
the application server.

```dotenv
MEDIA_STORAGE_DRIVER=google
GOOGLE_DRIVE_AUTH=oauth
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
```

## Activate the schema

```powershell
php artisan migrate
php artisan config:clear
```

Keep `MEDIA_STORAGE_DRIVER=local` until credentials are ready. Switching back to
`local` affects only new uploads; files already registered on Google Drive
continue to be served from Google Drive.

## Security notes

- Never commit credentials JSON, OAuth secrets, or refresh tokens.
- Do not set Drive files to "anyone with the link". Public website media is
  proxied through the application, while customer media is authorization
  checked.
- Back up the application database. Drive stores file bytes, while the database
  stores ownership, project links, permissions, and file IDs.
- Use resumable uploads for large files. The integration uploads in chunks and
  can be tuned with `GOOGLE_DRIVE_CHUNK_SIZE`.
