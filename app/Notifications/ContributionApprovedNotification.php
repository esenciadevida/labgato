<?php

namespace App\Notifications;

use App\Enums\ContributionType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContributionApprovedNotification extends Notification
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
            ->subject('Your contribution has been approved')
            ->greeting('Congratulations!')
            ->line('Your '.strtolower($this->type->label()).' contribution "'.$this->resource->name.'" has been approved and published.')
            ->action('View your published contribution', $this->publicUrl())
            ->line('Thank you for contributing to the LabGato library.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your contribution "'.$this->resource->name.'" has been approved and published.',
            'action_text' => 'View contribution',
            'action_url' => $this->publicUrl(),
        ];
    }

    private function publicUrl(): string
    {
        return match ($this->type) {
            ContributionType::SOP => route('public.sops.show', $this->resource->slug),
            ContributionType::KNOWLEDGE => route('public.knowledge.show', $this->resource->slug),
            ContributionType::INTERPRETATION => route('public.interpretation.show', $this->resource->slug),
        };
    }
}
