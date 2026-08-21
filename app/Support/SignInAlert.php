<?php

namespace App\Support;

use App\Mail\SignInNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SignInAlert
{
    /**
     * Send a sign-in alert email, at most once per user, IP, and day.
     * Mail failures are logged and never interrupt the request.
     */
    public static function send(User $user, string $ipAddress, string $userAgent): void
    {
        $throttleKey = 'signin_alert:'.$user->id.':'.sha1($ipAddress).':'.now()->format('Ymd');

        if (Cache::has($throttleKey)) {
            return;
        }

        Cache::put($throttleKey, true, now()->endOfDay());

        try {
            Mail::to($user->email)->send(new SignInNotification(
                $user,
                $ipAddress,
                $userAgent,
                now()->format('F j, Y \a\t g:i A T'),
            ));
        } catch (Throwable $e) {
            Log::warning('Sign-in alert could not be sent', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
