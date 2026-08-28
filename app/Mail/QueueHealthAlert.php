<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QueueHealthAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $failedCount,
        public int $windowHours,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Labgoto queue health: '.$this->failedCount.' failed job(s) in the last '.$this->windowHours.'h',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system.queue-health',
        );
    }
}