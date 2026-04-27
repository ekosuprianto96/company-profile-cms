@extends('emails.layouts.mobile-brand')

@section('headline', $decision === 'approved' ? 'Pengajuan disetujui' : ($decision === 'completed' ? 'Pengajuan selesai diproses' : 'Pengajuan ditolak'))

@section('subheadline')
    @if ($decision === 'approved')
        Pengajuan survey kamu sudah disetujui dan siap dilanjutkan ke tahap berikutnya.
    @elseif ($decision === 'completed')
        Pengajuan survey kamu sudah selesai diproses oleh tim kami.
    @else
        Pengajuan survey kamu belum dapat kami lanjutkan.
    @endif
@endsection

@section('content')
    @php
        $serviceName = $serviceRequest->service?->title ?? '-';
        $statusLabel = str_replace('_', ' ', ucfirst($serviceRequest->status));
        $paymentLabel = str_replace('_', ' ', ucfirst($serviceRequest->payment_status));
        $requestCode = $serviceRequest->transaction_code_label;
    @endphp

    <div style="font-size:16px;line-height:1.8;color:#374151;margin-bottom:18px;">
        Hai {{ $serviceRequest->user?->name ?? 'Pengguna' }},
    </div>

    <div style="font-size:14px;line-height:1.8;color:#4b5563;margin-bottom:22px;">
        Pengajuan survey untuk layanan <strong>{{ $serviceName }}</strong>
        @if ($decision === 'approved')
            telah <strong>disetujui</strong>.
        @elseif ($decision === 'completed')
            telah <strong>selesai diproses</strong>.
        @else
            tidak dapat kami lanjutkan.
        @endif
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:24px;">
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;width:42%;color:#6b7280;font-size:13px;">Kode Pengajuan</td>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;font-weight:700;">{{ $requestCode }}</td>
        </tr>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;width:42%;color:#6b7280;font-size:13px;">Status Pengajuan</td>
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

    @if (!empty($note))
        <div style="background:#f8fafc;border:1px solid #e5e7eb;padding:18px 20px;border-radius:14px;font-size:13px;line-height:1.8;color:#4b5563;margin-bottom:18px;">
            <div style="font-weight:700;color:#111827;margin-bottom:6px;">Catatan admin</div>
            {{ $note }}
        </div>
    @endif

    <div style="font-size:14px;line-height:1.8;color:#4b5563;">
        @if ($decision === 'approved')
            Tim kami akan menindaklanjuti pengajuan ini sesuai alur berikutnya.
        @elseif ($decision === 'completed')
            Terima kasih, pengajuan kamu sudah ditangani sampai selesai.
        @else
            Jika kamu ingin mengajukan ulang, silakan buat pengajuan baru dari aplikasi.
        @endif
    </div>
@endsection
