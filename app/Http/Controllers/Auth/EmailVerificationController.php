<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        if (! config('labgoto.auth.email_verification', false)) {
            return redirect()->to(\App\Support\RedirectTarget::memberHome());
        }

        if ($request->user()->email_verified_at !== null) {
            return redirect()->to(\App\Support\RedirectTarget::memberHome());
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, AuditLogService $auditLog)
    {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->email))) {
            abort(403);
        }

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();

            $auditLog->log(
                action: 'auth.email.verified',
                resourceType: 'user',
                resourceId: $user->id,
            );
        }

        return redirect()->to(\App\Support\RedirectTarget::memberHome())->with('status', 'Your email address has been verified.');
    }

    public function resend(Request $request, AuditLogService $auditLog)
    {
        if (! config('labgoto.auth.email_verification', false)) {
            return redirect()->to(\App\Support\RedirectTarget::memberHome());
        }

        if ($request->user()->email_verified_at !== null) {
            return redirect()->to(\App\Support\RedirectTarget::memberHome());
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $request->user()->id,
                'hash' => sha1($request->user()->email),
            ],
        );

        Notification::route('mail', $request->user()->email)->notify(new VerifyEmail($verificationUrl));

        $auditLog->log(
            action: 'auth.email.verification_resend',
        );

        return back()->with('status', 'A new verification link has been sent.');
    }
}
