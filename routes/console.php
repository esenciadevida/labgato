<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('analytics:aggregate')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('sitemap:generate')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('backup:run --only-db')->dailyAt('02:00')->withoutOverlapping()->onOneServer();
Schedule::command('backup:run')->weeklyOn(0, '04:00')->withoutOverlapping()->onOneServer();
Schedule::command('content-images:prune --days=7')->dailyAt('03:30')->withoutOverlapping();
