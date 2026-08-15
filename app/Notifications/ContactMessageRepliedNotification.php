<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $reply,
        public ?string $name = null,
    ) {
    }

    public function via($notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable
            ? ['mail']
            : ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Re: Your message to LabGato')
            ->line('Thank you for contacting LabGato regarding "'.$this->subject.'".')
            ->line($this->reply);

        if ($this->name !== null && $this->name !== '') {
            $mail->greeting('Hello '.$this->name.',');
        } else {
            $mail->greeting('Hello '.$notifiable->name.',');
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'LabGato replied to your message regarding "'.$this->subject.'".',
            'action_text' => 'View messages',
            'action_url' => url('/account/messages'),
        ];
    }
}
