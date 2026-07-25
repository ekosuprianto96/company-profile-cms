@extends('admin.layouts.main')

@section('content')
@php
    // Status yang ditampilkan = status Order Layanan (SR). Proposal hanya dokumen.
    $orderStatusLabels = [
        'draft' => 'Draft', 'waiting_payment' => 'Menunggu Pembayaran', 'waiting_transfer' => 'Menunggu Transfer',
        'payment_challenge' => 'Verifikasi Bayar', 'pending' => 'Diproses', 'paid' => 'Terbayar',
        'approved' => 'Disetujui', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai',
        'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan',
    ];
@endphp
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                <div>
                    <h4 class="card-title mb-1">Proposal</h4>
                    <p class="text-muted mb-0">Pengajuan layanan dari form dinamis. Setiap proposal memuat seluruh isian user beserta rincian biaya yang berlaku saat pengajuan.</p>
                </div>
                <a href="{{ route('admin.mobile.service_requests.index') }}" class="btn btn-success btn-sm"><i class="ri-shopping-bag-3-line me-1"></i> Order Layanan</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Pemohon</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th>Dikirim</th>
                                <th class="text-center" style="width:130px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($proposals as $p)
                                @php
                                    $os = $p->serviceRequest?->status;
                                    $osLabel = $orderStatusLabels[$os] ?? ($os ?? 'Belum ada order');
                                    $badge = match ($os) {
                                        'completed', 'approved', 'paid' => 'success',
                                        'rejected', 'cancelled' => 'danger',
                                        'waiting_payment', 'waiting_transfer', 'payment_challenge' => 'warning',
                                        default => 'info',
                                    };
                                @endphp
                                <tr>
                                    <td><span class="fw-semibold">{{ $p->proposal_number }}</span></td>
                                    <td>
                                        {{ $p->user?->name ?? '-' }}
                                        <div><small class="text-muted">{{ $p->user?->phone ?? $p->user?->email ?? '' }}</small></div>
                                    </td>
                                    <td>{{ $p->service?->title ?? '-' }}</td>
                                    <td><span class="badge badge-sm badge-{{ $badge }}">{{ $osLabel }}</span></td>
                                    <td class="text-end">Rp {{ number_format((int) $p->total_amount, 0, ',', '.') }}</td>
                                    <td><small class="text-muted">{{ optional($p->submitted_at)?->format('d M Y H:i') ?? '-' }}</small></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center flex-nowrap" style="gap:6px">
                                            <a href="{{ route('admin.mobile.proposals.show', $p->id) }}" class="btn btn-success btn-xs" title="Detail"><i class="ri-eye-line"></i></a>
                                            @if ($p->serviceRequest)
                                                <a href="{{ route('admin.mobile.service_requests.show', $p->serviceRequest->id) }}" class="btn btn-primary btn-xs" title="Ke Order Layanan"><i class="ri-shopping-bag-3-line"></i></a>
                                            @endif
                                            <a href="{{ route('admin.mobile.proposals.pdf', $p->id) }}" target="_blank" class="btn btn-info btn-xs" title="Preview PDF"><i class="ri-file-pdf-line"></i></a>
                                            <a href="{{ route('admin.mobile.proposals.download', $p->id) }}" class="btn btn-light btn-xs" title="Unduh PDF"><i class="ri-download-2-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada proposal masuk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $proposals->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
