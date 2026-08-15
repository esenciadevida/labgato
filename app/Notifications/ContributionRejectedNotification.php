<?php

namespace App\Notifications;

use App\Enums\ContributionType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContributionRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContributionType $type,
        public $resource,
        public string $reason,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on your contribution')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your '.strtolower($this->type->label()).' contribution "'.$this->resource->name.'" was not approved at this time.')
            ->line('Reason: '.$this->reason)
            ->line('You can revise your contribution and resubmit it for review.')
            ->action('Review and resubmit', $this->editUrl());
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your contribution "'.$this->resource->name.'" was not approved. Reason: '.$this->reason,
            'action_text' => 'Revise and resubmit',
            'action_url' => $this->editUrl(),
        ];
    }

    private function editUrl(): string
    {
        return route('account.contributions.edit', [
            'type' => $this->type->value,
            'id' => $this->resource->id,
        ]);
    }
}
