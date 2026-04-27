<?php

namespace App\Mail;

use App\Models\MobileServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MobileServiceRequestPaymentMethodSelectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MobileServiceRequest $serviceRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Metode pembayaran ' . $this->serviceRequest->transaction_code_label . ' dipilih',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mobile.service-request-payment-method-selected',
            with: [
                'serviceRequest' => $this->serviceRequest,
            ],
        );
    }
}
