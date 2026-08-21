<?php

namespace App\Notifications;

use App\Enums\ContributionType;
use App\Mail\ContributionStatusMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContributionRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContributionType $type,
        public $resource,
        public string $reason,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): ContributionStatusMail
    {
        return (new ContributionStatusMail(
            $notifiable,
            $this->type->label(),
            $this->resource->name,
            'rejected',
            reason: $this->reason,
            ctaUrl: $this->editUrl(),
        ))
            ->to($notifiable->email);
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
