<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! auth()->user()->is_active) {
            $auditLog = app(AuditLogService::class);
            $auditLog->log(
                action: 'auth.session.blocked_inactive',
                request: $request,
            );

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->with('error', 'Your account is no longer active.');
        }

        return $next($request);
    }
}
