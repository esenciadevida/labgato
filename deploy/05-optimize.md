# Step 5: Optimize

Optimize the application for production performance.

## Cache Configuration

```bash
# Cache all configuration files
php artisan config:cache

# Verify configuration cache
php artisan config:clear  # Only if you need to make changes
```

## Cache Routes

```bash
# Cache all routes
php artisan route:cache

# Verify route cache
php artisan route:clear  # Only if you need to make changes
```

## Cache Views

```bash
# Compile and cache all Blade views
php artisan view:cache

# Verify view cache
php artisan view:clear  # Only if you need to make changes
```

## Cache Events (Optional)

```bash
# Cache event and listener mappings
php artisan event:cache
```

## Generate Sitemap

```bash
# Generate the XML sitemap
php artisan sitemap:generate

# Verify sitemap exists
ls -la public/sitemap.xml
```

## Create Storage Symlink

```bash
# Create the storage symlink for public access
php artisan storage:link

# Verify symlink exists
ls -la public/storage
```

## Verify Optimization

```bash
# Check that caches are working
php artisan about
```

## Clear Caches (If Needed)

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```
