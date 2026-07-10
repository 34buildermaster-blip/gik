# 34 BM Construction Website

Company website for 34 BM Construction, built with Next.js for the public SEO-focused frontend and Laravel for the backend/API.

## Structure

- `frontend/` - Next.js website
- `backend/` - Laravel backend

## Local URLs

- Integrated preview: http://127.0.0.1:3000
- Admin via gateway: http://127.0.0.1:3000/admin
- Laravel backend direct: http://127.0.0.1:8000

## Run Integrated Dev

```powershell
.\scripts\dev-integrated.ps1
```

The script runs Laravel on port `8000` and Next.js on port `3000`. Use port `3000` in the browser to check frontend and backend together.
