<?php

namespace App\Support;

use App\Models\LaunchSetting;

class RedirectTarget
{
    /**
     * Where authenticated members belong right now: their account dashboard
     * once the site is live, otherwise the pre-launch waiting room.
     */
    public static function memberHome(): string
    {
        return LaunchSetting::current()->isLive()
            ? route('account.dashboard')
            : route('launch.dashboard');
    }
}
