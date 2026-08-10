<?php

namespace App\Models;

use App\Services\PermissionCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleSlug): bool
    {
        $cacheService = app(PermissionCacheService::class);
        $roles = $cacheService->getUserRoles($this->id);

        return in_array($roleSlug, $roles);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        $cacheService = app(PermissionCacheService::class);
        $permissions = $cacheService->getUserPermissions($this->id);

        if (in_array('admin.full-access', $permissions)) {
            return true;
        }

        return in_array($permissionSlug, $permissions);
    }

    public function hasAnyPermission(array $permissionSlugs): bool
    {
        $cacheService = app(PermissionCacheService::class);
        $permissions = $cacheService->getUserPermissions($this->id);

        if (in_array('admin.full-access', $permissions)) {
            return true;
        }

        return count(array_intersect($permissionSlugs, $permissions)) > 0;
    }

    public function hasAllPermissions(array $permissionSlugs): bool
    {
        $cacheService = app(PermissionCacheService::class);
        $permissions = $cacheService->getUserPermissions($this->id);

        if (in_array('admin.full-access', $permissions)) {
            return true;
        }

        return count(array_diff($permissionSlugs, $permissions)) === 0;
    }

    public function isSuperAdministrator(): bool
    {
        return $this->hasRole('super-administrator') || $this->hasPermission('admin.full-access');
    }
}
