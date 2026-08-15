<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSystemTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true, 'user_type' => 'admin']);
        $role = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $role->permissions()->attach(
            Permission::factory()->create(['slug' => 'admin.full-access'])
        );
        $user->roles()->attach($role);

        return $user;
    }

    private function adminWithPermissions(array $permissionSlugs): User
    {
        $user = User::factory()->create(['is_active' => true, 'user_type' => 'admin']);
        $role = Role::factory()->create();

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::factory()->create(['slug' => $slug]);
            $role->permissions()->attach($permission);
        }

        $user->roles()->attach($role);

        return $user;
    }

    public function test_super_admin_can_view_administrators_list(): void
    {
        $superAdmin = $this->superAdmin();
        User::factory()->count(2)->create(['user_type' => 'admin']);

        $response = $this->actingAs($superAdmin)->get(route('admin.administrators.index'));

        $response->assertOk();
        $response->assertSee('Administrators');
    }

    public function test_administrators_list_only_shows_admin_users(): void
    {
        $superAdmin = $this->superAdmin();
        $administrator = User::factory()->create(['user_type' => 'admin', 'name' => 'Admin Portal User']);
        $member = User::factory()->member()->create(['name' => 'Public Member User']);

        $response = $this->actingAs($superAdmin)->get(route('admin.administrators.index'));

        $response->assertOk();
        $response->assertSee('Admin Portal User');
        $response->assertDontSee('Public Member User');
    }

    public function test_super_admin_can_create_administrator(): void
    {
        $superAdmin = $this->superAdmin();
        $role = Role::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.administrators.store'), [
            'name' => 'New Administrator',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'user_type' => 'admin',
        ]);
    }

    public function test_super_admin_can_edit_administrator(): void
    {
        \Illuminate\Support\Facades\Cache::flush();

        $superAdmin = $this->superAdmin();
        $administrator = User::factory()->create(['user_type' => 'admin', 'name' => 'Original Name']);
        $role = Role::factory()->create();

        $response = $this->actingAs($superAdmin)->put(route('admin.administrators.update', $administrator), [
            'name' => 'Renamed Administrator',
            'email' => $administrator->email,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'name' => 'Renamed Administrator',
        ]);
    }

    public function test_super_admin_can_deactivate_administrator(): void
    {
        $superAdmin = $this->superAdmin();
        $administrator = User::factory()->create(['user_type' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.administrators.toggle-active', $administrator));

        $response->assertRedirect(route('admin.administrators.index'));

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'is_active' => false,
        ]);
    }

    public function test_non_super_admin_cannot_manage_administrators(): void
    {
        $admin = $this->adminWithPermissions(['users.view', 'users.create']);

        $response = $this->actingAs($admin)->get(route('admin.administrators.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_users_list(): void
    {
        $superAdmin = $this->superAdmin();
        User::factory()->member()->create(['name' => 'Registered Member']);

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Registered Member');
    }

    public function test_users_list_only_shows_members(): void
    {
        $admin = $this->adminWithPermissions(['users.view']);
        User::factory()->create(['user_type' => 'admin', 'name' => 'Staff Administrator']);
        User::factory()->member()->create(['name' => 'Ordinary Member']);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Ordinary Member');
        $response->assertDontSee('Staff Administrator');
    }

    public function test_super_admin_can_deactivate_user(): void
    {
        $superAdmin = $this->superAdmin();
        $member = User::factory()->member()->create(['is_active' => true]);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.users.toggle-active', $member));

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'is_active' => false,
        ]);
    }

    public function test_administrator_cannot_deactivate_themselves(): void
    {
        $superAdmin = $this->superAdmin();

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.administrators.toggle-active', $superAdmin));

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'is_active' => true,
        ]);
    }

    public function test_cannot_delete_last_super_admin(): void
    {
        $actor = $this->adminWithPermissions(['users.delete', 'admin.full-access']);
        $lastSuperAdmin = User::factory()->create(['user_type' => 'admin', 'is_active' => true]);
        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $lastSuperAdmin->roles()->attach($superAdminRole);

        $response = $this->actingAs($actor)
            ->delete(route('admin.administrators.destroy', $lastSuperAdmin));

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $lastSuperAdmin->id,
            'is_active' => true,
        ]);
    }

    public function test_administrator_and_user_scopes_separate_correctly(): void
    {
        User::factory()->count(2)->create(['user_type' => 'admin']);
        User::factory()->count(3)->create(['user_type' => 'member']);

        $this->assertSame(2, User::administrators()->count());
        $this->assertSame(3, User::users()->count());
    }

    public function test_user_model_terminology_helpers(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $member = User::factory()->create(['user_type' => 'member']);

        $this->assertTrue($admin->isAdministrator());
        $this->assertFalse($admin->isRegularUser());
        $this->assertTrue($member->isRegularUser());
        $this->assertFalse($member->isAdministrator());
    }
}
