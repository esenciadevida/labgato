<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContributionStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $typeLabel,
        public string $title,
        public string $status,
        public ?string $reason = null,
        public string $ctaUrl = '',
        public int $totalContributions = 0,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->status === 'approved'
                ? 'Your contribution has been approved'
                : 'Update on your contribution',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->status === 'approved'
                ? 'emails.contributions.approved'
                : 'emails.contributions.rejected',
        );
    }
}
