<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMember
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('home')->with('error', 'Please log in to continue.');
        }

        if (! auth()->user()->isMember()) {
            return redirect()->route('home')->with('error', 'You do not have access to the member area.');
        }

        return $next($request);
    }
}
