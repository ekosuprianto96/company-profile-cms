<?php

namespace App\Mail;

use App\Models\MobileServiceRequest;
use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MobileServiceRequestPaymentMethodSelectedMail extends Mailable
{
    use Queueable, SerializesModels, \App\Mail\Concerns\BuildsTemplatedEmail;

    protected ?array $renderedCache = null;

    public function __construct(
        public MobileServiceRequest $serviceRequest
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

        $sr = $this->serviceRequest;

        return $this->renderedCache = app(NotificationTemplateService::class)->render('service_request.payment_method_selected', 'email', 'user', [
            'recipient_name' => $sr->user?->name ?? 'Pelanggan',
            'transaction_code' => $sr->transaction_code_label,
            'service_title' => $sr->service?->title ?? '-',
            'payment_method' => $sr->payment_method ?? '-',
            'total_amount' => 'Rp' . number_format((int) $sr->total_amount, 0, ',', '.'),
        ]);
    }
}
