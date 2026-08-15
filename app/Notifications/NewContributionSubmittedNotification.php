<?php

namespace App\Notifications;

use App\Enums\ContributionType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContributionSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContributionType $type,
        public $resource,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New contribution awaiting review')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new '.$this->type->label().' contribution "'.$this->resource->name.'" has been submitted by '.$this->contributorName().'.')
            ->line('It is now waiting for review.')
            ->action('Open review queue', $this->reviewUrl());
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'New '.$this->type->label().' contribution "'.$this->resource->name.'" submitted by '.$this->contributorName().' is awaiting review.',
            'action_text' => 'Review contribution',
            'action_url' => $this->reviewUrl(),
        ];
    }

    private function contributorName(): string
    {
        return $this->resource->contributedBy?->name ?? 'a contributor';
    }

    private function reviewUrl(): string
    {
        return route('admin.contributions.review', [
            'type' => $this->type->value,
            'id' => $this->resource->id,
        ]);
    }
}
