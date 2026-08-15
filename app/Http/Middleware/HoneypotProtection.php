<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HoneypotProtection
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->filled('website')) {
            return redirect()->back()
                ->with('success', 'Your request has been processed.');
        }

        return $next($request);
    }
}
