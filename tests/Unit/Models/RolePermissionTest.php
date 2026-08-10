<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_can_check_permission_by_slug(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'users.view']);
        $role->permissions()->attach($permission);

        $this->assertTrue($role->hasPermission('users.view'));
        $this->assertFalse($role->hasPermission('users.edit'));
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $role = Role::factory()->create(['is_system_role' => true]);

        try {
            $role->delete();
        } catch (\Exception $e) {
            // Expected - deletion is blocked
        }

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_non_system_role_can_be_deleted(): void
    {
        $role = Role::factory()->create(['is_system_role' => false]);

        $role->delete();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_is_system_role_returns_correct_value(): void
    {
        $systemRole = Role::factory()->create(['is_system_role' => true]);
        $regularRole = Role::factory()->create(['is_system_role' => false]);

        $this->assertTrue($systemRole->isSystemRole());
        $this->assertFalse($regularRole->isSystemRole());
    }
}
