<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LimitRequestSize
{
    protected const MAX_BYTES = 1048576; // 1 MB

    protected const MAX_MULTIPART_BYTES = 10485760; // 10 MB

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

        $limit = ($request->isMethod('POST') && $request->files->count() > 0)
            ? self::MAX_MULTIPART_BYTES
            : self::MAX_BYTES;

        if ($contentLength > $limit) {
            abort(413, 'The request payload is too large.');
        }

        return $next($request);
    }

    protected function shouldSkip(Request $request): bool
    {
        return $request->is($this->excludedPaths);
    }
}
