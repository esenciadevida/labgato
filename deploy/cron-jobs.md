# Cron Jobs Configuration

Configure cron jobs for the Laravel scheduler and queue worker on Hostinger.

## Scheduler

The Laravel scheduler runs every minute to execute scheduled tasks.

### SSH Method

```bash
# Edit crontab
crontab -e

# Add the following line
* * * * * cd /home/your_username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### Hostinger Panel Method

1. Log in to Hostinger Panel
2. Go to Advanced → Cron Jobs
3. Add a new cron job:
   - Command: `cd /home/your_username/public_html && php artisan schedule:run >> /dev/null 2>&1`
   - Frequency: Every minute

## Queue Worker

The queue worker processes background jobs.

### SSH Method

```bash
# Edit crontab
crontab -e

# Add the following line
* * * * * cd /home/your_username/public_html && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1
```

### Hostinger Panel Method

1. Log in to Hostinger Panel
2. Go to Advanced → Cron Jobs
3. Add a new cron job:
   - Command: `cd /home/your_username/public_html && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1`
   - Frequency: Every minute

## Verify Cron Jobs

```bash
# List current cron jobs
crontab -l

# Check if scheduler is running
php artisan schedule:list

# Test scheduler manually
php artisan schedule:run

# Check queue status
php artisan queue:status
```

## Troubleshooting

If cron jobs are not executing:

1. Check that the PHP binary path is correct
2. Verify the application path is correct
3. Check file permissions
4. Look for errors in `storage/logs/laravel.log`
5. Ensure the database connection is working
