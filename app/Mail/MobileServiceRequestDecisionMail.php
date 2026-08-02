<?php

namespace App\Mail;

use App\Models\MobileServiceRequest;
use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MobileServiceRequestDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array{subject:string,body:string}|null */
    protected ?array $renderedCache = null;

    public function __construct(
        public MobileServiceRequest $serviceRequest,
        public string $decision,
        public ?string $note = null
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

    /** Render subjek + isi dari template notifikasi (event sesuai keputusan). */
    protected function rendered(): array
    {
        if ($this->renderedCache !== null) {
            return $this->renderedCache;
        }

        $event = match ($this->decision) {
            'approved' => 'service_request.approved',
            'completed' => 'service_request.completed',
            default => 'service_request.rejected',
        };
        $sr = $this->serviceRequest;

        return $this->renderedCache = app(NotificationTemplateService::class)->render($event, 'email', 'user', [
            'recipient_name' => $sr->user?->name ?? 'Pelanggan',
            'transaction_code' => $sr->transaction_code_label,
            'service_title' => $sr->service?->title ?? '-',
            'admin_note' => $this->note ?? $sr->admin_note ?? '',
            'rejection_reason' => $this->note ?? $sr->rejection_reason ?? '',
            'total_amount' => 'Rp' . number_format((int) $sr->total_amount, 0, ',', '.'),
        ]);
    }
}
