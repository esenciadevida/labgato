<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\SignInAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(AuditLogService $auditLog)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', [
                'exception' => $e,
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'uri' => request()->fullUrl(),
            ]);

            return redirect()->route('login')->with('error', 'Google sign-in was not completed. Please try again.');
        }

        $email = strtolower($googleUser->getEmail() ?? '');
        $googleId = $googleUser->getId();

        if ($email === '') {
            return redirect()->route('login')->with('error', 'Google did not return an email address for your account.');
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if ($user->isAdmin()) {
                $auditLog->log(
                    action: 'auth.social.rejected_admin_email',
                );

                return redirect()->route('login')->with('error', 'This email is linked to a staff account and cannot be used with Google sign-in.');
            }

            if (! $user->is_active) {
                return redirect()->route('login')->with('error', 'Your account is no longer active.');
            }

            $user->update([
                'google_id' => $user->hasGoogleAccount() ? $user->google_id : $googleId,
                'last_login_at' => now(),
            ]);

            Auth::login($user);
            request()->session()->regenerate();

            $auditLog->log(
                action: 'auth.social.login',
                resourceType: 'user',
                resourceId: $user->id,
            );

            SignInAlert::send($user, request()->ip(), (string) request()->userAgent());

            return redirect()->route('home');
        }

        $user = User::create([
            'name' => $googleUser->getName() ?: 'Google User',
            'email' => $email,
            'password' => Str::password(32),
            'google_id' => $googleId,
            'user_type' => 'member',
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        Auth::login($user);
        request()->session()->regenerate();

        $auditLog->log(
            action: 'auth.social.register',
            resourceType: 'user',
            resourceId: $user->id,
        );

        SignInAlert::send($user, request()->ip(), (string) request()->userAgent());

        return redirect()->route('home');
    }
}
