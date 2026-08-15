<?php

use App\Exceptions\Ai\AiException;
use App\Exceptions\Ai\AiRateLimitException;
use App\Http\Middleware\AdminInactivityTimeout;
use App\Http\Middleware\CachePublicPages;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsActiveUser;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsMember;
use App\Http\Middleware\HoneypotProtection;
use App\Http\Middleware\LimitRequestSize;
use App\Http\Middleware\SecurityHeaders;
use App\Support\AiErrorMessage;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(__DIR__.'/../routes/auth.php');
            Route::middleware('web')
                ->group(__DIR__.'/../routes/admin.php');
            Route::middleware('web')
                ->group(__DIR__.'/../routes/member.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(LimitRequestSize::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            CachePublicPages::class,
        ]);

        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'active.user' => EnsureUserIsActiveUser::class,
            'ensure_user_is_active' => EnsureUserIsActive::class,
            'admin' => EnsureUserIsAdmin::class,
            'admin.inactivity' => AdminInactivityTimeout::class,
            'honeypot' => HoneypotProtection::class,
            'member' => EnsureUserIsMember::class,
            'permission' => EnsureUserHasPermission::class,
        ]);

        $middleware->redirectGuestsTo(function (): string {
            return route('auth.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*'),
        );

        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::validationError(
                    $e->errors(),
                    $e->getMessage()
                );
            }
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::unauthorized();
            }
        });

        $exceptions->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::notFound();
            }
        });

        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::notFound();
            }
        });

        $exceptions->renderable(function (InvalidSignatureException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::forbidden('Invalid signature.');
            }
        });

        $exceptions->renderable(function (AiException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof AiRateLimitException) {
                return ApiResponse::tooManyRequests(AiErrorMessage::for($e));
            }

            return ApiResponse::serverError(AiErrorMessage::for($e));
        });
    })->create();
