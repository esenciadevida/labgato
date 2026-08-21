<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
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

    public function test_admin_can_list_roles_with_permission(): void
    {
        $user = $this->createUserWithPermissions(['roles.view']);

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertStatus(200);
        $response->assertSee('Roles');
    }

    public function test_admin_can_create_role(): void
    {
        $user = $this->createUserWithPermissions(['roles.manage']);

        $response = $this->actingAs($user)->post(route('admin.roles.store'), [
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'A test role',
            'permissions' => [],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'slug' => 'test-role',
        ]);
    }

    public function test_admin_can_edit_role(): void
    {
        $user = $this->createUserWithPermissions(['roles.manage']);
        $role = Role::factory()->create(['slug' => 'editable-role']);

        $response = $this->actingAs($user)->put(route('admin.roles.update', $role), [
            'name' => 'Updated Role',
            'slug' => 'editable-role',
            'description' => 'Updated description',
            'permissions' => [],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Updated Role',
        ]);
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $user = $this->createUserWithPermissions(['roles.manage']);
        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);

        $response = $this->actingAs($user)->delete(route('admin.roles.destroy', $superAdminRole));

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_custom_role(): void
    {
        $user = $this->createUserWithPermissions(['roles.manage']);
        $role = Role::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_cannot_delete_role_with_assigned_users(): void
    {
        $user = $this->createUserWithPermissions(['roles.manage']);
        $role = Role::factory()->create();
        User::factory()->create()->roles()->attach($role);

        $response = $this->actingAs($user)->delete(route('admin.roles.destroy', $role));

        $response->assertStatus(403);
    }

    public function test_permission_matrix_saves_correctly(): void
    {
        $user = $this->createUserWithPermissions(['roles.manage']);
        $permission = Permission::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.roles.store'), [
            'name' => 'Matrix Role',
            'slug' => 'matrix-role',
            'description' => 'Test',
            'permissions' => [$permission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $role = Role::where('slug', 'matrix-role')->first();
        $this->assertTrue($role->permissions->contains($permission));
    }

    public function test_unauthorized_user_cannot_access_role_management(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }
}
