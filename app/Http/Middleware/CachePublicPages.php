<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicPages
{
    private const SKIPPED_PREFIXES = [
        'admin.',
        'account.',
        'auth.',
        'member.',
        'dashboard.',
        'cron.',
        'contact.',
    ];

    private const NO_STORE_PREFIXES = [
        'certificates.',
    ];

    private const LONG_CACHE_ROUTES = [
        'robots',
        'sitemap',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Certificate verify/share pages reflect revocation and privacy state;
        // they must never be served stale by browsers or shared caches.
        $routeName = (string) $request->route()?->getName();

        foreach (self::NO_STORE_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                $response->headers->set('Cache-Control', 'no-store, private');
                return $response;
            }
        }

        if (! $this->shouldCache($request, $response)) {
            // Responses that carry fresh cookies must never be stored by shared caches.
            if ($response->headers->has('Set-Cookie')) {
                $response->headers->set('Cache-Control', 'no-store, private');
            }

            return $response;
        }

        $maxAge = in_array($routeName, self::LONG_CACHE_ROUTES, true) ? 86400 : 60;

        $response->headers->set('Cache-Control', "public, max-age={$maxAge}");

        return $response;
    }

    private function shouldCache(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        if (! $response->isSuccessful()) {
            return false;
        }

        if (auth()->check()) {
            return false;
        }

        $routeName = (string) $request->route()?->getName();

        foreach (self::SKIPPED_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return false;
            }
        }

        if ($request->is('api/*')) {
            return false;
        }

        if ($response->headers->has('Set-Cookie')) {
            return false;
        }

        return true;
    }
}
