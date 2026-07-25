<?php

namespace App\Services;

use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class ProposalPdfService
{
    public function __construct(
        protected ProposalService $proposals
    ) {}

    /** Render dokumen proposal (A4 portrait) sebagai string biner PDF. */
    public function generate(Proposal $proposal): string
    {
        return Pdf::loadView('admin.pdf.proposal', $this->data($proposal))
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->output();
    }

    public function data(Proposal $proposal): array
    {
        $proposal->loadMissing(['user', 'service']);
        $total = (int) $proposal->total_amount;

        return $this->document(
            docNumber: $proposal->proposal_number,
            serviceTitle: $proposal->service?->title ?? 'Layanan',
            client: [
                'name' => $proposal->user?->name ?? '',
                'phone' => $proposal->user?->phone ?? '',
                'email' => $proposal->user?->email ?? '',
            ],
            answers: $this->proposals->readableAnswers($proposal),
            priceItems: collect($proposal->price_items ?? []),
            total: $total,
            issuedAt: $proposal->submitted_at ?? $proposal->created_at ?? Carbon::now(),
        );
    }

    /**
     * Dokumen proposal untuk pengajuan lama (mobile_service_requests) yang belum
     * lahir dari form builder — supaya semua PDF memakai format yang sama.
     */
    public function dataFromServiceRequest(\App\Models\MobileServiceRequest $request): array
    {
        $request->loadMissing(['user', 'service']);

        $rows = [];
        $add = fn (string $label, $value) => $rows[] = ['label' => $label, 'type' => 'text', 'value' => (string) ($value ?: '-'), 'files' => []];

        $rows[] = ['label' => 'Detail Kebutuhan', 'type' => 'section', 'value' => '', 'files' => []];
        $add('Jenis Bangunan', $request->building_label);
        $add('Deskripsi Kebutuhan', $request->description);

        if ($request->survey_address) {
            $rows[] = ['label' => 'Lokasi & Jadwal Survei', 'type' => 'section', 'value' => '', 'files' => []];
            $add('Alamat Survei', trim($request->survey_address . ($request->survey_region ? ' — ' . $request->survey_region : '')));
            $add('Jadwal Survei', optional($request->survey_date)?->format('d M Y'));
        }

        $priceItems = collect();
        if ((int) $request->survey_fee > 0) {
            $priceItems->push(['type' => 'survey', 'label' => 'Biaya Survei', 'amount' => (int) $request->survey_fee, 'is_required' => true]);
        }
        if ((int) $request->tax_amount > 0) {
            $priceItems->push(['type' => 'other', 'label' => 'Pajak (' . (int) $request->tax_percentage . '%)', 'amount' => (int) $request->tax_amount, 'is_required' => true]);
        }
        if ((int) $request->products_amount > 0) {
            $priceItems->push(['type' => 'other', 'label' => 'Produk Tambahan', 'amount' => (int) $request->products_amount, 'is_required' => true]);
        }

        return $this->document(
            docNumber: $request->transaction_code_label ?? $request->transaction_code ?? ('SR-' . $request->id),
            serviceTitle: $request->service?->title ?? 'Layanan',
            client: [
                'name' => $request->user?->name ?? '',
                'phone' => $request->user?->phone ?? '',
                'email' => $request->user?->email ?? '',
            ],
            answers: $rows,
            priceItems: $priceItems,
            total: (int) $request->total_amount,
            issuedAt: $request->submitted_at ?? $request->created_at ?? Carbon::now(),
        );
    }

    /** Bentuk data seragam yang dipakai blade dokumen proposal. */
    private function document(string $docNumber, string $serviceTitle, array $client, array $answers, $priceItems, int $total, Carbon $issuedAt): array
    {
        return [
            'docNumber' => $docNumber,
            'serviceTitle' => $serviceTitle,
            'client' => $client,
            'answers' => $answers,
            'priceItems' => collect($priceItems),
            'total' => $total,
            'totalWords' => $this->terbilang($total) . ' rupiah',
            'company' => [
                'name' => config('settings.value.app_name') ?: 'Maninjau',
                'tagline' => config('settings.value.tagline') ?: '',
                'logo' => $this->localLogoPath(),
            ],
            'issuedAt' => $issuedAt,
            'validUntil' => $issuedAt->copy()->addDays(30),
        ];
    }

    /** Path absolut logo bila berkasnya ada di server; null bila tidak. */
    private function localLogoPath(): ?string
    {
        $logo = config('settings.value.app_logo');
        // Setting logo bisa berupa string atau array (mis. ['file' => 'logo.png']).
        if (is_array($logo)) {
            $logo = $logo['file'] ?? null;
        }
        if (! is_string($logo) || $logo === '') {
            return null;
        }

        // dompdf butuh ekstensi GD untuk menyisipkan PNG/JPG. Bila tidak ada,
        // kop surat tetap tampil (tanpa logo) daripada PDF gagal dibuat.
        if (! extension_loaded('gd')) {
            return null;
        }

        foreach (['assets/images/informasi/', 'assets/images/', ''] as $dir) {
            $path = public_path($dir . $logo);
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /** Angka → terbilang bahasa Indonesia (untuk nominal biaya). */
    public function terbilang(int $number): string
    {
        $number = abs($number);
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $number === 0 ? 'nol' : $words[$number];
        }
        if ($number < 20) {
            return $this->terbilang($number - 10) . ' belas';
        }
        if ($number < 100) {
            return $this->terbilang(intdiv($number, 10)) . ' puluh' . $this->suffix($number % 10);
        }
        if ($number < 200) {
            return 'seratus' . $this->suffix($number - 100);
        }
        if ($number < 1000) {
            return $this->terbilang(intdiv($number, 100)) . ' ratus' . $this->suffix($number % 100);
        }
        if ($number < 2000) {
            return 'seribu' . $this->suffix($number - 1000);
        }
        if ($number < 1000000) {
            return $this->terbilang(intdiv($number, 1000)) . ' ribu' . $this->suffix($number % 1000);
        }
        if ($number < 1000000000) {
            return $this->terbilang(intdiv($number, 1000000)) . ' juta' . $this->suffix($number % 1000000);
        }

        return $this->terbilang(intdiv($number, 1000000000)) . ' miliar' . $this->suffix($number % 1000000000);
    }

    private function suffix(int $remainder): string
    {
        return $remainder > 0 ? ' ' . $this->terbilang($remainder) : '';
    }
}
