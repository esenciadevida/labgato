<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $reply,
        public ?string $adminName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: Your message to Labgoto',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.reply',
            with: [
                'userName' => $this->contactMessage->name,
                'originalMessage' => $this->contactMessage->message,
                'messagesUrl' => url('/account/messages'),
            ],
        );
    }
}
