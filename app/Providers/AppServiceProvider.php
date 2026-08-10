<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('passwordResetToken', function (string $value) {
            return $value;
        });

        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);

        Gate::before(function ($user, $ability) {
            if ($user->hasPermission('admin.full-access')) {
                return true;
            }
        });

        Gate::define('dashboard.view', fn ($user) => $user->hasPermission('dashboard.view'));
        Gate::define('users.view', fn ($user) => $user->hasPermission('users.view'));
        Gate::define('users.create', fn ($user) => $user->hasPermission('users.create'));
        Gate::define('users.edit', fn ($user) => $user->hasPermission('users.edit'));
        Gate::define('users.delete', fn ($user) => $user->hasPermission('users.delete'));
        Gate::define('users.assign-roles', fn ($user) => $user->hasPermission('users.assign-roles'));
        Gate::define('roles.view', fn ($user) => $user->hasPermission('roles.view'));
        Gate::define('roles.manage', fn ($user) => $user->hasPermission('roles.manage'));
        Gate::define('taxonomy.view', fn ($user) => $user->hasPermission('taxonomy.view'));
        Gate::define('taxonomy.manage', fn ($user) => $user->hasPermission('taxonomy.manage'));
        Gate::define('audit-logs.view', fn ($user) => $user->hasPermission('audit-logs.view'));
        Gate::define('analytics.view', fn ($user) => $user->hasPermission('analytics.view'));
        Gate::define('settings.view', fn ($user) => $user->hasPermission('settings.view'));
        Gate::define('settings.manage', fn ($user) => $user->hasPermission('settings.manage'));
    }
}
