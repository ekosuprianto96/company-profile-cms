<?php

namespace App\Mail;

use App\Models\MobileServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MobileServiceRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MobileServiceRequest $serviceRequest,
        public string $recipientType = 'user'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->recipientType === 'admin'
                ? 'Pengajuan survey baru masuk - ' . $this->serviceRequest->transaction_code_label
                : 'Pengajuan survey ' . $this->serviceRequest->transaction_code_label . ' berhasil dikirim',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mobile.service-request-submitted',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'recipientType' => $this->recipientType,
            ],
        );
    }
}
