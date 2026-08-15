<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LimitRequestSize
{
    protected const MAX_BYTES = 1048576; // 1 MB

    protected array $excludedPaths = [
        'livewire/*',
        'admin/reference-ranges/import*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $contentLength = (int) $request->header('Content-Length');

        if ($contentLength > self::MAX_BYTES) {
            abort(413, 'The request payload is too large.');
        }

        return $next($request);
    }

    protected function shouldSkip(Request $request): bool
    {
        if ($request->isMethod('POST') && $request->files->count() > 0) {
            return true;
        }

        return $request->is($this->excludedPaths);
    }
}
