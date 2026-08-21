<?php

namespace App\Mail;

use App\Models\AdminInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AdminInvitation $invitation,
        public string $acceptUrl,
        public bool $isResend = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isResend
                ? 'Reminder: Your LabGato admin invitation'
                : "You're invited to join the LabGato admin team",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.invitation',
            with: [
                'invitedBy' => $this->invitation->inviter?->name ?? config('app.name'),
                'roleName' => $this->invitation->role->name,
                'expiresAt' => $this->invitation->expires_at->format('F j, Y'),
            ],
        );
    }
}
