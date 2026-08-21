<?php

namespace App\Notifications;

use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;

class ContactMessageRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $reply,
        public ?string $adminName = null,
    ) {}

    public function via($notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable
            ? ['mail']
            : ['database', 'mail'];
    }

    public function toMail($notifiable): ContactReplyMail
    {
        $recipient = $notifiable instanceof AnonymousNotifiable
            ? $notifiable->routeNotificationFor('mail')
            : $notifiable->email;

        return (new ContactReplyMail($this->contactMessage, $this->reply, $this->adminName))
            ->to($recipient);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'LabGato replied to your message regarding "'.$this->contactMessage->subject.'".',
            'action_text' => 'View messages',
            'action_url' => url('/account/messages'),
        ];
    }
}
