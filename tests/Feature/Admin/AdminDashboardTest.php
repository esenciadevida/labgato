<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermission(string $permissionSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => $permissionSlug]);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_dashboard_displays_authenticated_user_name(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_dashboard_displays_real_kpi_metrics(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Published Resources');
        $response->assertSee('Pending Review');
        $response->assertSee('Searches Today');
        $response->assertSee('AI Usage Today');
    }

    public function test_dashboard_does_not_display_fake_statistics(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Total Samples');
        $response->assertDontSee('Total Tests');
        $response->assertDontSee('Pending Results');
    }
}
