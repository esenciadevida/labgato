<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberRegisteredNotification extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to LabGato')
            ->greeting('Welcome aboard, '.$notifiable->name.'!')
            ->line('Your LabGato member account has been created.')
            ->line('Explore our library of laboratory calculators, reference ranges, SOPs, and knowledge resources.')
            ->action('Go to your dashboard', url('/account/dashboard'))
            ->line('Ready to share your expertise? Submit your first contribution and help the laboratory community grow.');
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
