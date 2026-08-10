# LabGato Deployment Guide

This guide walks you through deploying LabGato to Hostinger shared hosting (PHP 8.4, MySQL/MariaDB).

Each step has a dedicated document in the [deploy/](deploy/) directory. Start with the README there for a quick overview:

- [Build Process](deploy/01-build.md)
- [Upload](deploy/02-upload.md)
- [Configure](deploy/03-configure.md)
- [Database Migration](deploy/04-migrate.md)
- [Optimize](deploy/05-optimize.md)
- [Verify](deploy/06-verify.md)
- [Cron Jobs](deploy/cron-jobs.md)

## 1. Prerequisites

- A Hostinger account with PHP 8.4 enabled.
- A MySQL database created via Hostinger Panel (note the host, name, user, and password).
- SSH access if available (recommended for migrations, caches, and cron jobs).
- FTP/SFTP access (or Hostinger File Manager) for uploading files.
- A local development environment with PHP 8.4, Composer, and Node.js.

## 2. Build Process

Build the production assets locally before uploading.

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Verify that `public/build/` exists and contains `manifest.json` plus the compiled CSS/JS assets.

See [deploy/01-build.md](deploy/01-build.md) for details.

## 3. Packaging

Create a production archive that includes the application code and excludes development files:

```bash
tar -czf labgato-production.tar.gz \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='tests' \
  --exclude='deploy' \
  --exclude='*.md' \
  --exclude='phpunit.xml' \
  .
```

Include the required directories:

- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/`
- `resources/`
- `routes/`
- `storage/` (directory structure only)
- `vendor/`
- `composer.json`
- `composer.lock`
- `.env.production.example`

Exclude development-only files such as `tests/`, `node_modules/`, `.git/`, and local `.env`.

## 4. Upload

Upload the archive to your Hostinger account via SFTP, FTP, or the File Manager, then extract it so the application root sits at the domain directory (for example `public_html/`).

Configure the document root of your domain to point to the `public/` directory inside the application root. This keeps framework code and `.env` outside the web root.

See [deploy/02-upload.md](deploy/02-upload.md) for the full upload and permissions steps.

## 5. Configuration

On the server:

```bash
cp .env.production.example .env
```

Edit `.env` and set real production values (database credentials, `APP_URL`, mail settings). Then generate an application key:

```bash
php artisan key:generate
```

Required production settings:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- `LOG_CHANNEL=stack`

See [deploy/03-configure.md](deploy/03-configure.md) for details.

## 6. Database Migration

Run migrations safely. The recommended method is SSH:

```bash
php artisan migrate --force
php artisan migrate:status
```

If SSH is not available, export your local schema and import it via phpMyAdmin.

Seed the base roles and permissions only (never create admin users automatically in production):

```bash
php artisan db:seed --class=RolePermissionSeeder --force
```

See [deploy/04-migrate.md](deploy/04-migrate.md) for details.

## 7. Optimization

Cache the application for production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan sitemap:generate
php artisan storage:link
```

See [deploy/05-optimize.md](deploy/05-optimize.md) for details.

## 8. Storage Link

Create the public storage symlink so uploaded files are accessible:

```bash
php artisan storage:link
```

Verify the symlink exists at `public/storage`.

## 9. Cron Jobs

Configure two cron jobs (one for the scheduler, one for the queue worker):

```bash
* * * * * cd /home/your_username/public_html && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/your_username/public_html && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1
```

See [deploy/cron-jobs.md](deploy/cron-jobs.md) for Hostinger Panel instructions.

## 10. Verification

Run the [Release Verification Checklist](RELEASE_CHECKLIST.md) to confirm the deployment is healthy:

- Home page loads over HTTPS.
- Login and admin dashboard work.
- Assets load without 404 errors.
- `robots.txt` and `sitemap.xml` are accessible.
- `.env` is not publicly accessible.
- Cron jobs and the queue are running.

If anything fails, follow the [Rollback Plan](ROLLBACK.md).
