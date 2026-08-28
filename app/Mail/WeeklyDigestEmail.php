<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyDigestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $metrics
     */
    public function __construct(
        public string $weekStart,
        public string $weekEnd,
        public array $metrics,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Labgoto weekly digest: '.$this->weekStart.' to '.$this->weekEnd,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system.weekly-digest',
        );
    }
}