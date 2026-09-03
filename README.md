# 34 BM Construction Website

Company website and customer project portal for 34 BM Construction. The active application uses Laravel Blade for the public website, admin, API, and customer portal on one host.

## Structure

- `backend/` - active Laravel application, including the Blade public website
- `frontend/` - preserved Next.js baseline for reference and rollback only

## Local URLs

- Public website: http://127.0.0.1:8000
- Admin: http://127.0.0.1:8000/admin
- Customer portal: http://127.0.0.1:8000/my-projects

## Run Locally

```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

No Node.js process is required for the active website. The Next.js version remains available in Git tag `nextjs-baseline-2026-09`.
