<?php

namespace App\Notifications;

use App\Enums\ContributionType;
use App\Mail\ContributionStatusMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContributionApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContributionType $type,
        public $resource,
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
            'approved',
            ctaUrl: $this->publicUrl(),
            totalContributions: $this->totalContributions($notifiable),
        ))
            ->to($notifiable->email);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your contribution "'.$this->resource->name.'" has been approved and published.',
            'action_text' => 'View contribution',
            'action_url' => $this->publicUrl(),
        ];
    }

    private function totalContributions($user): int
    {
        return $user->sops()->count()
            + $user->knowledgeResources()->count()
            + $user->interpretationResources()->count();
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
