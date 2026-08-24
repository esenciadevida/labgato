<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use App\Models\User;
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
        public ?ContactMessage $contactMessage = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $recipientName = $notifiable instanceof User ? $notifiable->name : null;
        $greeting = $recipientName ? 'Hello '.$recipientName.',' : 'Hello,';

        $mail = (new MailMessage)
            ->subject('New contact message')
            ->greeting($greeting)
            ->line('You received a new contact message from '.$this->name.' ('.$this->email.').')
            ->line('Subject: '.$this->subject)
            ->line($this->message);

        if ($notifiable instanceof User && $this->contactMessage !== null) {
            return $mail->action(
                'Read message',
                route('admin.contact-messages.show', $this->contactMessage),
            );
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'contact_message',
            'contact_message_id' => $this->contactMessage?->id,
            'message' => 'New contact message from '.$this->name.' ('.$this->email.') — "'.$this->subject.'"',
            'action_text' => 'Read',
            'action_url' => $this->contactMessage !== null
                ? route('admin.contact-messages.show', $this->contactMessage)
                : route('admin.contact-messages.index'),
        ];
    }
}
