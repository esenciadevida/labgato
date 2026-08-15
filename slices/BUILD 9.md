LABGOTO/
├── .env.production.example
├── DEPLOYMENT.md
├── RELEASE_CHECKLIST.md
├── ROLLBACK.md
├── public/
│   └── .htaccess
├── deploy/
│   ├── README.md
│   ├── 01-build.md
│   ├── 02-upload.md
│   ├── 03-configure.md
│   ├── 04-migrate.md
│   ├── 05-optimize.md
│   ├── 06-verify.md
│   └── cron-jobs.md
└── storage/
    └── logs/
        └── .gitignore

IMPLEMENTATION PROMPT ID: LABGOTO-SLICE1-BUILD9

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1 through Build 8 are complete. The Laravel foundation, database schema, authentication, RBAC, Admin Portal foundation, Public Site foundation, API foundation, logging, error handling, testing, SEO, and accessibility verification are implemented.

We are now implementing Build 9: Deployment Preparation & First Release.

This is the final build of Slice 1: Application Foundation.

OBJECTIVE:
Prepare the application for deployment to Hostinger shared hosting and execute the first release.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- LABGOTO uses Hostinger PHP 8.4 shared hosting.
- Frontend assets are pre-built locally or in CI.
- Queue workers are triggered via Cron.
- Database migrations must be run safely.
- Session, cache, and queue drivers use database.
- No Redis.
- No Node.js on production server.
- No Docker.
- No Supervisor.
- Secrets must never be committed.
- Production debug mode must be disabled.
- HTTPS must be enforced.

FILES TO INSPECT:
- .env.example
- config/app.php
- config/database.php
- config/session.php
- config/cache.php
- config/queue.php
- config/logging.php
- public/.htaccess
- public/robots.txt
- public/sitemap.xml
- composer.json
- package.json
- vite.config.js
- DEPLOYMENT.md if it exists

FILES TO CREATE:
- .env.production.example
- DEPLOYMENT.md
- RELEASE_CHECKLIST.md
- ROLLBACK.md
- deploy/README.md
- deploy/01-build.md
- deploy/02-upload.md
- deploy/03-configure.md
- deploy/04-migrate.md
- deploy/05-optimize.md
- deploy/06-verify.md
- deploy/cron-jobs.md

FILES TO MODIFY:
- public/.htaccess
- .gitignore
- composer.json if production scripts are needed
- package.json if production build scripts are needed

TASK 1: PRODUCTION ENVIRONMENT TEMPLATE

Create .env.production.example.

This file must contain all required production environment variables with safe placeholder values.

Required variables:

APP_NAME="LABGOTO"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC
APP_LOCALE=en
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=warning
LOG_RETENTION_DAYS=14

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="LABGOTO"

AI_ENABLED=false
AI_PROVIDER=
GROQ_API_KEY=
AI_TIMEOUT_SECONDS=15

FILESYSTEM_DISK=local
MEDIA_MAX_UPLOAD_KB=2048

LOGIN_RATE_LIMIT_MAX_ATTEMPTS=5
LOGIN_RATE_LIMIT_DECAY_MINUTES=15
PASSWORD_RESET_EXPIRY_MINUTES=60
REAUTHORIZE_TIMEOUT_MINUTES=30

SEO_DEFAULT_TITLE="LABGOTO"
SEO_DEFAULT_DESCRIPTION="Professional laboratory toolkit for medical laboratory scientists, students, and technicians."
SEO_CANONICAL_DOMAIN=https://yourdomain.com

ANALYTICS_ENABLED=false
LABGOTO_SEED_ADMIN=false

Rules:
- Do not include real secrets.
- Use placeholder values.
- APP_DEBUG must be false.
- SESSION_SECURE_COOKIE must be true.
- APP_ENV must be production.

TASK 2: PRODUCTION .HTACCESS HARDENING

Modify public/.htaccess.

Ensure the following security rules are present:

1. Enable mod_rewrite.
2. Redirect all requests to index.php if file or directory does not exist.
3. Deny access to sensitive files:
   - .env
   - .env.*
   - composer.json
   - composer.lock
   - package.json
   - package-lock.json
   - artisan
   - .git
   - .gitignore
   - storage/
   - vendor/
   - bootstrap/
   - config/
   - database/
   - routes/
   - tests/
   - docs/
   - deploy/
4. Set security headers where supported:
   - X-Content-Type-Options: nosniff
   - X-Frame-Options: SAMEORIGIN
   - Referrer-Policy: strict-origin-when-cross-origin
5. Prevent directory listing.
6. Set appropriate cache headers for static assets.

Rules:
- Do not break Laravel routing.
- Do not expose application source files.
- Do not expose environment files.
- Do not expose storage directory.

TASK 3: DEPLOYMENT GUIDE

Create DEPLOYMENT.md.

This document must provide a comprehensive deployment guide for Hostinger shared hosting.

Sections:

1. Prerequisites
   - Hostinger account with PHP 8.4.
   - MySQL database created.
   - SSH access if available.
   - FTP/SFTP access.
   - Local development environment with PHP 8.4, Composer, and Node.js.

2. Build Process
   - Run composer install --no-dev --optimize-autoloader.
   - Run npm ci.
   - Run npm run build.
   - Verify public/build directory exists.

3. Packaging
   - Create production archive.
   - Include required directories.
   - Exclude development files.

4. Upload
   - Upload via SFTP, FTP, or Git.
   - Place files in correct directory.
   - Configure document root.

5. Configuration
   - Copy .env.production.example to .env.
   - Set real production values.
   - Generate APP_KEY.
   - Set correct file permissions.

6. Database Migration
   - Run migrations via SSH if available.
   - Alternative: import SQL dump via phpMyAdmin.

7. Optimization
   - Run config cache.
   - Run route cache.
   - Run view cache.

8. Storage Link
   - Create storage symlink.

9. Cron Jobs
   - Configure scheduler.
   - Configure queue worker.

10. Verification
    - Run release verification checklist.

TASK 4: DEPLOYMENT STEP DOCUMENTS

Create deploy/README.md.

This document should link to all deployment step documents.

Create deploy/01-build.md.

Document the local build process:
- composer install --no-dev --optimize-autoloader
- npm ci
- npm run build
- Verify build output

Create deploy/02-upload.md.

Document the upload process:
- Connect via SFTP or FTP.
- Upload application files.
- Configure document root.
- Set file permissions.

Create deploy/03-configure.md.

Document the configuration process:
- Copy .env.production.example to .env.
- Set production values.
- Generate APP_KEY.
- Verify configuration.

Create deploy/04-migrate.md.

Document the migration process:
- SSH method: php artisan migrate --force
- phpMyAdmin method: import SQL dump.
- Verify migrations.

Create deploy/05-optimize.md.

Document the optimization process:
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache
- Verify optimization.

Create deploy/06-verify.md.

Document the verification process:
- Run release verification checklist.
- Check application health.
- Check database connectivity.
- Check asset loading.
- Check error pages.

Create deploy/cron-jobs.md.

Document cron job configuration:

Scheduler:
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1

Queue worker:
* * * * * cd /path/to/app && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1

Rules:
- Use actual application path.
- Use actual PHP binary path.
- Verify cron execution.

TASK 5: RELEASE VERIFICATION CHECKLIST

Create RELEASE_CHECKLIST.md.

This document must provide a comprehensive checklist for verifying the release.

Sections:

1. Application Health
   - [ ] Home page loads correctly over HTTPS.
   - [ ] No PHP errors or warnings are displayed publicly.
   - [ ] Login page loads and authentication works.
   - [ ] Admin dashboard loads for authorized users.
   - [ ] Public pages render correctly.

2. Database & Storage
   - [ ] Database migrations ran successfully.
   - [ ] Storage symlink works.
   - [ ] Logs are being written to storage/logs/.
   - [ ] Database connection is stable.

3. Assets & Performance
   - [ ] CSS loads correctly.
   - [ ] JavaScript loads correctly.
   - [ ] Vite manifest loads correctly.
   - [ ] Page load time is acceptable.
   - [ ] No 404 errors for assets.

4. Security
   - [ ] .env file is not publicly accessible.
   - [ ] Admin routes are protected.
   - [ ] Auth routes are protected.
   - [ ] API endpoints return correct responses.
   - [ ] Error pages do not expose stack traces.
   - [ ] HTTPS is enforced.

5. SEO
   - [ ] robots.txt is accessible.
   - [ ] sitemap.xml is accessible.
   - [ ] Meta tags render correctly.
   - [ ] Admin pages are noindex.

6. Cron & Queue
   - [ ] Cron jobs are executing.
   - [ ] Queue jobs are processed.
   - [ ] Scheduler is running.

7. Logging & Audit
   - [ ] Application logs are written.
   - [ ] Audit logs are recorded.
   - [ ] No sensitive data in logs.

TASK 6: ROLLBACK PLAN

Create ROLLBACK.md.

This document must provide a rollback plan for failed deployments.

Sections:

1. Code Rollback
   - Keep previous release archive.
   - Replace current files with previous release.
   - Clear caches.

2. Database Rollback
   - Restore from pre-migration backup.
   - Run php artisan migrate:rollback if safe.

3. Communication
   - Enable maintenance mode if possible.
   - Notify stakeholders.

4. Verification
   - Verify rollback success.
   - Run smoke tests.

TASK 7: GITIGNORE VERIFICATION

Modify .gitignore.

Ensure the following are ignored:
- .env
- .env.production
- .env.local
- storage/logs/*
- storage/framework/cache/*
- storage/framework/sessions/*
- storage/framework/views/*
- node_modules/
- vendor/
- public/build/
- public/hot
- public/storage
- .DS_Store
- Thumbs.db

Rules:
- Do not ignore .env.example.
- Do not ignore .env.production.example.
- Do not ignore public/.htaccess.
- Do not ignore public/robots.txt.

DEFINITION OF DONE:

- .env.production.example exists with safe placeholder values.
- public/.htaccess is hardened for production.
- DEPLOYMENT.md provides comprehensive deployment guide.
- Deployment step documents exist.
- RELEASE_CHECKLIST.md provides comprehensive verification checklist.
- ROLLBACK.md provides rollback plan.
- Cron job configuration is documented.
- .gitignore is verified.
- No secrets are committed.
- Application is ready for first deployment.