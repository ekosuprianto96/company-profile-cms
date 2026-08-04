<?php

namespace App\Mail;

use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels, \App\Mail\Concerns\BuildsTemplatedEmail;

    protected ?array $renderedCache = null;

    public function __construct(
        public array $contactData = [],
        public string $messageID = ''
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->rendered()['subject']);
    }

    public function withSwiftMessage($message)
    {
        $message->getHeaders()->addTextHeader('Message-ID', $this->messageID);
    }

    public function content(): Content
    {
        $rendered = $this->rendered();

        return $this->templatedContent($rendered);
    }

    protected function rendered(): array
    {
        if ($this->renderedCache !== null) {
            return $this->renderedCache;
        }

        $d = $this->contactData;

        return $this->renderedCache = app(NotificationTemplateService::class)->render('contact_form', 'email', 'admin', [
            'recipient_name' => 'Admin',
            'sender_name' => $d['name'] ?? '-',
            'sender_email' => $d['email'] ?? '-',
            'sender_phone' => $d['phone'] ?? '-',
            'subject' => $d['subject'] ?? '-',
            'sender_message' => $d['message'] ?? '-',
        ]);
    }

    public function attachments(): array
    {
        return [];
    }
}
