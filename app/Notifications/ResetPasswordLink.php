<?php

namespace App\Notifications;

use App\Mail\ResetPasswordEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResetPasswordLink extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): ResetPasswordEmail
    {
        $url = url(route('auth.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $passwordsConfig = config('auth.passwords.'.config('auth.defaults.passwords'));

        return (new ResetPasswordEmail(
            $notifiable,
            $url,
            ipAddress: request()?->ip() ?? 'unknown',
            expireMinutes: $passwordsConfig['expire'] ?? 60,
        ))
            ->to($notifiable->email);
    }
}
