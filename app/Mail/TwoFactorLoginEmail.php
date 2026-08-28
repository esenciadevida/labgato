<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorLoginEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your '.config('app.name').' sign-in code');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.two-factor',
            with: [
                'expiresIn' => (int) config('labgoto.security.two_factor_ttl_minutes', 10),
            ],
        );
    }
}