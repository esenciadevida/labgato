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
    ];

    private const LONG_CACHE_ROUTES = [
        'robots',
        'sitemap',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldCache($request, $response)) {
            return $response;
        }

        $routeName = (string) $request->route()?->getName();
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

        return true;
    }
}
