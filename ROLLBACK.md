# Labgoto Rollback Plan

Use this plan if a release fails verification or introduces a critical issue. Act quickly, communicate clearly, and verify the rollback afterwards.

## 1. Code Rollback

1. Keep every previous release archive.
2. Put the application into maintenance mode if possible:
   ```bash
   php artisan down
   ```
3. Replace the current application files with the previous release:
   - Delete or archive the current release directory.
   - Extract the previous release archive into the same location.
   - Restore the production `.env` from the previous release backup.
   - Ensure file permissions are correct (`storage/` and `bootstrap/cache/` writable).
4. Clear all caches:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```
5. Bring the application back online:
   ```bash
   php artisan up
   ```

## 2. Database Rollback

1. Keep a full database backup (dump) before every release:
   ```bash
   mysqldump -u username -p database_name > pre_release_backup.sql
   ```
2. If a release introduced schema changes that break the application:
   - Restore from the pre-migration backup via phpMyAdmin or the MySQL CLI:
     ```bash
     mysql -u username -p database_name < pre_release_backup.sql
     ```
   - Only use `php artisan migrate:rollback` if you are certain the rollback is safe and reversible.
3. Verify the restored schema matches the previous release (`php artisan migrate:status`).
4. Never run data-destructive commands without a verified backup.

## 3. Communication

1. Enable maintenance mode so visitors see a friendly message instead of errors:
   ```bash
   php artisan down --retry=60
   ```
2. Notify stakeholders (owner, administrators, and affected users) that a rollback is in progress.
3. Record what failed, what was rolled back, and when.
4. Only disable maintenance mode after verification passes.

## 4. Verification

After rolling back, confirm the rollback actually worked:

1. Run the [Release Verification Checklist](RELEASE_CHECKLIST.md).
2. Run smoke tests:
   - Home page loads.
   - Login works.
   - Admin dashboard loads for authorized users.
   - Database connectivity is stable.
   - Assets load without 404 errors.
3. Confirm logs are written to `storage/logs/` and audit logs are recording.
4. Confirm no stack traces are exposed on error pages.

## Golden Rules

- Always keep the previous release archive and a database backup before deploying.
- Roll back the code and the database together when the schema changed.
- Never leave the application in maintenance mode without a plan to bring it back up.
- Verify before declaring the rollback complete.
