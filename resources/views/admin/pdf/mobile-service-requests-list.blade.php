@php
    $formatRegion = function ($surveyRegion) {
        if (!is_array($surveyRegion)) {
            return '-';
        }

        $parts = [
            data_get($surveyRegion, 'village.name'),
            data_get($surveyRegion, 'district.name'),
            data_get($surveyRegion, 'regency.name'),
            data_get($surveyRegion, 'province.name'),
        ];

        $parts = array_values(array_filter($parts, fn ($part) => is_string($part) && trim($part) !== ''));

        return $parts ? implode(', ', $parts) : '-';
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px 24px 30px 24px; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 9.5px;
            line-height: 1.45;
        }
        .header {
            border-bottom: 2px solid #1f6f8b;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .subtitle {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }
        .meta {
            font-size: 9px;
            color: #475569;
            margin-top: 8px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-left: 4px;
        }
        .badge-info { background: #e0f2fe; color: #075985; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .filters {
            margin: 10px 0 14px;
            padding: 8px 10px;
            border: 1px solid #dbe4ee;
            background: #f8fafc;
        }
        .filters table,
        .summary-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .filters td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .filters .label {
            color: #64748b;
            font-weight: 700;
            width: 120px;
        }
        .summary-table th,
        .summary-table td,
        .data-table th,
        .data-table td {
            border: 1px solid #dbe4ee;
            padding: 6px 7px;
            vertical-align: top;
        }
        .summary-table th,
        .data-table th {
            background: #f1f5f9;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .data-table th {
            text-align: left;
        }
        .text-right { text-align: right; }
        .muted { color: #64748b; }
        .small { font-size: 8.5px; }
        .page-number {
            position: fixed;
            bottom: -4px;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Daftar Pengajuan Mobile</div>
        <p class="subtitle">Rekap data pengajuan berdasarkan filter yang aktif.</p>
        <div class="meta">
            Digenerate pada: <strong>{{ $generatedAt->format('d M Y H:i') }}</strong>
        </div>
    </div>

    <div class="filters">
        <table>
            <tr>
                <td class="label">Kata Kunci</td>
                <td>{{ $filters['search'] ?: '-' }}</td>
                <td class="label">Layanan</td>
                <td>{{ $filters['service_id'] ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td>{{ $filters['status'] ?: '-' }}</td>
                <td class="label">Pembayaran</td>
                <td>{{ $filters['payment_status'] ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Provinsi</td>
                <td>{{ $filters['province'] ?: '-' }}</td>
                <td class="label">Kota/Kab.</td>
                <td>{{ $filters['regency'] ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kecamatan</td>
                <td>{{ $filters['district'] ?: '-' }}</td>
                <td class="label">Kelurahan/Desa</td>
                <td>{{ $filters['village'] ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Survey Dari</td>
                <td>{{ $filters['survey_from'] ?: '-' }}</td>
                <td class="label">Survey Sampai</td>
                <td>{{ $filters['survey_to'] ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Kode</th>
                <th style="width: 13%;">Pemesan</th>
                <th style="width: 14%;">Layanan</th>
                <th style="width: 14%;">Jadwal</th>
                <th style="width: 14%;">Alamat</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 8%;">Pembayaran</th>
                <th style="width: 10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                @php
                    $serviceSubtitle = $record->service?->title ?? '-';
                @endphp
                <tr>
                    <td>{{ $record->transaction_code_label }}</td>
                    <td>
                        <strong>{{ $record->user?->name ?? '-' }}</strong><br>
                        <span class="small muted">{{ $record->user?->phone ?? $record->user?->email ?? '-' }}</span>
                    </td>
                    <td>{{ $record->service?->title ?? '-' }}<br><span class="small muted">{{ $serviceSubtitle ?: '-' }}</span></td>
                    <td>{{ optional($record->survey_date)?->format('d M Y') ?? '-' }}<br><span class="small muted">{{ $record->survey_address ?? '-' }}</span></td>
                    <td>{{ $formatRegion($record->survey_region ?? data_get($record->draft_payload, 'surveyRegion')) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst((string) $record->status)) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst((string) $record->payment_status)) }}</td>
                    <td class="text-right">Rp{{ number_format((int) $record->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center muted">Tidak ada data sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-number">Halaman 1</div>
</body>
</html>
