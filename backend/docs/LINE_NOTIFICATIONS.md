# LINE notification setup

The application supports secure LINE account linking through the LINE Messaging API. Customers never type a LINE ID manually.

## Account-link flow

1. The user opens their profile and taps `เปิด LINE เพื่อเชื่อมต่อ`.
2. The user adds the company's LINE Official Account and sends `เชื่อมบัญชี`.
3. The bot returns a single-use link that expires in 10 minutes.
4. The customer opens the link and signs in with their website account.
5. LINE sends a signed account-link event to the application webhook.
6. The application stores the Messaging API user ID and confirms the connection in LINE.

Customers, inspectors, and Admin accounts use the same secure linking flow. The
events available in profile settings are limited automatically by account role.

## Multiple-Admin routing

- Assign an `Admin ผู้ตรวจอนุมัติ` while creating or editing a project. New site
  updates from the assigned inspector are sent to this Admin.
- An Admin who enables `รับแจ้งเตือนจากทุกโครงการ` in profile settings also
  receives updates from every project. Use this for a supervisor or Super Admin.
- If a project has no assigned reviewing Admin, the system falls back to all
  Admin accounts so a submitted update cannot be left unnoticed.
- Every user can choose website, LINE, or email channels and can enable only the
  event types relevant to their role.
- Website notifications remain the safe default when LINE or SMTP credentials
  are unavailable.

## LINE Developers configuration

1. Create or select the company's LINE Official Account and Messaging API channel.
2. Enable webhooks for the channel.
3. Set the webhook URL to `https://YOUR_DOMAIN/api/line/webhook`.
4. Copy the Channel access token and Channel secret.
5. Copy the Official Account add-friend URL from LINE Official Account Manager.

## Production environment

```dotenv
APP_URL=https://YOUR_DOMAIN
PROJECT_LINE_NOTIFICATIONS=true
LINE_CHANNEL_ACCESS_TOKEN=replace-with-channel-access-token
LINE_CHANNEL_SECRET=replace-with-channel-secret
LINE_ADD_FRIEND_URL=https://line.me/R/ti/p/@your-account
```

Run these commands after updating the production environment:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
```

Use the `Verify` button in LINE Developers Console after deployment. The endpoint rejects requests with an invalid `X-Line-Signature` and never logs channel credentials or account-link nonces.

## Operational notes

- One LINE account can be linked to only one website account.
- Users can disconnect their own linked account, and Admin can disconnect a
  customer's linked account from customer management.
- Blocking the Official Account prevents delivery even while the website still shows the account as linked.
- The website notification remains available if LINE delivery fails.
- The webhook replies synchronously, so it does not depend on a queue worker on shared hosting.
