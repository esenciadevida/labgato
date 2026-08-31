<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\LaunchSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaunchDashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_launch_dashboard_lists_challenge_with_start_cta(): void
    {
        $user = User::factory()->member()->create(['is_active' => true]);
        $user->saveProfile(['professional_status' => 'professional']);
        $user->markOnboarded();

        Assessment::create([
            'title' => 'Engine Smoke Test',
            'slug' => 'engine-smoke-test',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 19,
            'total_questions' => 20,
            'max_attempts' => 1,
            'allow_practice' => true,
            'certificate_enabled' => true,
            'pass_percentage' => 70,
            'results_status' => 'available',
            'answer_review_status' => 'available',
            'leaderboard_status' => 'available',
            'opens_at' => null,
            'closes_at' => now()->addDay(),
        ]);

        LaunchSetting::query()->firstOrFail()->update([
            'main_site_status' => 'locked',
            'challenge_open' => true,
        ]);
        LaunchSetting::flushCache();

        $response = $this->actingAs($user)->get(route('launch.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('space-grotesk', false);   // brand fonts loaded
        $response->assertSee('LabGoTo_favicon', false); // favicon links
        $response->assertSee('Your Challenges', false);
        $response->assertSee('Start Exam', false);
        $response->assertSee('Engine Smoke Test', false);
        $response->assertSee('Challenge expires in', false);
        $response->assertSee('assessmentCountdown(' . now()->addDay()->timestamp . ',', false);
        $response->assertDontSee('data-tour="navigation"', false); // member nav bar removed
    }
}
