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

        // Assessments already marked "available" release with the launch;
        // the notifier deduplicates so re-running is harmless.
        foreach (\App\Models\Assessment::query()->where('results_status', 'available')->get() as $assessment) {
            app(\App\Services\ResultsReleaseNotifier::class)->notifyReleased($assessment);
        }

        Log::info('Launch activated automatically', ['launch_at' => $settings->launch_at->toIso8601String()]);

        $this->info('Launch activated automatically.');

        return self::SUCCESS;
    }
}
