<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $ipAddress,
        public bool $created = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->created
                ? 'Your LabGato password was created'
                : 'Your LabGato password was changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.password-updated',
            with: [
                'changedAt' => now()->format('F j, Y \a\t g:i A T'),
            ],
        );
    }
}
