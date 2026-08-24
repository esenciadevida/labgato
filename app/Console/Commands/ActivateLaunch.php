<?php

namespace App\Console\Commands;

use App\Models\LaunchSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActivateLaunch extends Command
{
    protected $signature = 'launch:check';

    protected $description = 'Flip the main site live automatically when the launch moment arrives';

    public function handle(): int
    {
        $settings = LaunchSetting::current();

        if (! $settings->auto_activate
            || $settings->launch_at === null
            || ! $settings->isPreLaunch()
            || now()->lt($settings->launch_at)) {
            return self::SUCCESS;
        }

        $settings->update(['main_site_status' => 'open']);
        LaunchSetting::flushCache();

        Log::info('Launch activated automatically', ['launch_at' => $settings->launch_at->toIso8601String()]);

        $this->info('Launch activated automatically.');

        return self::SUCCESS;
    }
}
