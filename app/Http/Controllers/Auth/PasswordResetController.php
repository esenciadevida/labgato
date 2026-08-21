<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($request->email);
        $throttleKey = $email.'|'.$request->ip();
        $maxRequests = config('labgoto.security.password_reset_max_per_minute', 3);

        if (Cache::get("password_reset_{$throttleKey}", 0) >= $maxRequests) {
            throw ValidationException::withMessages([
                'email' => ['Too many password reset requests. Please try again later.'],
            ]);
        }

        Password::sendResetLink(['email' => $email]);

        Cache::increment("password_reset_{$throttleKey}");
        Cache::put("password_reset_{$throttleKey}", Cache::get("password_reset_{$throttleKey}", 0), now()->addMinute());

        return back()->with('status', 'If an account with that email exists, a password reset link has been sent.');
    }

    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request, AuditLogService $auditLog)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed', 'regex:/[a-zA-Z]/', 'regex:/[0-9]/'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'password_set_at' => now(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $auditLog->log(
                action: 'auth.password.reset_success',
            );

            return redirect()->route('auth.login')->with('status', 'Password reset successfully. You may now log in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
