<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class MemberLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('member.login');
    }

    public function login(Request $request, AuditLogService $auditLog)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower($request->email);
        $throttleKey = $email.'|'.$request->ip();
        $maxAttempts = config('labgoto.security.login_max_attempts', 5);
        $decayMinutes = config('labgoto.security.login_decay_minutes', 15);

        if (Cache::get("member_login_attempts_{$throttleKey}", 0) >= $maxAttempts) {
            throw ValidationException::withMessages([
                'email' => ['Too many failed attempts. Please try again in '.$decayMinutes.' minutes.'],
            ]);
        }

        $credentials = [
            'email' => $email,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, false)) {
            Cache::increment("member_login_attempts_{$throttleKey}");
            Cache::put("member_login_attempts_{$throttleKey}", Cache::get("member_login_attempts_{$throttleKey}", 0), now()->addMinutes($decayMinutes));

            $auditLog->log(
                action: 'auth.login.failed',
            );

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            $auditLog->log(
                action: 'auth.login.failed_inactive',
            );

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($user->isAdmin()) {
            Auth::logout();

            $auditLog->log(
                action: 'auth.login.rejected_admin',
            );

            return redirect()->route('auth.login')->withErrors([
                'email' => 'Please use the staff login page.',
            ]);
        }

        Cache::forget("member_login_attempts_{$throttleKey}");

        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        $auditLog->log(
            action: 'auth.login.success',
        );

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request, AuditLogService $auditLog)
    {
        $auditLog->log(
            action: 'auth.logout',
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
