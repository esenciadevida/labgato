<?php

namespace App\Models;

use App\Notifications\ResetPasswordLink;
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
        'password_set_at',
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

        static::created(function (User $user) {
            UserProfile::firstOrCreate(['user_id' => $user->id]);
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
            'password_set_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'onboarded_at' => 'datetime',
            'tour_state' => 'array',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class)->withDefault();
    }

    public function saveProfile(array $attributes): UserProfile
    {
        return $this->profile()->updateOrCreate([], $attributes);
    }

    public function isStudent(): bool
    {
        return in_array($this->profile->professional_status, UserProfile::STUDENT_STATUSES, true);
    }

    public function isProfessional(): bool
    {
        return in_array($this->profile->professional_status, UserProfile::PROFESSIONAL_STATUSES, true);
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

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
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

    public function hasCustomPassword(): bool
    {
        return $this->password_set_at !== null;
    }

    public function hasOnboarded(): bool
    {
        return $this->onboarded_at !== null;
    }

    public function markOnboarded(): void
    {
        if ($this->onboarded_at === null) {
            $this->forceFill(['onboarded_at' => now()])->save();
        }
    }

    public function hasCompletedTour(string $key): bool
    {
        return ($this->tour_state ?? [])[$key] ?? false;
    }

    public function completeTour(string $key): void
    {
        $state = $this->tour_state ?? [];

        if (($state[$key] ?? false) === true) {
            return;
        }

        $state[$key] = true;

        $this->forceFill(['tour_state' => $state])->save();
    }

    public function needsTour(string $key): bool
    {
        return $this->hasOnboarded() && ! $this->hasCompletedTour($key);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordLink($token));
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
