<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\MemberRegisteredNotification;
use App\Services\AuditLogService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class MemberRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request, AuditLogService $auditLog)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(config('labgoto.security.password_min_length', 12))->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => $request->password,
            'is_active' => true,
            'user_type' => 'member',
        ]);

        $auditLog->log(
            action: 'auth.register.success',
            resourceType: 'user',
            resourceId: $user->id,
        );

        Auth::login($user);
        $request->session()->regenerate();

        $user->notify(new MemberRegisteredNotification());

        if (! config('labgoto.auth.email_verification', false)) {
            $user->forceFill(['email_verified_at' => now()])->save();

            return redirect()->route('account.dashboard');
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ],
        );

        Notification::route('mail', $user->email)->notify(new VerifyEmail($verificationUrl));

        return redirect()->route('verification.notice')->with('status', 'We emailed you a verification link.');
    }
}
