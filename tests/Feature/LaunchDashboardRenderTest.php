<?php

namespace Tests\Feature;

use App\Models\LaunchSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaunchDashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_launch_dashboard_renders_with_member_chrome(): void
    {
        $user = User::factory()->member()->create(['is_active' => true]);
        $user->saveProfile(['professional_status' => 'professional']);
        $user->markOnboarded();

        LaunchSetting::query()->firstOrFail()->update(['main_site_status' => 'locked']);
        LaunchSetting::flushCache();

        $response = $this->actingAs($user)->get(route('launch.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('space-grotesk', false);   // brand fonts loaded
        $response->assertSee('LabGoTo_favicon', false); // favicon links
        $response->assertSee('Welcome,', false);
        $response->assertSee('ASCPI Challenge', false);
        $response->assertDontSee('data-tour="navigation"', false); // member nav bar removed
    }
}
