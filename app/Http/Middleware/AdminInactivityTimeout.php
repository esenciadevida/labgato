<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminInactivityTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $thresholdMinutes = (int) config('labgoto.security.admin_inactivity_minutes', 30);
        $session = $request->session();
        $lastActivity = (int) $session->get('admin_last_activity', 0);

        if ($lastActivity > 0 && (now()->timestamp - $lastActivity) > ($thresholdMinutes * 60)) {
            $session->forget('admin_last_activity');

            Auth::logout();
            $session->invalidate();
            $session->regenerateToken();

            return redirect()->route('auth.login')
                ->with('status', 'Session expired due to inactivity.');
        }

        $session->put('admin_last_activity', now()->timestamp);

        return $next($request);
    }
}
