# Step 4: Database Migration

Run database migrations to set up the database schema.

## Method 1: SSH (Recommended)

```bash
# Connect via SSH
ssh your_username@your_host

# Navigate to application directory
cd /path/to/public_html

# Run migrations
php artisan migrate --force

# Verify migrations
php artisan migrate:status
```

## Method 2: phpMyAdmin

If SSH is not available:

1. Export your local database (excluding data if needed)
2. Log in to phpMyAdmin via Hostinger Panel
3. Select your production database
4. Import the SQL dump file
5. Run any pending migrations by visiting a route that triggers them

## Method 3: SQL Dump Import

```bash
# Export database locally
mysqldump -u username -p database_name > labgato_backup.sql

# Import via phpMyAdmin or MySQL CLI
mysql -u username -p database_name < labgato_backup.sql
```

## Verify Migrations

1. Check that all tables exist in the database
2. Verify the `users` table has the correct columns
3. Verify the `roles`, `permissions`, and pivot tables exist
4. Check that `audit_logs` table exists
5. Verify `settings`, `categories`, `tags` tables exist

## Seed Data (Optional)

```bash
# Run seeders (if needed for production)
php artisan db:seed --class=RolePermissionSeeder
```

**Note:** Do NOT run `AdminUserSeeder` in production. Create admin users manually or via the admin panel.
