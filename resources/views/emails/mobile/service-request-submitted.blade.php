@extends('emails.layouts.mobile-brand')

@section('headline', $recipientType === 'admin' ? 'Pengajuan baru masuk' : 'Pengajuan survey berhasil dikirim')

@section('subheadline')
    {{ $recipientType === 'admin' ? 'Ada pengajuan survey baru dari aplikasi mobile.' : 'Pengajuan kamu sudah diterima dan sedang menunggu proses berikutnya.' }}
@endsection

@section('content')
    @php
        $statusLabel = str_replace('_', ' ', ucfirst($serviceRequest->status));
        $paymentLabel = str_replace('_', ' ', ucfirst($serviceRequest->payment_status));
        $requestCode = $serviceRequest->transaction_code_label;
    @endphp

    <div style="font-size:16px;line-height:1.8;color:#374151;margin-bottom:18px;">
        Hai {{ $recipientType === 'admin' ? 'Admin' : ($serviceRequest->user?->name ?? 'Pengguna') }},
    </div>

    <div style="font-size:14px;line-height:1.8;color:#4b5563;margin-bottom:22px;">
        @if ($recipientType === 'admin')
            Kami menerima pengajuan survey baru dari aplikasi mobile. Berikut ringkasan awalnya:
        @else
            Terima kasih, pengajuan survey kamu sudah masuk ke sistem kami. Berikut ringkasan pengajuanmu:
        @endif
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:24px;">
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;width:42%;color:#6b7280;font-size:13px;">Kode Pengajuan</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;font-weight:700;color:#111827;">{{ $requestCode }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;width:42%;color:#6b7280;font-size:13px;">Layanan</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;font-weight:600;color:#111827;">{{ $serviceRequest->service?->title ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Jenis Kebutuhan</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">{{ $serviceRequest->needType?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Status Pengajuan</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">{{ $statusLabel }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Status Pembayaran</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">{{ $paymentLabel }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;color:#6b7280;font-size:13px;">Total Pembayaran</td>
            <td style="padding:12px 0;font-size:14px;font-weight:700;color:#111827;">Rp{{ number_format((int) $serviceRequest->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if ($recipientType !== 'admin')
        <div style="background:#f8fafc;border:1px solid #e5e7eb;padding:18px 20px;border-radius:14px;font-size:13px;line-height:1.8;color:#4b5563;">
            Tim kami akan meninjau pengajuan kamu secara manual. Jika ada update lanjutan, kamu akan menerima notifikasi berikutnya lewat email dan aplikasi.
        </div>
    @else
        <div style="background:#f8fafc;border:1px solid #e5e7eb;padding:18px 20px;border-radius:14px;font-size:13px;line-height:1.8;color:#4b5563;">
            Silakan lanjutkan review di dashboard admin untuk memproses pengajuan ini.
        </div>
    @endif
@endsection
