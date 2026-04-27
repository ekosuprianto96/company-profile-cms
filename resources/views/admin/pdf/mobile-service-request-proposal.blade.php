@php
    $statusLabel = ucfirst(str_replace('_', ' ', (string) $serviceRequest->status));
    $paymentLabel = ucfirst(str_replace('_', ' ', (string) $serviceRequest->payment_status));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 32px 36px 32px; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.55;
        }
        .header {
            border-bottom: 2px solid #1f6f8b;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .brand-row {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .brand-subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }
        .meta-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #e2e8f0; color: #334155; }
        .section-title {
            margin: 18px 0 10px;
            padding: 7px 10px;
            background: #f1f5f9;
            border-left: 4px solid #1f6f8b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .info-label {
            width: 28%;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-value {
            border-bottom: 1px solid #e2e8f0;
        }
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col td {
            width: 50%;
            vertical-align: top;
            padding: 0 6px 0 0;
        }
        .box {
            border: 1px solid #dbe4ee;
            border-radius: 6px;
            padding: 10px 12px;
            background: #fff;
        }
        .cost-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .cost-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .cost-table tr:last-child td {
            border-bottom: none;
            font-weight: 700;
            font-size: 12px;
        }
        .muted {
            color: #64748b;
        }
        .note-box {
            border: 1px solid #dbe4ee;
            background: #f8fafc;
            padding: 10px 12px;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 24px;
        }
        .signature-wrap {
            width: 100%;
            border-collapse: collapse;
        }
        .signature {
            width: 40%;
            text-align: center;
            vertical-align: top;
        }
        .signature-line {
            margin-top: 56px;
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            font-weight: 700;
        }
        .page-number {
            position: fixed;
            bottom: -4px;
            right: 0;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="brand-row">
            <tr>
                <td>
                    <div class="brand-title">Proposal Pengajuan Survey</div>
                    <p class="brand-subtitle">Dokumen ringkasan pengajuan layanan mobile order kontraktor</p>
                </td>
                <td style="text-align: right;">
                    <div class="meta-badge badge-secondary">{{ $proposalNumber }}</div><br><br>
                    <div class="meta-badge badge-{{ $serviceRequest->status === 'approved' ? 'success' : ($serviceRequest->status === 'rejected' ? 'danger' : 'warning') }}">
                        Status: {{ $statusLabel }}
                    </div>
                    <div style="height: 6px;"></div>
                    <div class="meta-badge badge-{{ $serviceRequest->payment_status === 'paid' ? 'success' : ($serviceRequest->payment_status === 'failed' ? 'danger' : 'warning') }}">
                        Payment: {{ $paymentLabel }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="two-col">
        <tr>
            <td>
                <div class="section-title">Data User</div>
                <div class="box">
                    <table class="info-table">
                        <tr>
                            <td class="info-label">Nama</td>
                            <td class="info-value">{{ $serviceRequest->user?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Telepon</td>
                            <td class="info-value">{{ $serviceRequest->user?->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Email</td>
                            <td class="info-value">{{ $serviceRequest->user?->email ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="section-title">Informasi Proposal</div>
                <div class="box">
                    <table class="info-table">
                        <tr>
                            <td class="info-label">Layanan</td>
                            <td class="info-value">{{ $serviceRequest->service?->title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Jenis Kebutuhan</td>
                            <td class="info-value">{{ $serviceRequest->needType?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Tanggal</td>
                            <td class="info-value">{{ $generatedAt->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Rincian Pengajuan</div>
    <div class="box">
        <table class="info-table">
            <tr>
                <td class="info-label">Jenis Bangunan</td>
                <td class="info-value">{{ $serviceRequest->building_label ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Lokasi Survey</td>
                <td class="info-value">{{ $serviceRequest->survey_address ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Tanggal Survey</td>
                <td class="info-value">{{ optional($serviceRequest->survey_date)?->format('d M Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Perkiraan Anggaran</td>
                <td class="info-value">{{ $serviceRequest->budgetOption?->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Rincian Biaya</div>
    <div class="box">
        <table class="cost-table">
            <tr>
                <td>Biaya Survey</td>
                <td style="text-align: right;">Rp{{ number_format((int) $serviceRequest->survey_fee, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pajak</td>
                <td style="text-align: right;">{{ (int) $serviceRequest->tax_percentage }}% (Rp{{ number_format((int) $serviceRequest->tax_amount, 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td>Total</td>
                <td style="text-align: right;">Rp{{ number_format((int) $serviceRequest->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Catatan Masalah</div>
    <div class="note-box">
        {{ $serviceRequest->description ?: '-' }}
    </div>

    @if ($serviceRequest->admin_note)
        <div class="section-title">Catatan Admin</div>
        <div class="note-box">
            {{ $serviceRequest->admin_note }}
        </div>
    @endif

    @if ($serviceRequest->rejection_reason)
        <div class="section-title">Alasan Penolakan</div>
        <div class="note-box">
            {{ $serviceRequest->rejection_reason }}
        </div>
    @endif

    <div class="footer">
        <table class="signature-wrap">
            <tr>
                <td class="signature">
                    <div class="muted">Disusun oleh</div>
                    <div class="signature-line">Admin Maninjau E-Sembalun</div>
                </td>
                <td class="signature" style="width: 20%;">&nbsp;</td>
                <td class="signature">
                    <div class="muted">Diverifikasi oleh</div>
                    <div class="signature-line">{{ $serviceRequest->handledBy?->name ?? 'Admin' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-number">Proposal {{ $proposalNumber }}</div>
</body>
</html>
