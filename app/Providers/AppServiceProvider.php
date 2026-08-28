<?php

namespace App\Providers;

use App\Auth\HashedRememberTokenUserProvider;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\Permission;
use App\Models\ReferenceRange;
use App\Models\Role;
use App\Models\Sop;
use App\Observers\PermissionObserver;
use App\Observers\PivotObserver;
use App\Observers\RoleObserver;
use App\Services\Ai\AiProviderService;
use App\Services\SearchService;
use App\Support\SearchResult;
use App\View\Composers\TaxonomyComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchService::class);
        $this->app->singleton(AiProviderService::class);
    }

    public function boot(SearchService $searchService): void
    {
        Auth::provider('hashed-tokens', function ($app, array $config) {
            return new HashedRememberTokenUserProvider($app['hash'], $config['model']);
        });

        Route::bind('passwordResetToken', function (string $value) {
            return $value;
        });

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('auth.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
        Pivot::observe(PivotObserver::class);

        View::composer(
            [
                'public.calculators.index',
                'public.reference-ranges.index',
                'public.interpretation.index',
                'public.knowledge.index',
                'public.tools.index',
                'public.sops.index',
                'components.public.search.form',
                'components.public.search.live-results',
            ],
            TaxonomyComposer::class,
        );

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
        Gate::define('knowledge.view', fn ($user) => $user->hasPermission('knowledge.view'));
        Gate::define('sops.view', fn ($user) => $user->hasPermission('sops.view'));
        Gate::define('interpretation.view', fn ($user) => $user->hasPermission('interpretation.view'));
        Gate::define('calculators.view', fn ($user) => $user->hasPermission('calculators.view'));
        Gate::define('reference-ranges.view', fn ($user) => $user->hasPermission('reference-ranges.view'));
        Gate::define('tools.view', fn ($user) => $user->hasPermission('tools.view'));
        Gate::define('questions.view', fn ($user) => $user->hasPermission('questions.view'));
        Gate::define('contact-messages.view', fn ($user) => $user->hasPermission('contact-messages.view'));
        Gate::define('certificates.view', fn ($user) => $user->hasPermission('certificates.view'));
        Gate::define('integrity.view', fn ($user) => $user->hasPermission('integrity.view'));
        Gate::define('assessments.manage', fn ($user) => $user->hasPermission('assessments.manage'));
        Gate::define('announcements.manage', fn ($user) => $user->hasPermission('announcements.manage'));
        Gate::define('popups.manage', fn ($user) => $user->hasPermission('popups.manage'));
        Gate::define('certificates.manage', fn ($user) => $user->hasPermission('certificates.manage'));
        Gate::define('certificates.download', fn ($user) => $user->hasPermission('certificates.download'));

        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute((int) config('labgoto.search.rate_limit_per_minute', 15))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('search-live', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });

        RateLimiter::for('contributions', function (Request $request) {
            return Limit::perHour(20)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->ip());
        });

        RateLimiter::for('two-factor-resend', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        $searchService->registerProvider('calculator', function (string $query, array $filters) {
            return $this->searchProviderResult(
                Calculator::query()
                    ->published()
                    ->search($query)
                    ->when(isset($filters['category']) && $filters['category'] !== '', function ($q) use ($filters) {
                        $q->whereHas('categories', fn ($c) => $c->where('slug', $filters['category']));
                    })
                    ->when(isset($filters['tag']) && $filters['tag'] !== '', function ($q) use ($filters) {
                        $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag']));
                    }),
                ['id', 'slug', 'name', 'short_description', 'published_at'],
                fn (Calculator $calculator) => new SearchResult(
                    type: 'calculator',
                    title: $calculator->name,
                    url: route('public.calculators.show', $calculator->slug),
                    description: $calculator->short_description,
                    categoryName: $calculator->categories->first()?->name,
                    tags: $calculator->tags->pluck('name')->all(),
                    publishedAt: $calculator->published_at,
                ),
            );
        });

        $searchService->registerProvider('reference-range', function (string $query, array $filters) {
            return $this->searchProviderResult(
                ReferenceRange::query()
                    ->published()
                    ->search($query)
                    ->when(isset($filters['category']) && $filters['category'] !== '', function ($q) use ($filters) {
                        $q->whereHas('categories', fn ($c) => $c->where('slug', $filters['category']));
                    })
                    ->when(isset($filters['tag']) && $filters['tag'] !== '', function ($q) use ($filters) {
                        $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag']));
                    }),
                ['id', 'slug', 'name', 'analyte_name', 'short_description', 'published_at'],
                fn (ReferenceRange $range) => new SearchResult(
                    type: 'reference-range',
                    title: $range->name,
                    url: route('public.reference-ranges.show', $range->slug),
                    description: $range->short_description,
                    categoryName: $range->categories->first()?->name,
                    tags: $range->tags->pluck('name')->all(),
                    publishedAt: $range->published_at,
                ),
            );
        });

        $searchService->registerProvider('interpretation', function (string $query, array $filters) {
            return $this->searchProviderResult(
                InterpretationResource::query()
                    ->published()
                    ->search($query)
                    ->when(isset($filters['category']) && $filters['category'] !== '', function ($q) use ($filters) {
                        $q->whereHas('categories', fn ($c) => $c->where('slug', $filters['category']));
                    })
                    ->when(isset($filters['tag']) && $filters['tag'] !== '', function ($q) use ($filters) {
                        $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag']));
                    }),
                ['id', 'slug', 'name', 'short_description', 'published_at'],
                fn (InterpretationResource $resource) => new SearchResult(
                    type: 'interpretation',
                    title: $resource->name,
                    url: route('public.interpretation.show', $resource->slug),
                    description: $resource->short_description,
                    categoryName: $resource->categories->first()?->name,
                    tags: $resource->tags->pluck('name')->all(),
                    publishedAt: $resource->published_at,
                ),
            );
        });

        $searchService->registerProvider('knowledge', function (string $query, array $filters) {
            return $this->searchProviderResult(
                KnowledgeResource::query()
                    ->published()
                    ->search($query)
                    ->when(isset($filters['category']) && $filters['category'] !== '', function ($q) use ($filters) {
                        $q->whereHas('categories', fn ($c) => $c->where('slug', $filters['category']));
                    })
                    ->when(isset($filters['tag']) && $filters['tag'] !== '', function ($q) use ($filters) {
                        $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag']));
                    }),
                ['id', 'slug', 'name', 'short_description', 'published_at'],
                fn (KnowledgeResource $resource) => new SearchResult(
                    type: 'knowledge',
                    title: $resource->name,
                    url: route('public.knowledge.show', $resource->slug),
                    description: $resource->short_description,
                    categoryName: $resource->categories->first()?->name,
                    tags: $resource->tags->pluck('name')->all(),
                    publishedAt: $resource->published_at,
                ),
            );
        });

        $searchService->registerProvider('tool', function (string $query, array $filters) {
            return $this->searchProviderResult(
                LaboratoryTool::query()
                    ->published()
                    ->search($query)
                    ->when(isset($filters['category']) && $filters['category'] !== '', function ($q) use ($filters) {
                        $q->whereHas('categories', fn ($c) => $c->where('slug', $filters['category']));
                    })
                    ->when(isset($filters['tag']) && $filters['tag'] !== '', function ($q) use ($filters) {
                        $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag']));
                    }),
                ['id', 'slug', 'name', 'short_description', 'published_at'],
                fn (LaboratoryTool $tool) => new SearchResult(
                    type: 'tool',
                    title: $tool->name,
                    url: route('public.tools.show', $tool->slug),
                    description: $tool->short_description,
                    categoryName: $tool->categories->first()?->name,
                    tags: $tool->tags->pluck('name')->all(),
                    publishedAt: $tool->published_at,
                ),
            );
        });

        $searchService->registerProvider('sop', function (string $query, array $filters) {
            return $this->searchProviderResult(
                Sop::query()
                    ->published()
                    ->search($query)
                    ->when(isset($filters['category']) && $filters['category'] !== '', function ($q) use ($filters) {
                        $q->whereHas('categories', fn ($c) => $c->where('slug', $filters['category']));
                    })
                    ->when(isset($filters['tag']) && $filters['tag'] !== '', function ($q) use ($filters) {
                        $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag']));
                    }),
                ['id', 'slug', 'name', 'short_description', 'published_at'],
                fn (Sop $sop) => new SearchResult(
                    type: 'sop',
                    title: $sop->name,
                    url: route('public.sops.show', $sop->slug),
                    description: $sop->short_description,
                    categoryName: $sop->categories->first()?->name,
                    tags: $sop->tags->pluck('name')->all(),
                    publishedAt: $sop->published_at,
                ),
            );
        });
    }

    private function searchProviderResult(Builder $builder, array $columns, callable $present): array
    {
        return [
            'results' => (clone $builder)
                ->select($columns)
                ->with(['categories', 'tags'])
                ->orderByDesc('published_at')
                ->limit(50)
                ->get()
                ->map($present),
            'total' => (clone $builder)->count(),
        ];
    }
}
