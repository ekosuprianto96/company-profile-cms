<?php

namespace App\Mail;

use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class MobileOtpMail extends Mailable
{
    use Queueable, SerializesModels, \App\Mail\Concerns\BuildsTemplatedEmail;

    protected ?array $renderedCache = null;

    public function __construct(
        public string $userName,
        public string $code,
        public string $purpose,
        public Carbon $expiresAt
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->rendered()['subject']);
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

        $minutes = max(1, (int) now()->diffInMinutes($this->expiresAt, false));

        return $this->renderedCache = app(NotificationTemplateService::class)->render('otp.mobile', 'email', 'user', [
            'recipient_name' => $this->userName,
            'otp_code' => $this->code,
            'otp_purpose' => $this->purpose,
            'otp_expire_minutes' => (string) $minutes,
        ]);
    }

    public function attachments(): array
    {
        return [];
    }
}
