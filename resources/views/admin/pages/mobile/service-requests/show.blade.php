@extends('admin.layouts.main')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $sr = $serviceRequest;
    $isEventProject = ($sr->request_flow_type ?? 'standard') === 'event_project';
    $code = $sr->transaction_code_label ?? $sr->transaction_code ?? ('SR-' . $sr->id);

    $statusLabels = [
        'draft' => 'Draft', 'waiting_payment' => 'Menunggu Pembayaran', 'waiting_transfer' => 'Menunggu Transfer',
        'payment_challenge' => 'Verifikasi Bayar', 'approved' => 'Disetujui', 'completed' => 'Selesai',
        'rejected' => 'Ditolak', 'failed' => 'Gagal',
    ];
    $statusColor = match ($sr->status) {
        'approved', 'completed' => 'success',
        'rejected', 'failed' => 'danger',
        'waiting_payment', 'waiting_transfer', 'payment_challenge' => 'warning',
        default => 'secondary',
    };
    $paymentLabels = [
        'unpaid' => 'Belum Bayar', 'pending' => 'Menunggu', 'challenge' => 'Challenge',
        'paid' => 'Lunas', 'failed' => 'Gagal', 'waiting_transfer' => 'Menunggu Transfer',
    ];
    $paymentColor = match ($sr->payment_status) {
        'paid' => 'success', 'pending' => 'warning', 'challenge', 'failed' => 'danger', default => 'secondary',
    };

    // --- Lifecycle stepper ---
    $isPaid = $sr->payment_status === 'paid' || in_array($sr->status, ['approved', 'completed'], true);
    $isRejected = in_array($sr->status, ['rejected', 'failed'], true);
    $currentStep = match (true) {
        $sr->status === 'completed' => 5,
        $sr->status === 'approved' => 4,
        $isPaid => 3,
        in_array($sr->status, ['waiting_payment', 'waiting_transfer', 'payment_challenge'], true) => 2,
        default => 1,
    };
    $steps = [
        1 => ['label' => 'Dibuat', 'icon' => 'ri-file-add-line'],
        2 => ['label' => 'Pembayaran', 'icon' => 'ri-wallet-3-line'],
        3 => ['label' => 'Ditinjau', 'icon' => 'ri-search-eye-line'],
        4 => ['label' => 'Disetujui', 'icon' => 'ri-checkbox-circle-line'],
        5 => ['label' => 'Selesai', 'icon' => 'ri-flag-2-line'],
    ];

    // --- Aksi kontekstual ---
    $canVerifyManual = $sr->payment_method === 'manual_transfer' && $sr->payment_status !== 'paid';
    $canReview = $isPaid && ! in_array($sr->status, ['approved', 'completed', 'rejected', 'failed'], true);
    $canComplete = $sr->status === 'approved';
    $canReject = ! in_array($sr->status, ['completed', 'rejected', 'failed'], true);

    // --- Foto (resolver dipertahankan dari versi lama) ---
    $resolvePhotoUrl = function ($photo) {
        $normalize = function (?string $value) {
            if (! $value) return null;
            $value = trim($value);
            if ($value === '') return null;
            if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) return $value;
            if (str_starts_with($value, '/')) return asset(ltrim($value, '/'));
            if (str_starts_with($value, 'mobile/service-requests/') || str_starts_with($value, 'storage/')) return Storage::disk('public')->url(ltrim($value, '/'));
            if (str_starts_with($value, 'assets/')) return asset($value);
            return Storage::disk('public')->url(ltrim($value, '/'));
        };
        $isLocalUri = fn (?string $v) => $v && (str_starts_with($v, 'expo-file://') || str_starts_with($v, 'file://') || str_starts_with($v, 'content://'));
        if (is_string($photo)) {
            if ($isLocalUri($photo)) return null;
            $r = $normalize($photo);
            return $r ? ['uri' => $r, 'file_name' => basename($r)] : null;
        }
        if (! is_array($photo)) return null;
        $fileName = $photo['file_name'] ?? null;
        $url = $normalize($photo['url'] ?? null);
        $path = $normalize($photo['path'] ?? null);
        if ($url) return ['uri' => $url, 'file_name' => $fileName ?? basename($url)];
        if ($path) return ['uri' => $path, 'file_name' => $fileName ?? basename($path)];
        return null;
    };
    $issuePhotos = collect($sr->issue_photos ?? [])
        ->map(fn ($p) => $resolvePhotoUrl($p))
        ->filter(fn ($p) => is_array($p) && ! empty($p['uri']))
        ->values();

    // --- Lokasi ---
    $surveyRegion = $sr->survey_region ?? data_get($sr->draft_payload, 'surveyRegion');
    $regionLabel = collect([
        data_get($surveyRegion, 'village.name'), data_get($surveyRegion, 'district.name'),
        data_get($surveyRegion, 'regency.name'), data_get($surveyRegion, 'province.name'),
    ])->filter(fn ($v) => is_string($v) && trim($v) !== '')->implode(', ');
    $hasCoord = $sr->survey_latitude && $sr->survey_longitude;
@endphp

<style>
    .sr-step { flex: 1; text-align: center; position: relative; }
    .sr-step .sr-dot {
        width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 6px; font-size: 18px; background: #eef1f5; color: #97a1b0; z-index: 2; position: relative;
    }
    .sr-step.done .sr-dot { background: #275a56; color: #fff; }
    .sr-step.active .sr-dot { background: #c8915c; color: #fff; box-shadow: 0 0 0 4px rgba(200,145,92,.18); }
    .sr-step::before, .sr-step::after {
        content: ''; position: absolute; top: 20px; height: 3px; background: #eef1f5; width: 50%; z-index: 1;
    }
    .sr-step::before { left: 0; } .sr-step::after { right: 0; }
    .sr-step:first-child::before, .sr-step:last-child::after { display: none; }
    .sr-step.done::before, .sr-step.done::after, .sr-step.active::before { background: #275a56; }
    .sr-step .sr-label { font-size: .72rem; font-weight: 600; color: #64748b; }
    .sr-step.done .sr-label, .sr-step.active .sr-label { color: #1f2937; }
    .sr-ans { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .sr-ans td { padding: 9px 4px; border-bottom: 1px solid #f1f4f8; vertical-align: top;
        word-break: break-word; overflow-wrap: anywhere; white-space: normal; }
    .sr-ans tr:last-child td { border-bottom: 0; }
    .sr-ans a { word-break: break-all; }
    .sr-ans .sr-sec td { background: #f7faf9; color: #275a56; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; font-size: .72rem; padding: 7px 8px; }
    .sr-ans .sr-key { color: #8a94a6; width: 38%; font-size: .82rem; }
    .sr-ans .sr-val { color: #212529; font-weight: 600; font-size: .88rem; }
    .sr-meta .k { font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; color: #97a1b0; font-weight: 600; }
    .sr-meta .v { font-size: .9rem; font-weight: 600; color: #212529; }
</style>

<div class="row align-items-start">
    {{-- Header --}}
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                        <h4 class="card-title mb-0">Order Layanan {{ $code }}</h4>
                        <span class="badge badge-sm badge-{{ $statusColor }}">{{ $statusLabels[$sr->status] ?? ucfirst($sr->status) }}</span>
                        <span class="badge badge-sm badge-{{ $paymentColor }}">{{ $paymentLabels[$sr->payment_status] ?? ucfirst($sr->payment_status) }}</span>
                    </div>
                    <p class="text-muted mb-0 mt-1">{{ $sr->service?->title ?? '-' }} · dibuat {{ optional($sr->created_at)?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-end" style="gap: 8px;">
                    @if ($proposal)
                        <a href="{{ route('admin.mobile.proposals.show', $proposal->id) }}" class="btn btn-outline-primary btn-sm"><i class="ri-file-list-3-line me-1"></i> Lihat Proposal</a>
                    @endif
                    <a href="{{ route('admin.mobile.service_requests.download', $sr->id) }}" class="btn btn-primary btn-sm"><i class="ri-download-2-line me-1"></i> PDF</a>
                    <a href="{{ route('admin.mobile.service_requests.chat_user', $sr->id) }}" class="btn btn-info btn-sm"><i class="ri-message-3-line me-1"></i> Chat</a>
                    <a href="{{ route('admin.mobile.service_requests.index') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stepper --}}
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($isRejected)
                    <div class="alert alert-danger mb-3 py-2"><i class="ri-close-circle-line me-1"></i>
                        Order ini <b>{{ $statusLabels[$sr->status] ?? $sr->status }}</b>@if($sr->rejection_reason) — {{ $sr->rejection_reason }} @endif
                    </div>
                @endif
                <div class="d-flex">
                    @foreach ($steps as $i => $step)
                        @php $state = $i < $currentStep ? 'done' : ($i === $currentStep && ! $isRejected ? 'active' : ($i <= $currentStep ? 'done' : '')); @endphp
                        <div class="sr-step {{ $state }}">
                            <div class="sr-dot"><i class="{{ $step['icon'] }}"></i></div>
                            <div class="sr-label">{{ $step['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN --}}
    <div class="col-12 col-xl-8 mb-4">
        {{-- Ringkasan --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Ringkasan Order</h5>
                <div class="row g-3 sr-meta">
                    <div class="col-md-4"><div class="k">Layanan</div><div class="v">{{ $sr->service?->title ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="k">Pemesan</div><div class="v">{{ $sr->user?->name ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="k">Kontak</div><div class="v">{{ $sr->user?->phone ?? $sr->user?->email ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="k">{{ $isEventProject ? 'Tgl Meeting' : 'Tgl Survei' }}</div><div class="v">{{ optional($sr->survey_date)?->format('d M Y') ?? '-' }}</div></div>
                    <div class="col-md-8">
                        <div class="k">{{ $isEventProject ? 'Lokasi Meeting' : 'Lokasi Survei' }}</div>
                        <div class="v">{{ trim(($sr->survey_address ?: '') . ($regionLabel ? ' — ' . $regionLabel : '')) ?: '-' }}</div>
                        @if ($sr->survey_address_detail)
                            <div class="v"><span class="text-muted">Detail:</span> {{ $sr->survey_address_detail }}</div>
                        @endif
                        @if ($hasCoord)
                            <a href="https://www.google.com/maps?q={{ $sr->survey_latitude }},{{ $sr->survey_longitude }}" target="_blank" class="small text-primary"><i class="ri-map-pin-line"></i> Buka di Google Maps</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Isian dinamis dari proposal / fallback --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Isian Pengajuan</h5>
                    @if ($proposal)<span class="badge badge-sm badge-light">dari Proposal {{ $proposal->proposal_number }}</span>@endif
                </div>
                @if (! empty($proposalAnswers))
                    <table class="sr-ans">
                        @foreach ($proposalAnswers as $row)
                            @if ($row['type'] === 'section')
                                <tr class="sr-sec"><td colspan="2">{{ $row['label'] }}</td></tr>
                            @else
                                <tr>
                                    <td class="sr-key">{{ $row['label'] }}</td>
                                    <td class="sr-val">
                                        @if (! empty($row['files']))
                                            @foreach ($row['files'] as $file)
                                                <a href="{{ $file['url'] }}" download="{{ $file['name'] }}" class="d-inline-flex align-items-center text-primary mb-1" style="gap:6px"><i class="ri-download-2-line"></i> {{ $file['name'] }}</a>
                                            @endforeach
                                        @else
                                            {!! nl2br(e(($row['value'] ?? '') !== '' ? $row['value'] : '-')) !!}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                @else
                    {{-- Fallback SR lawas tanpa proposal --}}
                    <table class="sr-ans">
                        <tr><td class="sr-key">Jenis Bangunan</td><td class="sr-val">{{ $sr->building_label ?? '-' }}</td></tr>
                        <tr><td class="sr-key">Catatan</td><td class="sr-val">{!! nl2br(e($sr->description ?? '-')) !!}</td></tr>
                    </table>
                @endif
            </div>
        </div>

        {{-- Foto --}}
        @if ($issuePhotos->isNotEmpty())
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Lampiran Foto</h5>
                        <span class="badge badge-sm badge-secondary">{{ $issuePhotos->count() }} foto</span>
                    </div>
                    <div class="row g-3">
                        @foreach ($issuePhotos as $photo)
                            <div class="col-6 col-md-4">
                                <a href="{{ $photo['uri'] }}" target="_blank" class="d-block">
                                    <div class="border bg-white shadow-sm overflow-hidden" style="height: 120px; border-radius: 12px;">
                                        <img src="{{ $photo['uri'] }}" alt="{{ $photo['file_name'] ?? 'Foto' }}" style="width:100%;height:100%;object-fit:cover;display:block;"
                                            onerror="this.closest('a').style.pointerEvents='none';this.nextElementSibling.style.display='flex';this.style.display='none';">
                                        <div class="d-none h-100 w-100 align-items-center justify-content-center bg-light text-muted px-2 text-center" style="font-size:12px;">Preview tidak tersedia</div>
                                    </div>
                                </a>
                                <a href="{{ $photo['uri'] }}" download="{{ $photo['file_name'] ?? 'foto.jpg' }}" class="d-inline-flex align-items-center text-primary mt-1 small" style="gap:4px"><i class="ri-download-2-line"></i> Unduh</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- SIDEBAR --}}
    <div class="col-12 col-xl-4 mb-4">
        {{-- Biaya --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Rincian Biaya</h5>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">{{ $isEventProject ? 'Biaya Konsultasi' : 'Biaya Survei' }}</span><span class="fw-semibold">Rp{{ number_format((int) $sr->survey_fee, 0, ',', '.') }}</span></div>
                @if ((int) $sr->products_amount > 0)
                    <div class="d-flex justify-content-between py-1"><span class="text-muted">Produk</span><span class="fw-semibold">Rp{{ number_format((int) $sr->products_amount, 0, ',', '.') }}</span></div>
                @endif
                @if ((int) $sr->discount_amount > 0)
                    <div class="d-flex justify-content-between py-1"><span class="text-muted">Diskon</span><span class="fw-semibold text-danger">-Rp{{ number_format((int) $sr->discount_amount, 0, ',', '.') }}</span></div>
                @endif
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Pajak {{ (int) $sr->tax_percentage }}%</span><span class="fw-semibold">Rp{{ number_format((int) $sr->tax_amount, 0, ',', '.') }}</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between"><span class="fw-bold">Total</span><span class="fw-bold text-success">Rp{{ number_format((int) $sr->total_amount, 0, ',', '.') }}</span></div>
            </div>
        </div>

        {{-- Pembayaran --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Pembayaran</h5>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Metode</span><span class="fw-semibold">{{ $sr->payment_method ? ucwords(str_replace('_', ' ', $sr->payment_method)) : '-' }}</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Status</span><span class="badge badge-sm badge-{{ $paymentColor }}">{{ $paymentLabels[$sr->payment_status] ?? ucfirst($sr->payment_status) }}</span></div>
                @if ($sr->paid_at)<div class="d-flex justify-content-between py-1"><span class="text-muted">Dibayar</span><span class="fw-semibold">{{ $sr->paid_at->format('d M Y H:i') }}</span></div>@endif

                @if ($canVerifyManual)
                    <hr class="my-2">
                    <div class="fw-semibold mb-2"><i class="ri-bank-line me-1"></i> Verifikasi Transfer Manual</div>
                    @if ($sr->payment_proof_path)
                        @php $proofUrl = Storage::disk('public')->url($sr->payment_proof_path); @endphp
                        <p class="text-muted small mb-2">Bukti diupload: {{ optional($sr->payment_proof_uploaded_at)?->format('d M Y H:i') ?? '-' }}</p>
                        @if (\Illuminate\Support\Str::endsWith(strtolower($sr->payment_proof_path), '.pdf'))
                            <a href="{{ $proofUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm mb-2"><i class="ri-file-pdf-line me-1"></i> Lihat Bukti (PDF)</a>
                        @else
                            <a href="{{ $proofUrl }}" target="_blank"><img src="{{ $proofUrl }}" class="img-fluid rounded border mb-2" style="max-height:220px;"></a>
                        @endif
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('admin.mobile.service_requests.confirm_payment', $sr->id) }}" class="flex-fill">@csrf
                                <button class="btn btn-success btn-sm w-100"><i class="ri-check-line me-1"></i> Lunas</button>
                            </form>
                            <form method="POST" action="{{ route('admin.mobile.service_requests.reject_payment', $sr->id) }}" class="flex-fill">@csrf
                                <button class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Tolak bukti transfer ini?')"><i class="ri-close-line me-1"></i> Tolak</button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted small mb-0">User belum mengunggah bukti transfer.</p>
                    @endif
                @endif
            </div>
        </div>

        {{-- Aksi Admin kontekstual --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Aksi</h5>

                @if (! $isPaid && ! $isRejected)
                    <div class="alert alert-info py-2 small mb-3"><i class="ri-information-line me-1"></i> Menunggu pembayaran user. Review tersedia setelah lunas.</div>
                @endif

                @if ($canReview)
                    <form method="POST" action="{{ route('admin.mobile.service_requests.approve', $sr->id) }}" class="mb-2">@csrf
                        <textarea name="admin_note" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan (opsional)">{{ old('admin_note', $sr->admin_note) }}</textarea>
                        <button class="btn btn-success w-100"><i class="ri-check-double-line me-1"></i> Approve Order</button>
                    </form>
                @endif

                @if ($canComplete)
                    <form method="POST" action="{{ route('admin.mobile.service_requests.complete', $sr->id) }}" class="mb-2">@csrf
                        <button class="btn btn-primary w-100"><i class="ri-flag-2-line me-1"></i> Tandai Selesai</button>
                    </form>
                @endif

                @if ($canReject)
                    <button class="btn btn-outline-danger w-100" type="button" data-bs-toggle="collapse" data-bs-target="#rejectForm"><i class="ri-close-circle-line me-1"></i> Reject Order</button>
                    <div class="collapse mt-2" id="rejectForm">
                        <form method="POST" action="{{ route('admin.mobile.service_requests.reject', $sr->id) }}">@csrf
                            <textarea name="rejection_reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Alasan penolakan (wajib)" required></textarea>
                            <button class="btn btn-danger btn-sm w-100">Konfirmasi Reject</button>
                        </form>
                    </div>
                @endif

                @if ($sr->admin_note)
                    <hr class="my-2">
                    <div class="k text-muted small text-uppercase">Catatan Admin</div>
                    <div class="small">{{ $sr->admin_note }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
