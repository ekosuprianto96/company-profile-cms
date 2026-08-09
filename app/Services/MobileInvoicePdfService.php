<?php

namespace App\Services;

use App\Models\MobileServiceRequest;
use App\Models\ProductOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class MobileInvoicePdfService
{
    /** Invoice untuk pengajuan jasa / layanan. */
    public function forServiceRequest(MobileServiceRequest $serviceRequest): string
    {
        $serviceRequest->loadMissing(['user', 'service', 'products']);

        $paid = $serviceRequest->payment_status === 'paid' || ! empty($serviceRequest->paid_at);
        $date = $serviceRequest->paid_at
            ?? $serviceRequest->payment_method_selected_at
            ?? $serviceRequest->submitted_at
            ?? $serviceRequest->created_at;

        $invoice = [
            'type' => 'service',
            'number' => $serviceRequest->transaction_code_label ?? ('INV-' . $serviceRequest->id),
            'date' => $this->formatDate($date),
            'paid' => $paid,
            'status_label' => $paid ? 'Lunas' : 'Belum Bayar',
            'customer' => [
                'name' => $serviceRequest->user?->name ?? '-',
                'email' => $serviceRequest->user?->email ?? '-',
                'phone' => $serviceRequest->user?->phone ?? '-',
            ],
            'context_rows' => [
                ['label' => 'Lokasi Survei', 'value' => $serviceRequest->survey_address ?: '-'],
                ['label' => 'Jadwal Survei', 'value' => $this->formatDate($serviceRequest->survey_date)],
            ],
            'item' => [
                'title' => $serviceRequest->service?->title ?? '-',
                'subtitle' => trim(($serviceRequest->building_label ?? '-') . ' · Survei lokasi'),
                'amount' => null,
            ],
            'summary' => array_values(array_filter([
                ['label' => 'Biaya Survey', 'value' => $this->rp($serviceRequest->survey_fee)],
                ...$serviceRequest->products->map(fn ($product) => [
                    'label' => $product->product_name . ' (' . (int) $product->quantity . '×)',
                    'value' => $this->rp($product->subtotal),
                ])->all(),
                ((int) $serviceRequest->discount_amount > 0
                    ? ['label' => 'Diskon Voucher', 'value' => '-' . $this->rp($serviceRequest->discount_amount)]
                    : null),
                ['label' => 'Pajak (' . (int) $serviceRequest->tax_percentage . '%)', 'value' => $this->rp($serviceRequest->tax_amount)],
            ])),
            'total' => $this->rp($serviceRequest->total_amount),
            'payment' => [
                ['label' => 'Metode', 'value' => $this->paymentLabel($serviceRequest->payment_method)],
                ['label' => 'Status', 'value' => $paid ? 'Lunas' : 'Belum Bayar'],
                ['label' => 'Waktu Bayar', 'value' => $serviceRequest->paid_at ? $this->formatDateTime($serviceRequest->paid_at) : '-'],
            ],
        ];

        return $this->renderCached('service', $serviceRequest->id, $serviceRequest->updated_at, config('invoice.templates.service', 'classic'), $invoice);
    }

    /** Invoice untuk order produk (saat ini mock). */
    public function forProductOrder(ProductOrder $order): string
    {
        $order->loadMissing('user');
        $paid = $order->payment_status === 'paid';

        $invoice = [
            'type' => 'product',
            'number' => $order->order_number,
            'date' => $this->formatDate($order->updated_at),
            'paid' => $paid,
            'status_label' => $paid ? 'Lunas' : 'Belum Bayar',
            'customer' => [
                'name' => $order->customer_name ?: ($order->user?->name ?? '-'),
                'email' => $order->customer_email ?: ($order->user?->email ?? '-'),
                'phone' => $order->customer_phone ?: ($order->user?->phone ?? '-'),
            ],
            'context_rows' => [
                ['label' => 'Alamat Pengiriman', 'value' => $order->address ?: '-'],
            ],
            'item' => [
                'title' => $order->product_name,
                'subtitle' => $order->quantity . ' × ' . $this->rp($order->unit_price)
                    . ($order->variant ? ' · ' . $order->variant : ''),
                'amount' => $this->rp($order->subtotal),
            ],
            'summary' => array_values(array_filter([
                ['label' => 'Subtotal', 'value' => $this->rp($order->subtotal)],
                ((int) $order->discount_amount > 0
                    ? ['label' => 'Diskon Voucher', 'value' => '-' . $this->rp($order->discount_amount)]
                    : null),
                ['label' => 'Ongkir (' . ($order->courier ?: '-') . ')', 'value' => $this->rp($order->shipping_fee)],
            ])),
            'total' => $this->rp($order->grand_total),
            'payment' => [
                ['label' => 'Metode', 'value' => $order->payment_method ?: '-'],
                ['label' => 'Status', 'value' => $paid ? 'Lunas' : 'Belum Bayar'],
                ['label' => 'Kurir', 'value' => $order->courier ?: '-'],
                ['label' => 'No. Resi', 'value' => $order->tracking_number ?: '-'],
            ],
        ];

        return $this->renderCached('product', $order->id, $order->updated_at, config('invoice.templates.product', 'classic'), $invoice);
    }

    /**
     * Render PDF dengan cache berdasarkan versi (updated_at) order. Unduhan
     * berikutnya untuk invoice yang sama = instan; regenerasi otomatis saat
     * data order berubah (updated_at berubah → key cache berubah).
     */
    protected function renderCached(string $type, int $id, $version, string $template, array $invoice): string
    {
        $key = "invoice_pdf:{$type}:{$id}:" . (optional($version)->timestamp ?? 0);

        return Cache::remember($key, now()->addDay(), fn () => $this->render($template, $invoice));
    }

    protected function render(string $template, array $invoice): string
    {
        $available = array_keys(config('invoice.available', ['classic' => 'Classic']));
        if (! in_array($template, $available, true)) {
            $template = 'classic';
        }

        return Pdf::loadView('admin.pdf.invoices.' . $template, [
            'invoice' => $invoice,
            'company' => $this->company(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->output();
    }

    /** Kop invoice diambil dinamis dari settings, dipanggil via config. */
    protected function company(): array
    {
        return [
            // Nama kop mengikuti nama aplikasi mobile (settings mobile) agar konsisten
            // dengan yang tampil di aplikasi (mis. "Maninjau PRO").
            'name' => app(\App\Services\MobileAppSettingService::class)->appName(),
            'tagline' => config('settings.value.tagline') ?: 'Jasa & Material Bangunan',
            'logo' => $this->logoData(),
            'address' => config('footer_settings.address') ?: null,
            'phone' => config('footer_settings.phone') ?: (config('footer_settings.telepon') ?: null),
            'email' => config('footer_settings.email') ?: null,
        ];
    }

    protected function logoData(): ?string
    {
        $logo = config('app.logo');
        // Nilai app_logo dari settings bisa berupa array/objek {file: 'logo.png'}
        // (berkas ada di public/assets/images/informasi), string path, atau URL.
        if (is_array($logo)) {
            $logo = $logo['file'] ?? $logo['url'] ?? $logo['path'] ?? $logo['src'] ?? null;
        } elseif (is_object($logo)) {
            $logo = $logo->file ?? $logo->url ?? $logo->path ?? null;
        }
        if (! is_string($logo) || $logo === '') {
            return null;
        }

        if (str_starts_with($logo, 'http')) {
            return $logo;
        }

        // Cari berkas fisik di lokasi upload settings, lalu storage, lalu root public.
        foreach ([
            public_path('assets/images/informasi/' . $logo),
            public_path('storage/' . ltrim($logo, '/')),
            public_path(ltrim($logo, '/')),
        ] as $path) {
            if (is_file($path)) {
                // Encode base64 sekali saja (di-cache per berkas+mtime) — tak perlu
                // baca & encode ulang tiap render invoice.
                return Cache::rememberForever('invoice_logo:' . md5($path) . ':' . filemtime($path), function () use ($path) {
                    $mime = mime_content_type($path) ?: 'image/png';

                    return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
                });
            }
        }

        return null;
    }

    protected function rp($value): string
    {
        return 'Rp ' . number_format((int) $value, 0, ',', '.');
    }

    protected function formatDate($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->isoFormat('D MMM Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function formatDateTime($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->isoFormat('D MMM Y, HH.mm');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function paymentLabel(?string $value): string
    {
        if (! $value) {
            return '-';
        }

        $labels = [
            'gopay' => 'GoPay',
            'dana' => 'DANA',
            'ovo' => 'OVO',
            'qris' => 'QRIS',
            'va_bca' => 'Virtual Account BCA',
            'va_bni' => 'Virtual Account BNI',
            'va_mandiri' => 'Virtual Account Mandiri',
            'credit_card' => 'Kartu Kredit/Debit',
            'manual_transfer' => 'Transfer Manual',
        ];

        return $labels[$value] ?? ucwords(str_replace('_', ' ', $value));
    }
}
