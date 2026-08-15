<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New contact message')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You received a new contact message from '.$this->name.' ('.$this->email.').')
            ->line('Subject: '.$this->subject)
            ->line($this->message)
            ->action('Go to admin dashboard', url('/admin'));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'New contact message from '.$this->name.' ('.$this->email.') — "'.$this->subject.'"',
            'action_text' => 'Open admin area',
            'action_url' => url('/admin'),
        ];
    }
}
