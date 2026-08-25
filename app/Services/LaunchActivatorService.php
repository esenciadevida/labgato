<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\LaunchSetting;

class LaunchActivatorService
{
    /**
     * Flip the main site live the moment the launch time has passed, without
     * depending on the scheduler/cron. Returns true when it activated.
     */
    public function activateIfDue(): bool
    {
        $settings = LaunchSetting::current();

        if (! $settings->auto_activate
            || $settings->launch_at === null
            || ! $settings->isPreLaunch()
            || now()->lt($settings->launch_at)) {
            return false;
        }

        $settings->update(['main_site_status' => 'open']);
        LaunchSetting::flushCache();

        foreach (Assessment::query()->where('results_status', 'available')->get() as $assessment) {
            app(ResultsReleaseNotifier::class)->notifyReleased($assessment);
        }

        return true;
    }
}
