<?php

namespace App\Mail;

use App\Models\MobileServiceRequest;
use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email action "Template Rules Step": dikirim saat sebuah step pengajuan
 * tercentang dan step tersebut memilih action notif_email. Teks dari template
 * notifikasi `service_request.step_completed`.
 */
class MobileServiceRequestStepMail extends Mailable
{
    use Queueable, SerializesModels, \App\Mail\Concerns\BuildsTemplatedEmail;

    /** @var array{subject:string,body:string}|null */
    protected ?array $renderedCache = null;

    public function __construct(
        public MobileServiceRequest $serviceRequest,
        public string $stepName,
        public string $stepDescription,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->rendered()['subject']);
    }

    public function content(): Content
    {
        return $this->templatedContent($this->rendered());
    }

    protected function rendered(): array
    {
        if ($this->renderedCache !== null) {
            return $this->renderedCache;
        }

        $sr = $this->serviceRequest;

        return $this->renderedCache = app(NotificationTemplateService::class)->render('service_request.step_completed', 'email', 'user', [
            'recipient_name' => $sr->user?->name ?? 'Pelanggan',
            'transaction_code' => $sr->transaction_code_label,
            'service_title' => $sr->service?->title ?? '-',
            'step_name' => $this->stepName,
            'step_description' => $this->stepDescription,
            'total_amount' => 'Rp' . number_format((int) $sr->total_amount, 0, ',', '.'),
        ]);
    }
}
