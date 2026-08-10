# Step 6: Verify

Run the verification checklist to ensure the deployment is successful.

## Application Health

- [ ] Home page loads correctly over HTTPS
- [ ] No PHP errors or warnings are displayed publicly
- [ ] Login page loads and authentication works
- [ ] Admin dashboard loads for authorized users
- [ ] Public pages render correctly

## Database & Storage

- [ ] Database migrations ran successfully
- [ ] Storage symlink works
- [ ] Logs are being written to `storage/logs/`
- [ ] Database connection is stable

## Assets & Performance

- [ ] CSS loads correctly
- [ ] JavaScript loads correctly
- [ ] Vite manifest loads correctly
- [ ] Page load time is acceptable
- [ ] No 404 errors for assets

## Security

- [ ] `.env` file is not publicly accessible
- [ ] Admin routes are protected
- [ ] Auth routes are protected
- [ ] API endpoints return correct responses
- [ ] Error pages do not expose stack traces
- [ ] HTTPS is enforced

## SEO

- [ ] `robots.txt` is accessible
- [ ] `sitemap.xml` is accessible
- [ ] Meta tags render correctly
- [ ] Admin pages are noindex

## Cron & Queue

- [ ] Cron jobs are executing
- [ ] Queue jobs are processed
- [ ] Scheduler is running

## Logging & Audit

- [ ] Application logs are written
- [ ] Audit logs are recorded
- [ ] No sensitive data in logs

## Quick Verification Commands

```bash
# Check application status
php artisan about

# Verify routes
php artisan route:list

# Check database connection
php artisan db:monitor

# Test health endpoint
curl https://yourdomain.com/api/v1/health
```
