<?php

namespace App\Models;

use App\Services\PermissionCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'user_type',
        'profile_slug',
        'bio',
        'google_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (blank($user->profile_slug)) {
                $user->profile_slug = static::generateUniqueProfileSlug($user->name ?? 'contributor');
            }
        });
    }

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

    public function sops()
    {
        return $this->hasMany(Sop::class, 'contributed_by');
    }

    public function knowledgeResources()
    {
        return $this->hasMany(KnowledgeResource::class, 'contributed_by');
    }

    public function interpretationResources()
    {
        return $this->hasMany(InterpretationResource::class, 'contributed_by');
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

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isMember(): bool
    {
        return $this->user_type === 'member';
    }

    public function isAdministrator(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isRegularUser(): bool
    {
        return $this->user_type === 'member';
    }

    public function scopeAdministrators($query)
    {
        return $query->where('user_type', 'admin');
    }

    public function scopeUsers($query)
    {
        return $query->where('user_type', 'member');
    }

    public function hasGoogleAccount(): bool
    {
        return $this->google_id !== null && $this->google_id !== '';
    }

    public static function generateUniqueProfileSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'contributor';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('profile_slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
