<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionCacheService
{
    public function getUserPermissions(int $userId): array
    {
        $cacheKey = "user:{$userId}:permissions";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($userId) {
            $user = User::with('roles.permissions')->find($userId);

            if (! $user) {
                return [];
            }

            $permissions = collect();
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions->pluck('slug'));
            }

            return $permissions->unique()->values()->toArray();
        });
    }

    public function getUserRoles(int $userId): array
    {
        $cacheKey = "user:{$userId}:roles";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($userId) {
            $user = User::with('roles')->find($userId);

            if (! $user) {
                return [];
            }

            return $user->roles->pluck('slug')->toArray();
        });
    }

    public function invalidateUser(int $userId): void
    {
        Cache::forget("user:{$userId}:permissions");
        Cache::forget("user:{$userId}:roles");
    }

    public function invalidateAllUsersWithRole(int $roleId): void
    {
        $role = Role::with('users')->find($roleId);

        if ($role) {
            foreach ($role->users as $user) {
                $this->invalidateUser($user->id);
            }
        }
    }

    public function invalidateAllUsers(): void
    {
        $users = User::pluck('id');

        foreach ($users as $userId) {
            $this->invalidateUser($userId);
        }
    }
}
