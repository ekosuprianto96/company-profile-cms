<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class MobileOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $code,
        public string $purpose,
        public Carbon $expiresAt
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Mobile App'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mobile-otp'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
