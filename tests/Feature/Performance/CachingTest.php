<?php

namespace Tests\Feature\Performance;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachingTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_cache_is_populated_after_first_lookup(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $service = app(PermissionCacheService::class);

        $this->assertSame(['dashboard.view'], $service->getUserPermissions($user->id));
        $this->assertTrue(Cache::has("user:{$user->id}:permissions"));
    }

    public function test_permission_cache_is_invalidated_when_role_changes(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $service = app(PermissionCacheService::class);
        $service->getUserPermissions($user->id);
        $this->assertTrue(Cache::has("user:{$user->id}:permissions"));

        $role->update(['name' => 'Changed Role Name']);

        $this->assertFalse(Cache::has("user:{$user->id}:permissions"));
    }

    public function test_permission_cache_is_invalidated_when_permission_changes(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $service = app(PermissionCacheService::class);
        $service->getUserPermissions($user->id);
        $this->assertTrue(Cache::has("user:{$user->id}:permissions"));

        $permission->update(['name' => 'Dashboard View']);

        $this->assertFalse(Cache::has("user:{$user->id}:permissions"));
    }
}
