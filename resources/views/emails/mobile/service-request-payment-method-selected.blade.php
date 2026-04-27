@extends('emails.layouts.mobile-brand')

@section('headline', 'Metode pembayaran dipilih')

@section('subheadline')
    Metode pembayaran untuk pengajuan survey kamu sudah tersimpan.
@endsection

@section('content')
    @php
        $serviceName = $serviceRequest->service?->title ?? '-';
        $paymentMethod = $serviceRequest->payment_method ?? '-';
        $totalAmount = (int) $serviceRequest->total_amount;
        $requestCode = $serviceRequest->transaction_code_label;
    @endphp

    <div style="font-size:16px;line-height:1.8;color:#374151;margin-bottom:18px;">
        Hai {{ $serviceRequest->user?->name ?? 'Pengguna' }},
    </div>

    <div style="font-size:14px;line-height:1.8;color:#4b5563;margin-bottom:22px;">
        Metode pembayaran untuk pengajuan survey layanan <strong>{{ $serviceName }}</strong> sudah dipilih dan sedang diproses.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:24px;">
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;width:42%;color:#6b7280;font-size:13px;">Kode Pengajuan</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;font-weight:700;">{{ $requestCode }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;width:42%;color:#6b7280;font-size:13px;">Metode Pembayaran</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">{{ $paymentMethod }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Status Pembayaran</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">{{ str_replace('_', ' ', ucfirst($serviceRequest->payment_status)) }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;color:#6b7280;font-size:13px;">Total Pembayaran</td>
            <td style="padding:12px 0;font-size:14px;font-weight:700;color:#111827;">Rp{{ number_format($totalAmount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div style="background:#f8fafc;border:1px solid #e5e7eb;padding:18px 20px;border-radius:14px;font-size:13px;line-height:1.8;color:#4b5563;">
        Kami akan mengirimkan pembaruan berikutnya melalui email dan aplikasi setelah proses pembayaran atau verifikasi selesai.
    </div>
@endsection
