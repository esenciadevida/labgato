<?php

namespace Tests\Feature\Member;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->member()->create(['is_active' => true]);
    }

    public function test_welcome_endpoint_marks_user_onboarded(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)
            ->postJson(route('account.onboarding.welcome'));

        $response->assertOk();
        $this->assertNotNull($user->fresh()->onboarded_at);
    }

    public function test_tour_completion_persists_state(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)
            ->postJson(route('account.onboarding.tour.complete', ['key' => 'member-dashboard']));

        $response->assertOk();
        $this->assertTrue($user->fresh()->hasCompletedTour('member-dashboard'));
    }

    public function test_invalid_tour_key_returns_validation_error(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)
            ->postJson(route('account.onboarding.tour.complete', ['key' => 'unknown-tour']));

        $response->assertStatus(404);
        $this->assertFalse($user->fresh()->hasCompletedTour('unknown-tour'));
    }

    public function test_guests_cannot_complete_onboarding(): void
    {
        $response = $this->post(route('account.onboarding.welcome'));

        $response->assertRedirect();
    }

    public function test_admins_cannot_access_member_onboarding(): void
    {
        $admin = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)
            ->post(route('account.onboarding.welcome'));

        $response->assertRedirect(route('home'));
    }

    public function test_dashboard_shows_welcome_modal_for_new_members(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertOk();
        $response->assertSee('data-welcome-url', false);
        $response->assertSee('Getting started');
    }

    public function test_onboarded_members_do_not_see_welcome_modal(): void
    {
        $user = $this->member();
        $user->markOnboarded();
        $user->completeTour('member-dashboard');

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertOk();
        $response->assertDontSee('data-welcome-url', false);
    }

    public function test_dashboard_tour_config_is_present_for_replay(): void
    {
        $user = $this->member();
        $user->markOnboarded();

        $response = $this->actingAs($user)->get(route('account.dashboard', ['tour' => 'member-dashboard']));

        $response->assertOk();
        $response->assertSee('data-tour-config', false);
        $response->assertSee('Navigate your hub');
    }

    public function test_dashboard_checklist_promotes_new_features(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertOk();
        $response->assertSee('Read an interpretation guide');
        $response->assertSee('Take an assessment');
        $response->assertSee('Level up');
    }
}
