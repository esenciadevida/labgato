<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
            $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://www.google-analytics.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "img-src 'self' data: blob: https://www.google-analytics.com https://www.googletagmanager.com",
                "font-src 'self' data: https://fonts.gstatic.com",
                "connect-src 'self' https://www.google-analytics.com https://analytics.google.com https://www.googletagmanager.com",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "upgrade-insecure-requests",
            ]));
            $response->headers->remove('X-Powered-By');

            if ($request->isSecure()) {
                $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
            }
        }

        return $response;
    }
}
