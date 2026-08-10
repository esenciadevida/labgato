# LabGato Release Verification Checklist

Use this checklist to verify every release before and after going live. Work through every item and confirm each one before declaring the release successful.

## 1. Application Health

- [ ] Home page loads correctly over HTTPS.
- [ ] No PHP errors or warnings are displayed publicly.
- [ ] Login page loads and authentication works.
- [ ] Admin dashboard loads for authorized users.
- [ ] Public pages render correctly.
- [ ] API health endpoint responds (e.g. `GET /api/v1/health`).

## 2. Database & Storage

- [ ] Database migrations ran successfully.
- [ ] `php artisan migrate:status` reports all migrations as Ran.
- [ ] Storage symlink works (`public/storage` exists).
- [ ] Logs are being written to `storage/logs/`.
- [ ] Database connection is stable.

## 3. Assets & Performance

- [ ] CSS loads correctly.
- [ ] JavaScript loads correctly.
- [ ] Vite manifest loads correctly (`public/build/manifest.json`).
- [ ] Page load time is acceptable.
- [ ] No 404 errors for assets.

## 4. Security

- [ ] `.env` file is not publicly accessible.
- [ ] Admin routes are protected.
- [ ] Auth routes are protected.
- [ ] API endpoints return correct responses.
- [ ] Error pages do not expose stack traces.
- [ ] HTTPS is enforced.
- [ ] `APP_DEBUG` is `false`.

## 5. SEO

- [ ] `robots.txt` is accessible.
- [ ] `sitemap.xml` is accessible.
- [ ] Meta tags render correctly.
- [ ] Admin pages are noindex.

## 6. Cron & Queue

- [ ] Cron jobs are executing.
- [ ] Queue jobs are processed.
- [ ] Scheduler is running.

## 7. Logging & Audit

- [ ] Application logs are written.
- [ ] Audit logs are recorded.
- [ ] No sensitive data in logs.

---

## Quick Verification Commands

```bash
# Application status and environment
php artisan about

# Migration status
php artisan migrate:status

# Route registration
php artisan route:list

# Database connectivity
php artisan db:monitor

# Scheduler status
php artisan schedule:list

# Queue status
php artisan queue:status
```

## Quick URL Checks

- `https://yourdomain.com/` - public home page
- `https://yourdomain.com/up` - application health check
- `https://yourdomain.com/api/v1/health` - API health endpoint
- `https://yourdomain.com/robots.txt` - robots file
- `https://yourdomain.com/sitemap.xml` - sitemap
- `https://yourdomain.com/login` - authentication
- `https://yourdomain.com/admin/dashboard` - admin (requires login)
