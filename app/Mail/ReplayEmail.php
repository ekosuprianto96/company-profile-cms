<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReplayEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public mixed $messageParent = [],
        public string $fromEmail = '',
        public string $message = ''
    ) {
        //
    }

    public function build()
    {
        return $this->view('admin.mails.replay-message', [
            'messageParent' => $this->messageParent,
            'message' => $this->message
        ])
            ->markdown('admin.mails.replay-message')
            ->from($this->fromEmail)
            ->subject('Re : ' . $this->messageParent->subject);
    }

    public function withSwiftMessage($message)
    {
        $message->getHeaders()->addTextHeader('Message-ID', $this->messageParent->message_id);
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
