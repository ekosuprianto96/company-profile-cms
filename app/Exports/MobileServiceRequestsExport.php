<?php

namespace App\Exports;

use App\Services\MobileServiceRequestAdminService;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MobileServiceRequestsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected MobileServiceRequestAdminService $mobileServiceRequestAdminService,
        protected array $filters = []
    ) {}

    public function query(): Builder
    {
        return $this->mobileServiceRequestAdminService->query($this->filters);
    }

    public function map($request): array
    {
        $region = $this->formatRegion($request->survey_region ?? data_get($request->draft_payload, 'surveyRegion'));
        $isEventProject = ($request->request_flow_type ?? 'standard') === 'event_project';

        return [
            $request->transaction_code_label,
            $request->request_flow_type ?? 'standard',
            $request->user?->name ?? '-',
            $request->user?->phone ?? '-',
            $request->user?->email ?? '-',
            $request->service?->title ?? '-',
            $isEventProject ? ($request->eventProjectType?->name ?? '-') : '-',
            $isEventProject ? ($request->eventProjectNeed?->name ?? '-') : ($request->needType?->name ?? '-'),
            $isEventProject ? ($request->eventPackage?->name ?? '-') : '-',
            $isEventProject ? (optional($request->event_date)?->format('d M Y') ?? '-') : '-',
            $isEventProject ? '-' : ($request->building_label ?? '-'),
            $isEventProject ? ($request->eventBudgetOption?->name ?? '-') : ($request->budgetOption?->name ?? '-'),
            $request->survey_address ?? '-',
            $region,
            optional($request->survey_date)?->format('d M Y') ?? '-',
            str_replace('_', ' ', ucfirst((string) $request->status)),
            str_replace('_', ' ', ucfirst((string) $request->payment_status)),
            (int) $request->survey_fee,
            (int) $request->tax_amount,
            (int) $request->total_amount,
            $request->drafted_at?->format('d M Y H:i') ?? '-',
            $request->approved_at?->format('d M Y H:i') ?? '-',
            $request->paid_at?->format('d M Y H:i') ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Transaksi',
            'Flow',
            'Nama Pemesan',
            'No Telepon',
            'Email',
            'Layanan',
            'Jenis Project Event',
            'Jenis Kebutuhan',
            'Paket Event',
            'Tanggal Event',
            'Jenis Bangunan',
            'Perkiraan Anggaran',
            'Lokasi Survey',
            'Alamat',
            'Tanggal Survey',
            'Status Pengajuan',
            'Status Pembayaran',
            'Biaya Survey',
            'Pajak',
            'Total',
            'Dibuat Pada',
            'Approved Pada',
            'Lunas Pada',
        ];
    }

    protected function formatRegion($surveyRegion): string
    {
        if (! is_array($surveyRegion)) {
            return '-';
        }

        $parts = [
            data_get($surveyRegion, 'village.name'),
            data_get($surveyRegion, 'district.name'),
            data_get($surveyRegion, 'regency.name'),
            data_get($surveyRegion, 'province.name'),
        ];

        $filtered = array_values(array_filter($parts, fn ($part) => is_string($part) && trim($part) !== ''));

        return $filtered !== [] ? implode(', ', $filtered) : '-';
    }
}
