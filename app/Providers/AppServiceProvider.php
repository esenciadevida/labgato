<?php

namespace App\Providers;

use App\Models\Calculator;
use App\Models\Permission;
use App\Models\Role;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Services\SearchService;
use App\Support\SearchResult;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(SearchService $searchService): void
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

        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute((int) config('labgato.search.rate_limit_per_minute', 15))
                ->by($request->user()?->id ?: $request->ip());
        });

        $searchService->registerProvider('calculator', function (string $query, array $filters) {
            return Calculator::query()
                ->published()
                ->with(['categories', 'tags'])
                ->search($query)
                ->when(isset($filters['category']) && $filters['category'] !== '', function ($q) use ($filters) {
                    $q->whereHas('categories', fn ($c) => $c->where('slug', $filters['category']));
                })
                ->when(isset($filters['tag']) && $filters['tag'] !== '', function ($q) use ($filters) {
                    $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag']));
                })
                ->orderByDesc('published_at')
                ->limit(50)
                ->get()
                ->map(function (Calculator $calculator) {
                    return new SearchResult(
                        type: 'calculator',
                        title: $calculator->name,
                        url: route('public.calculators.show', $calculator->slug),
                        description: $calculator->short_description,
                        categoryName: $calculator->categories->first()?->name,
                        tags: $calculator->tags->pluck('name')->all(),
                        publishedAt: $calculator->published_at,
                    );
                });
        });
    }
}
