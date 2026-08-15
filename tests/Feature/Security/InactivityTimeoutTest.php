<?php

namespace Tests\Feature\Security;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactivityTimeoutTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithDashboard(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_admin_is_logged_out_after_inactivity_threshold(): void
    {
        $admin = $this->adminWithDashboard();

        $response = $this->actingAs($admin)
            ->withSession(['admin_last_activity' => now()->subMinutes(31)->timestamp])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('auth.login'));
        $response->assertSessionHas('status', 'Session expired due to inactivity.');
        $this->assertGuest();
    }

    public function test_active_admin_session_persists_under_threshold(): void
    {
        $admin = $this->adminWithDashboard();

        $response = $this->actingAs($admin)
            ->withSession(['admin_last_activity' => now()->subMinutes(10)->timestamp])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_first_request_initializes_last_activity_timestamp(): void
    {
        $admin = $this->adminWithDashboard();

        $this->actingAs($admin)->get(route('admin.dashboard'));

        $this->assertGreaterThan(0, (int) session('admin_last_activity'));
    }
}
