<?php

namespace Tests;

use App\Models\LaunchSetting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The launch wall gates every web route. Tests exercise the open-site
        // experience by default; LaunchSystemTest flips it back to pre-launch.
        if (Schema::hasTable('launch_settings')) {
            LaunchSetting::query()->create(['main_site_status' => 'open']);
        }
    }
}
