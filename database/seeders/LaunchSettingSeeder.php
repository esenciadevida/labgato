<?php

namespace Database\Seeders;

use App\Models\LaunchSetting;
use Illuminate\Database\Seeder;

class LaunchSettingSeeder extends Seeder
{
    public function run(): void
    {
        LaunchSetting::firstOrCreate([], [
            'launch_at' => now()->addDays(30)->setTimezone('Africa/Lagos')->setTime(20, 0),
            'timezone' => 'Africa/Lagos',
            'auto_activate' => true,
            'registration_open' => true,
            'challenge_open' => true,
            'results_status' => 'locked',
            'answer_review_status' => 'locked',
            'leaderboard_status' => 'locked',
            'main_site_status' => 'locked',
        ]);

        LaunchSetting::flushCache();
    }
}
