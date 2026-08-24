<?php

namespace Tests\Feature\Launch;

use App\Models\LaunchSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaunchSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // TestCase seeds a live site by default; these tests exercise the
        // pre-launch experience.
        parent::setUp();

        LaunchSetting::query()->firstOrFail()->update(['main_site_status' => 'locked']);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $role->permissions()->attach([
            Permission::factory()->create(['slug' => 'dashboard.view']),
            Permission::factory()->create(['slug' => 'admin.full-access']),
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_pre_launch_guest_visiting_inner_page_is_redirected_home(): void
    {
        $this->get('/calculators')->assertRedirect('/');
    }

    public function test_pre_launch_home_shows_launch_wall(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Labgoto is coming')
            ->assertSee('A new digital platform for medical laboratory science.');
    }

    public function test_pre_launch_login_and_register_are_accessible(): void
    {
        $this->get(route('auth.login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_pre_launch_authenticated_member_sees_launch_dashboard(): void
    {
        $member = User::factory()->member()->create(['is_active' => true]);

        $this->actingAs($member)
            ->get(route('launch.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, '.$member->name)
            ->assertSee('You are registered for the Labgoto launch.')
            ->assertSee('Your Challenges')
            ->assertSee('bg-gradient-to-b from-primary-950', false);
    }

    public function test_pre_launch_super_admin_can_reach_inner_pages_and_launch_control(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/calculators')->assertOk();
        $this->actingAs($admin)->get(route('admin.launch'))
            ->assertOk()
            ->assertSee('Pre-Launch');
    }

    public function test_registration_closed_blocks_register_page(): void
    {
        LaunchSetting::current()->update(['registration_open' => false]);
        LaunchSetting::flushCache();

        $this->get(route('register'))->assertRedirect('/');

        $this->followingRedirects()->get(route('register'))
            ->assertOk()
            ->assertSee('Registration opens soon');
    }

    public function test_pre_launch_member_login_lands_on_the_launch_dashboard(): void
    {
        $member = User::factory()->member()->create(['is_active' => true]);

        $this->post(route('login.submit'), [
            'email' => $member->email,
            'password' => 'password',
        ])->assertRedirect(route('launch.dashboard'));
    }

    public function test_pre_launch_registration_lands_on_the_launch_dashboard(): void
    {
        $this->post(route('register.submit'), [
            'name' => 'Early Member',
            'email' => 'early@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
            'professional_status' => 'student',
        ])->assertRedirect(route('launch.dashboard'));
    }

    public function test_pre_launch_home_redirects_authenticated_members_to_the_launch_dashboard(): void
    {
        $member = User::factory()->member()->create(['is_active' => true]);

        $this->actingAs($member)
            ->get('/')
            ->assertRedirect(route('launch.dashboard'));
    }

    public function test_live_site_shows_normal_home_and_allows_inner_pages(): void
    {
        LaunchSetting::current()->update(['main_site_status' => 'open']);
        LaunchSetting::flushCache();

        $this->get('/')->assertOk()->assertSee('Lab Calculators, Reference Ranges &amp; SOPs', false);
        $this->get('/calculators')->assertOk();
    }

    public function test_launch_check_activates_site_when_moment_passes(): void
    {
        $settings = LaunchSetting::current();
        $settings->update([
            'launch_at' => now()->subMinute(),
            'auto_activate' => true,
            'main_site_status' => 'locked',
        ]);
        LaunchSetting::flushCache();

        $this->artisan('launch:check')->assertSuccessful();

        $this->assertSame('open', LaunchSetting::current()->fresh()->main_site_status);
    }

    public function test_launch_check_does_not_activate_when_auto_activate_disabled(): void
    {
        LaunchSetting::current()->update([
            'launch_at' => now()->subMinute(),
            'auto_activate' => false,
            'main_site_status' => 'locked',
        ]);
        LaunchSetting::flushCache();

        $this->artisan('launch:check')->assertSuccessful();

        $this->assertSame('locked', LaunchSetting::current()->fresh()->main_site_status);
    }

    public function test_super_admin_can_update_launch_settings(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->put(route('admin.launch.update'), [
            'launch_date' => '2026-12-01',
            'launch_time' => '18:30',
            'timezone' => 'Africa/Lagos',
            'auto_activate' => '1',
            'registration_open' => '1',
            'challenge_open' => '0',
            'results_status' => 'locked',
            'answer_review_status' => 'available',
            'leaderboard_status' => 'locked',
            'main_site_status' => 'open',
        ]);

        $response->assertRedirect(route('admin.launch'));

        $settings = LaunchSetting::current()->fresh();

        $this->assertSame('2026-12-01 17:30', $settings->launch_at->format('Y-m-d H:i'));
        $this->assertTrue((bool) $settings->auto_activate);
        $this->assertFalse((bool) $settings->challenge_open);
        $this->assertSame('available', $settings->answer_review_status);
        $this->assertSame('open', $settings->main_site_status);
        $this->assertTrue($settings->isLive());
    }
}
