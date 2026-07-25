@extends('admin.layouts.main')

@section('content')
@php
    // Proposal = dokumen isian. Status & pembayaran dipegang Order Layanan (SR).
    $sr = $proposal->serviceRequest;
    $orderStatusLabels = [
        'draft' => 'Draft', 'waiting_payment' => 'Menunggu Pembayaran', 'waiting_transfer' => 'Menunggu Transfer',
        'payment_challenge' => 'Verifikasi Bayar', 'pending' => 'Diproses', 'paid' => 'Terbayar',
        'approved' => 'Disetujui', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai',
        'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan',
    ];
    $orderStatus = $sr?->status;
    $orderLabel = $orderStatusLabels[$orderStatus] ?? ($orderStatus ?? 'Belum ada order');
    $badge = match ($orderStatus) {
        'completed', 'approved', 'paid' => 'success',
        'rejected', 'cancelled' => 'danger',
        'waiting_payment', 'waiting_transfer', 'payment_challenge' => 'warning',
        default => 'info',
    };
@endphp
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px">
                <div>
                    <h4 class="card-title mb-1">{{ $proposal->proposal_number }}</h4>
                    <p class="text-muted mb-0">
                        {{ $proposal->service?->title ?? '-' }} ·
                        <span class="badge badge-sm badge-{{ $badge }}">{{ $orderLabel }}</span>
                        · dikirim {{ optional($proposal->submitted_at)?->format('d M Y H:i') ?? '-' }}
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-end" style="gap:8px">
                    @if ($sr)
                        <a href="{{ route('admin.mobile.service_requests.show', $sr->id) }}" class="btn btn-success btn-sm"><i class="ri-shopping-bag-3-line me-1"></i> Order Layanan</a>
                    @endif
                    <a href="{{ route('admin.mobile.proposals.pdf', $proposal->id) }}" target="_blank" class="btn btn-info btn-sm"><i class="ri-file-pdf-line me-1"></i> Preview PDF</a>
                    <a href="{{ route('admin.mobile.proposals.download', $proposal->id) }}" class="btn btn-primary btn-sm"><i class="ri-download-2-line me-1"></i> Unduh</a>
                    <a href="{{ route('admin.mobile.proposals') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-3">Rincian Kebutuhan</h6>
                <style>
                    /* Kunci lebar kolom agar nilai panjang membungkus, bukan meluber keluar card. */
                    .prp-answers { table-layout: fixed; width: 100%; }
                    .prp-answers td { vertical-align: top; word-break: break-word; overflow-wrap: anywhere; white-space: normal; }
                    .prp-answers td.prp-key { width: 38%; }
                    .prp-answers a { word-break: break-all; }
                </style>
                <table class="table table-sm align-middle mb-0 prp-answers">
                    <tbody>
                        @forelse ($answers as $row)
                            @if ($row['type'] === 'section')
                                <tr><td colspan="2" class="fw-bold text-uppercase" style="background:#f7faf9; color:#275a56; letter-spacing:.4px; font-size:.78rem;">{{ $row['label'] }}</td></tr>
                            @else
                                <tr>
                                    <td class="text-muted prp-key">{{ $row['label'] }}</td>
                                    <td class="fw-semibold">
                                        @if (!empty($row['files']))
                                            @foreach ($row['files'] as $file)
                                                <a href="{{ $file['url'] }}" target="_blank" class="d-block"><i class="ri-attachment-2"></i> {{ $file['name'] }}</a>
                                            @endforeach
                                        @else
                                            {!! nl2br(e($row['value'] !== '' ? $row['value'] : '-')) !!}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td class="text-center text-muted py-3">Tidak ada isian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-2">Pemohon</h6>
                <div class="fw-semibold">{{ $proposal->user?->name ?? '-' }}</div>
                <div class="text-muted" style="font-size:.85em">{{ $proposal->user?->phone ?? '-' }}</div>
                <div class="text-muted" style="font-size:.85em">{{ $proposal->user?->email ?? '-' }}</div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-2">Rincian Biaya</h6>
                <table class="table table-sm mb-2">
                    <tbody>
                        @forelse ($proposal->price_items ?? [] as $item)
                            <tr>
                                <td>{{ $item['label'] }}<br><small class="text-muted">{{ !empty($item['is_required']) ? 'Wajib' : 'Opsional' }}</small></td>
                                <td class="text-end">Rp {{ number_format((int) $item['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-muted" colspan="2">Tanpa biaya awal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold" style="color:#275a56">Rp {{ number_format((int) $proposal->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-1">Order Layanan</h6>
                <p class="text-muted mb-3" style="font-size:.82em">Proposal ini adalah dokumen isian. <b>Status &amp; pembayaran dikelola di Order Layanan.</b></p>
                @if ($sr)
                    <div class="mb-2"><span class="badge badge-{{ $badge }}">{{ $orderLabel }}</span></div>
                    <div class="text-muted mb-3" style="font-size:.85em">No. Order: {{ $sr->transaction_code ?? ('#' . $sr->id) }}</div>
                    <a href="{{ route('admin.mobile.service_requests.show', $sr->id) }}" class="btn btn-primary btn-sm w-100"><i class="ri-external-link-line me-1"></i> Kelola di Order Layanan</a>
                @else
                    <div class="text-muted">Belum ada order terkait.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
