<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivilegeEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_assign_roles_to_themselves_via_policy(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::factory()->create();
        $assignPerm = Permission::factory()->create(['slug' => 'users.assign-roles']);
        $adminRole->permissions()->attach($assignPerm);
        $admin->roles()->attach($adminRole);

        $this->assertTrue($admin->hasPermission('users.assign-roles'));

        $policy = new UserPolicy;
        $this->assertTrue($policy->assignRoles($admin), 'User with assign-roles permission should pass policy check');
    }

    public function test_non_super_admin_cannot_modify_super_admin(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::factory()->create(['slug' => 'admin-role']);
        $admin->roles()->attach($adminRole);

        $superAdmin = User::factory()->create();
        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $superAdmin->roles()->attach($superAdminRole);

        $this->assertTrue($superAdmin->isSuperAdministrator());
        $this->assertFalse($admin->isSuperAdministrator());
    }

    public function test_non_super_admin_cannot_assign_super_admin_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'users.assign-roles']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);

        $this->assertTrue($user->hasPermission('users.assign-roles'));
        $this->assertFalse($user->isSuperAdministrator());
    }

    public function test_user_without_full_access_cannot_modify_super_admin_user(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::factory()->create(['slug' => 'content-manager']);
        $editPerm = Permission::factory()->create(['slug' => 'users.edit']);
        $adminRole->permissions()->attach($editPerm);
        $admin->roles()->attach($adminRole);

        $superAdmin = User::factory()->create();
        $superAdminRole = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $superAdmin->roles()->attach($superAdminRole);

        $policy = new UserPolicy;
        $this->assertFalse($policy->update($admin, $superAdmin), 'Non-super-admin should not be able to update super-admin');
    }
}
