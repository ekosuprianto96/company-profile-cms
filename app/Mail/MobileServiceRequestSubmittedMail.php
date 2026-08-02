<?php

namespace App\Mail;

use App\Models\MobileServiceRequest;
use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MobileServiceRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected ?array $renderedCache = null;

    public function __construct(
        public MobileServiceRequest $serviceRequest,
        public string $recipientType = 'user'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->rendered()['subject']);
    }

    public function content(): Content
    {
        $rendered = $this->rendered();

        return new Content(
            view: 'emails.templated',
            with: ['headline' => $rendered['subject'], 'body' => $rendered['body']],
        );
    }

    protected function rendered(): array
    {
        if ($this->renderedCache !== null) {
            return $this->renderedCache;
        }

        $sr = $this->serviceRequest;
        $audience = $this->recipientType === 'admin' ? 'admin' : 'user';

        return $this->renderedCache = app(NotificationTemplateService::class)->render('service_request.submitted', 'email', $audience, [
            'recipient_name' => $audience === 'admin' ? 'Admin' : ($sr->user?->name ?? 'Pelanggan'),
            'transaction_code' => $sr->transaction_code_label,
            'service_title' => $sr->service?->title ?? '-',
            'customer_name' => $sr->user?->name,
            'survey_date' => optional($sr->survey_date)?->format('d M Y') ?? '-',
            'survey_address' => $sr->survey_address ?? '-',
            'total_amount' => 'Rp' . number_format((int) $sr->total_amount, 0, ',', '.'),
        ]);
    }
}
