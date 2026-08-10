<?php

namespace Tests\Feature\Database;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FoundationDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_tables_exist(): void
    {
        $expectedTables = [
            'users',
            'roles',
            'permissions',
            'role_user',
            'permission_role',
            'settings',
            'audit_logs',
            'categories',
            'tags',
            'sessions',
            'cache',
            'jobs',
            'failed_jobs',
            'password_reset_tokens',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' does not exist."
            );
        }
    }

    public function test_user_can_be_assigned_a_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->roles()->attach($role);

        $this->assertTrue($user->roles->contains($role));
    }

    public function test_role_can_be_assigned_a_permission(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();

        $role->permissions()->attach($permission);

        $this->assertTrue($role->permissions->contains($permission));
    }

    public function test_category_can_have_parent_and_children(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertTrue($parent->children->contains($child));
    }

    public function test_audit_log_does_not_use_updated_at(): void
    {
        $auditLog = AuditLog::factory()->create([
            'created_at' => now(),
        ]);

        $this->assertNull($auditLog->updated_at);
        $this->assertNotNull($auditLog->created_at);

        $this->assertEquals(null, AuditLog::UPDATED_AT);
    }

    public function test_role_permission_seeder_is_idempotent(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $roleCountFirst = Role::count();
        $permissionCountFirst = Permission::count();

        $this->seed(RolePermissionSeeder::class);

        $roleCountSecond = Role::count();
        $permissionCountSecond = Permission::count();

        $this->assertEquals($roleCountFirst, $roleCountSecond);
        $this->assertEquals($permissionCountFirst, $permissionCountSecond);
    }
}
