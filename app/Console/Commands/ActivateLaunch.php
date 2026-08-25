<?php

namespace App\Console\Commands;

use App\Models\LaunchSetting;
use App\Services\LaunchActivatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActivateLaunch extends Command
{
    protected $signature = 'launch:check';

    protected $description = 'Flip the main site live automatically when the launch moment arrives';

    public function handle(LaunchActivatorService $activator): int
    {
        if ($activator->activateIfDue()) {
            Log::info('Launch activated automatically', ['launch_at' => LaunchSetting::current()->launch_at?->toIso8601String()]);

            $this->info('Launch activated automatically.');
        }

        return self::SUCCESS;
    }
}
