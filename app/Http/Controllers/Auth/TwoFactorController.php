<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminTwoFactorService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function show(Request $request, string $token, AdminTwoFactorService $twoFactor): RedirectResponse|\Illuminate\Contracts\View\View
    {
        $pending = $twoFactor->pending($token);

        if ($pending === null) {
            return redirect()->route('auth.login')->withErrors([
                'email' => 'That sign-in code has expired. Please sign in again.',
            ]);
        }

        $user = User::find($pending['user_id']);

        return view('auth.two-factor', [
            'token' => $token,
            'email' => $user?->email,
        ]);
    }

    public function verify(Request $request, string $token, AdminTwoFactorService $twoFactor, AuditLogService $auditLog): RedirectResponse
    {
        if (! $twoFactor->enabled()) {
            return redirect()->route('auth.login');
        }

        $request->validate([
            'code' => ['required', 'regex:/^\d{6}$/'],
        ]);

        $user = $twoFactor->verify($token, $request->string('code'));

        if ($user === null) {
            $auditLog->log(action: 'auth.login.two_factor_failed');

            throw ValidationException::withMessages([
                'code' => ['That sign-in code is invalid or has expired.'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        $auditLog->log(action: 'auth.login.success');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resend(Request $request, string $token, AdminTwoFactorService $twoFactor, AuditLogService $auditLog): RedirectResponse
    {
        if (! $twoFactor->enabled()) {
            return redirect()->route('auth.login');
        }

        if (! $twoFactor->resend($token)) {
            return redirect()->route('auth.login')->withErrors([
                'email' => 'That sign-in code has expired. Please sign in again.',
            ]);
        }

        $auditLog->log(action: 'auth.login.two_factor_code_resent');

        return back()->with('status', 'A new sign-in code has been sent.');
    }
}