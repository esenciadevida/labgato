<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('analytics:aggregate')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('launch:check')->everyMinute()->withoutOverlapping();
Schedule::command('exam:autosubmit')->everyMinute()->withoutOverlapping();
Schedule::command('sitemap:generate')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('backup:run --only-db')->dailyAt('02:00')->withoutOverlapping()->onOneServer();
Schedule::command('backup:run')->weeklyOn(0, '04:00')->withoutOverlapping()->onOneServer();
Schedule::command('content-images:prune --days=7')->dailyAt('03:30')->withoutOverlapping();
Schedule::command('session:gc')->dailyAt('04:00')->withoutOverlapping();
Schedule::command('backup:monitor')->dailyAt('05:00')->withoutOverlapping();

// Monitoring & housekeeping
Schedule::command('queue:health')->everySixHours()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->weeklyOn(0, '06:30')->withoutOverlapping();
Schedule::command('audit-logs:prune')->weeklyOn(0, '07:00')->withoutOverlapping();
Schedule::command('digest:weekly')->weeklyOn(1, '08:00')->withoutOverlapping();

// Shared-host friendly queue worker: drains whatever is pending then exits so the
// next minute's cron tick can start a fresh process.
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=90')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Heartbeat sentinel: writes the current time every minute so we can confirm
// the scheduler (via the Hostinger cron) is actually ticking.
Schedule::call(function () {
    file_put_contents(storage_path('app/scheduler-heartbeat.txt'), now()->toIso8601String());
})->everyMinute()->name('scheduler-heartbeat');
