# 12 — Deployment and Release Process

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Deployment Constraints, Technology Stack).
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Database Foundation document.
- The approved Testing Foundation document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The deployment strategy for LABGOTO on Hostinger shared hosting.
- The environment prerequisites and verification steps.
- The local/CI build process for frontend and backend assets.
- The file upload and placement strategy.
- The database migration and seeding strategy.
- The storage link and asset handling rules.
- The Cron and queue configuration requirements.
- The release verification checklist.
- The rollback strategy.

## Affects

This document affects:

- The Foundation Build Order document.
- All future release cycles.
- The environment configuration management.
- The backup and disaster recovery strategy.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a repeatable, safe, and documented process for deploying LABGOTO to Hostinger.
- Prevents common shared hosting deployment failures (e.g., exposing the `.env` file, missing symlinks, broken migrations).
- Ensures that frontend assets are pre-built and optimized before upload.
- Defines how to handle database changes safely in production.

---

# 1. Purpose

This document establishes the Deployment and Release Process for LABGOTO.

Deploying a Laravel application to shared hosting requires careful planning to ensure security, performance, and reliability. Because Hostinger does not provide Docker, Supervisor, or automated CI/CD pipelines by default, the deployment process must be manual or script-assisted, with strict verification steps.

This document ensures that every release is consistent, secure, and reversible.

---

# 2. Deployment Strategy Overview

LABGOTO will use a **Pre-Built Artifact Deployment** strategy.

1.  **Build Locally or in CI**: Compile frontend assets and install backend dependencies.
2.  **Package**: Create a production-ready archive (zip/tar).
3.  **Upload**: Transfer the archive to Hostinger via SFTP, FTP, or Git (if available).
4.  **Extract & Configure**: Unpack the files, configure the environment, and set permissions.
5.  **Migrate**: Run database migrations.
6.  **Optimize**: Cache configuration, routes, and views.
7.  **Verify**: Run the release verification checklist.

This strategy avoids running `npm install` or `composer install` on the production server, which is often restricted or slow on shared hosting.

---

# 3. Environment Prerequisites & Verification

Before the first deployment, the following must be verified on the Hostinger account.

## 3.1 PHP Version

- Ensure PHP 8.4 is selected for the domain/subdomain in hPanel.
- Verify required PHP extensions are enabled (PDO, mbstring, openssl, curl, intl, gd).

## 3.2 Database

- Create a MySQL database and user in hPanel.
- Note the database name, username, password, and host (usually `localhost`).
- Ensure the user has full privileges on the database.

## 3.3 SSH Access (If Available)

- Verify if SSH access is enabled for the hosting plan.
- If SSH is available, verify Composer and PHP CLI access.
- If SSH is not available, plan for file-upload-only deployment and manual migration execution (see Section 6).

## 3.4 Cron Jobs

- Verify if Cron Jobs are available in hPanel.
- Note the minimum interval allowed (e.g., every 1 minute, 5 minutes, or 1 hour).

## 3.5 Document Root

- Determine the document root for the domain (usually `public_html/`).
- Decide on the directory structure strategy (see Section 5).

---

# 4. Build Process (Local/CI)

The build process must be executed on a local machine or CI server with PHP 8.4 and Node.js installed.

## 4.1 Backend Build

1.  Ensure the codebase is on the release branch/tag.
2.  Run `composer install --no-dev --optimize-autoloader --no-interaction`.
3.  Verify that the `vendor/` directory is generated correctly.

## 4.2 Frontend Build

1.  Run `npm ci` to install frontend dependencies.
2.  Run `npm run build` to compile Tailwind CSS, Alpine.js, and Vite assets.
3.  Verify that the `public/build/` directory is generated correctly.

## 4.3 Packaging

1.  Create a zip archive containing:
    - `app/`
    - `bootstrap/`
    - `config/`
    - `database/`
    - `public/` (including `build/`)
    - `resources/`
    - `routes/`
    - `storage/` (excluding `logs/` and `framework/cache/data/` if desired, but keep directory structure)
    - `vendor/`
    - `artisan`
    - `composer.json`
    - `composer.lock`
    - `.env.example` (Do NOT include `.env`)
2.  Name the archive with the release version (e.g., `LABGOTO-v1.0.0.zip`).

---

# 5. Upload & File Placement Strategy

## 5.1 Directory Structure Strategy

**Option A: Document Root Mapping (Preferred)**
If Hostinger allows changing the document root for the domain to a subfolder (e.g., `public/`), use this option.
- Upload the entire Laravel application to a folder like `LABGOTO-app/`.
- Point the domain's document root to `LABGOTO-app/public/`.

**Option B: Public HTML Mapping (Fallback)**
If the document root cannot be changed (fixed to `public_html/`):
- Upload the Laravel application core (`app/`, `bootstrap/`, `config/`, etc.) to a folder outside `public_html/` (e.g., `LABGOTO-core/`).
- Upload the contents of `public/` (including `index.php`, `.htaccess`, `build/`) directly into `public_html/`.
- Edit `public_html/index.php` to point to the correct paths for `bootstrap/app.php` and `vendor/autoload.php`.

## 5.2 Security Rules

- The `.env` file must NEVER be placed in `public_html/`.
- The `storage/` and `vendor/` directories must NOT be publicly accessible.
- Ensure `.htaccess` rules in `public_html/` deny access to sensitive files if Option B is used.

## 5.3 Permissions

- Set directories to `755` and files to `644`.
- Ensure `storage/` and `bootstrap/cache/` are writable by the web server user (usually `775` or `755` depending on Hostinger config).

---

# 6. Database Migration Strategy

## 6.1 With SSH Access

If SSH is available:

1.  Navigate to the application root.
2.  Copy `.env.example` to `.env` and configure production values.
3.  Run `php artisan key:generate`.
4.  Run `php artisan migrate --force`.
5.  Run seeders if required (e.g., `php artisan db:seed --class=RolePermissionSeeder`).

## 6.2 Without SSH Access

If SSH is not available:

1.  **Manual Migration**: Use a local database export (SQL dump) of the migrated schema and import it via phpMyAdmin.
2.  **Web-Based Migration (Risky)**: Create a temporary, highly secured route (e.g., `/deploy/migrate`) protected by a secret token in the URL or environment. Run migrations via this route, then immediately delete the route and file. *This is not recommended but may be necessary.*

## 6.3 Migration Safety

- Always backup the database before running migrations in production.
- Test migrations on a local or staging database first.
- Avoid destructive migrations (dropping columns/tables) without a rollback plan.

---

# 7. Asset & Storage Link Handling

## 7.1 Storage Link

Laravel requires a symlink from `public/storage` to `storage/app/public`.

- **With SSH**: Run `php artisan storage:link`.
- **Without SSH**: Manually create the symlink via the Hostinger File Manager or FTP. If symlinks are not supported, configure a custom disk to serve files directly from a public directory (requires code adjustment).

## 7.2 Asset Cache Busting

Vite generates hashed filenames for assets (e.g., `app-123456.css`). This ensures browsers fetch new assets after deployment.

- Ensure `public/build/` is uploaded completely.
- Clear browser cache or use incognito mode to verify assets load correctly.

---

# 8. Cron & Queue Configuration

## 8.1 Scheduler

If Cron is available, add the following entry to run the Laravel scheduler every minute:

`* * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1`

## 8.2 Queue Worker

Because Supervisor is unavailable, the queue worker must be triggered via Cron.

Add the following entry to run the queue worker every minute (or as allowed by the hosting plan):

`* * * * * cd /path/to/your/app && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1`

If Cron is limited to 1-hour intervals, the application must fall back to the `sync` queue driver or use a "poor man's cron" (triggering queue processing at the end of web requests, with strict rate limiting).

---

# 9. Release & Verification Checklist

After deployment, the following must be verified:

## 9.1 Application Health

- [ ] Home page loads correctly over HTTPS.
- [ ] No PHP errors or warnings are displayed publicly.
- [ ] Login page loads and authentication works.
- [ ] Admin dashboard loads for authorized users.

## 9.2 Database & Storage

- [ ] Database migrations ran successfully.
- [ ] Storage symlink works (images/files load).
- [ ] Logs are being written to `storage/logs/`.

## 9.3 Assets & Performance

- [ ] CSS and JS load correctly (check browser console for 404s).
- [ ] Vite manifest is loaded correctly.
- [ ] Page load time is acceptable.

## 9.4 Cron & Queue

- [ ] Cron jobs are executing (check logs or database `jobs` table).
- [ ] Queue jobs are processed (if applicable).

## 9.5 SEO & Security

- [ ] `robots.txt` is accessible.
- [ ] `sitemap.xml` is accessible (if generated).
- [ ] `.env` file is NOT publicly accessible.
- [ ] Admin routes are protected.

---

# 10. Rollback Strategy

If a deployment fails or introduces critical bugs:

## 10.1 Code Rollback

- Keep the previous release archive on the server.
- Replace the current application files with the previous release archive.
- Clear configuration, route, and view caches.

## 10.2 Database Rollback

- If migrations caused data loss or corruption, restore the database from the pre-migration backup.
- If migrations are reversible, run `php artisan migrate:rollback` (with caution).

## 10.3 Communication

- If the site is down, enable maintenance mode (`php artisan down`) if possible, or display a static maintenance page.
- Notify stakeholders of the rollback status.

---

# Document Completion Check

## Requirements Covered

- Deployment strategy overview defined.
- Environment prerequisites defined.
- Build process defined.
- Upload and file placement strategy defined.
- Database migration strategy defined.
- Asset and storage link handling defined.
- Cron and queue configuration defined.
- Release verification checklist defined.
- Rollback strategy defined.

## Decisions Established

- LABGOTO uses a Pre-Built Artifact Deployment strategy.
- Frontend assets are built locally/CI and uploaded.
- Database migrations run via SSH or manual SQL import.
- Queue workers are triggered via Cron with `--stop-when-empty`.
- Storage symlinks must be verified manually if SSH is unavailable.
- Rollback requires keeping previous release archives and database backups.

## Dependencies

- Approved Phase 01 Deployment Constraints.
- Approved Technology Stack.
- Approved Environment and Configuration.
- Approved Hostinger constraint.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/12_Deployment_and_Release_Process.md

It dictates the operational procedure for all future releases.

## Open Questions

1. Does the specific Hostinger plan support SSH access?
2. Does the specific Hostinger plan allow 1-minute Cron intervals?
3. Can the document root be changed to a subfolder?

## TBD Decisions

- TBD: Final verification of Hostinger plan capabilities.
- TBD: Final decision on migration method if SSH is unavailable.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE02-DOC12

## Context

LABGOTO requires a safe, repeatable deployment process for Hostinger shared hosting. This document defines the build, upload, migration, and verification steps required to release the application securely.

This prompt implements the documentation outcome of the Deployment and Release Process document.

## Objective

Create the official LABGOTO Deployment and Release Process document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding operational procedure for all future releases.

## Existing Architecture

Relevant approved decisions:

- LABGOTO uses Hostinger PHP 8.4 shared hosting.
- Frontend assets are pre-built locally or in CI.
- Queue workers are triggered via Cron.
- Database migrations must be run safely.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 11.
- Any existing deployment scripts or documentation.

## Files to Create

Create:

02_Technical_Foundation_Architecture/12_Deployment_and_Release_Process.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include real hosting credentials or SSH keys.
- Ensure that `.env` protection rules are clearly documented.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the build and upload process.
- The file defines the rollback strategy.

## Integration Requirements

This document must be used as the primary reference for the first production deployment and all future releases.

## Definition of Done

This implementation is complete when:

- The Deployment and Release Process file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.