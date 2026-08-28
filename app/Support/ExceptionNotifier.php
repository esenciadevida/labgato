<?php

namespace App\Support;

use App\Mail\AdminErrorAlert;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ExceptionNotifier
{
    /**
     * Exceptions that represent normal client behavior and must never page an admin.
     *
     * @var array<class-string<Throwable>>
     */
    private const SKIPPED = [
        NotFoundHttpException::class,
        AccessDeniedHttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        AuthenticationException::class,
    ];

    /**
     * Send a rate-limited error alert to the configured admin email.
     *
     * No-op outside of the production environment, when no alert email is
     * configured, or for expected client-side exceptions.
     */
    public static function notify(Throwable $e, ?string $environment = null): void
    {
        $environment = $environment ?? app()->environment();

        if ($environment !== 'production') {
            return;
        }

        if (config('labgoto.security.alert_email', '') === '') {
            return;
        }

        $alertEmail = (string) config('labgoto.security.alert_email', '');

        foreach (self::SKIPPED as $class) {
            if ($e instanceof $class) {
                return;
            }
        }

        if ($e instanceof HttpException && $e->getStatusCode() < 500) {
            return;
        }

        $cooldown = (int) config('labgoto.security.alert_cooldown_seconds', 900);

        if ($cooldown > 0) {
            $key = 'error-alert:'.md5($e::class.':'.$e->getFile().':'.$e->getLine());

            if (! Cache::add($key, true, $cooldown)) {
                return;
            }
        }

        $request = request();

        Mail::to($alertEmail)->queue(new AdminErrorAlert(
            exceptionClass: $e::class,
            exceptionMessage: $e->getMessage(),
            file: $e->getFile(),
            line: $e->getLine(),
            url: $request->fullUrl(),
            method: $request->method(),
            userAgent: $request->userAgent(),
            userId: Auth::id(),
        ));
    }
}