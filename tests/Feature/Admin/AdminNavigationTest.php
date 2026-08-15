<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermissions(array $permissionSlugs): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::factory()->create(['slug' => $slug]);
            $role->permissions()->attach($permission);
        }

        $user->roles()->attach($role);

        return $user;
    }

    public function test_navigation_shows_dashboard_for_user_with_dashboard_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_navigation_hides_people_group_from_user_without_users_or_roles_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('People', false);
    }

    public function test_navigation_shows_people_group_for_user_with_users_view_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view', 'users.view']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('People', false);
        $response->assertSee('Users', false);
    }

    public function test_navigation_shows_administrators_link_for_super_admin(): void
    {
        $superAdmin = User::factory()->create(['is_active' => true, 'user_type' => 'admin']);
        $role = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $role->permissions()->attach(
            Permission::factory()->create(['slug' => 'admin.full-access'])
        );
        $superAdmin->roles()->attach($role);

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('People', false);
        $response->assertSee('Administrators', false);
        $response->assertSee('Users', false);
    }

    public function test_navigation_hides_system_group_from_user_without_system_permissions(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('System', false);
        $response->assertDontSee('Settings', false);
        $response->assertDontSee('Audit Logs', false);
    }
}
