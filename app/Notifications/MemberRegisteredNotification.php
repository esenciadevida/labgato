<?php

namespace App\Notifications;

use App\Mail\WelcomeEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberRegisteredNotification extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): WelcomeEmail
    {
        return (new WelcomeEmail($notifiable))
            ->to($notifiable->email);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Welcome to LabGato! Your member account is ready.',
            'action_text' => 'Go to dashboard',
            'action_url' => url('/account/dashboard'),
        ];
    }
}
