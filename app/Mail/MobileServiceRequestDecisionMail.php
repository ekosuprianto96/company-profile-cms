<?php

namespace App\Mail;

use App\Models\MobileServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MobileServiceRequestDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MobileServiceRequest $serviceRequest,
        public string $decision,
        public ?string $note = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->decision) {
                'approved' => 'Pengajuan survey ' . $this->serviceRequest->transaction_code_label . ' disetujui',
                'completed' => 'Pengajuan survey ' . $this->serviceRequest->transaction_code_label . ' selesai diproses',
                default => 'Pengajuan survey ' . $this->serviceRequest->transaction_code_label . ' ditolak',
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mobile.service-request-decision',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'decision' => $this->decision,
                'note' => $this->note,
            ],
        );
    }
}
