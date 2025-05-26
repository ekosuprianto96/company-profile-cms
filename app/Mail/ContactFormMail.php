<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $contactData = [],
        public string $messageID = ''
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesan Dari Kontak Form',
        );
    }

    public function withSwiftMessage($message)
    {
        $message->getHeaders()->addTextHeader('Message-ID', $this->messageID);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'frontend.mails.contact-form',
            markdown: 'frontend.mails.contact-form',
            with: [
                'contactData' => $this->contactData
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
