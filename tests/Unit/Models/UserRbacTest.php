<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_check_role_by_slug(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'admin']);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('nonexistent'));
    }

    public function test_user_permission_check_returns_false_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasPermission('users.view'));
    }

    public function test_user_permission_check_returns_true_when_role_has_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'users.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasPermission('users.view'));
    }

    public function test_user_with_full_access_permission_bypasses_permission_checks(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'admin.full-access']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasPermission('users.view'));
        $this->assertTrue($user->hasPermission('anything'));
    }

    public function test_has_any_permission_returns_true_when_user_has_one_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'users.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasAnyPermission(['users.view', 'users.edit']));
    }

    public function test_has_all_permissions_returns_false_when_user_missing_one_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'users.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertFalse($user->hasAllPermissions(['users.view', 'users.edit']));
    }

    public function test_is_super_administrator_returns_true_for_super_admin_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'super-administrator']);
        $user->roles()->attach($role);

        $this->assertTrue($user->isSuperAdministrator());
    }

    public function test_is_super_administrator_returns_true_for_full_access_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'admin.full-access']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertTrue($user->isSuperAdministrator());
    }
}
