<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_admin_can_list_users_with_permission(): void
    {
        $user = $this->createUserWithPermissions(['users.view']);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Users');
    }

    public function test_super_admin_can_create_administrator(): void
    {
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $superAdminRole->permissions()->attach(
            Permission::factory()->create(['slug' => 'admin.full-access'])
        );
        $superAdmin->roles()->attach($superAdminRole);
        $role = Role::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.administrators.store'), [
            'name' => 'Test Administrator',
            'email' => 'testadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'testadmin@example.com',
            'user_type' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_member_user_edit_page(): void
    {
        $admin = $this->createUserWithPermissions(['users.edit']);
        $member = User::factory()->member()->create([
            'name' => 'Member Name',
            'email' => 'member@example.com',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $member));

        $response->assertStatus(200);
        $response->assertSee('Member Name');
        $response->assertSee('member@example.com');
    }

    public function test_admin_user_page_does_not_expose_editable_name_or_email(): void
    {
        $admin = $this->createUserWithPermissions(['users.edit']);
        $member = User::factory()->member()->create([
            'name' => 'Member Name',
            'email' => 'member@example.com',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $member));

        $response->assertDontSee('name="name"', false);
        $response->assertDontSee('name="email"', false);
        $response->assertDontSee('View public profile');
        $response->assertDontSee('Save Changes');
    }

    public function test_admin_cannot_change_member_name_or_email_via_removed_update_route(): void
    {
        $admin = $this->createUserWithPermissions(['users.edit']);
        $member = User::factory()->member()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $response = $this->actingAs($admin)->put('/admin/users/'.$member->id, [
            'name' => 'Updated Member Name',
            'email' => 'changed@example.com',
        ]);

        $this->assertSame(405, $response->getStatusCode());

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
    }

    public function test_administrator_cannot_deactivate_self(): void
    {
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $superAdminRole->permissions()->attach(
            Permission::factory()->create(['slug' => 'admin.full-access'])
        );
        $superAdmin->roles()->attach($superAdminRole);

        $response = $this->actingAs($superAdmin)->delete(route('admin.administrators.destroy', $superAdmin));

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'is_active' => true,
        ]);
    }

    public function test_cannot_deactivate_last_super_admin(): void
    {
        // Actor has full-access permission (counts as super admin) but not the role.
        $actor = $this->createUserWithPermissions(['admin.full-access']);

        // Target is the only active user holding the super-administrator role.
        $lastSuperAdmin = User::factory()->create(['is_active' => true]);
        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $lastSuperAdmin->roles()->attach($superAdminRole);

        $response = $this->actingAs($actor)->delete(route('admin.administrators.destroy', $lastSuperAdmin));

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $lastSuperAdmin->id,
            'is_active' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_access_user_management(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }
}
