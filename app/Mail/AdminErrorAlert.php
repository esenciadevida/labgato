<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class AdminErrorAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $exceptionClass,
        public string $exceptionMessage,
        public string $file,
        public int $line,
        public ?string $url = null,
        public ?string $method = null,
        public ?string $userAgent = null,
        public ?int $userId = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Labgoto error: '.Str::limit(class_basename($this->exceptionClass), 60, ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.errors.admin-alert',
        );
    }
}