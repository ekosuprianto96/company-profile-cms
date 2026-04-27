@extends('admin.layouts.main')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $statusColor = match ($serviceRequest->status) {
        'approved', 'completed' => 'success',
        'rejected', 'failed' => 'danger',
        'waiting_payment', 'waiting_transfer', 'payment_challenge' => 'warning',
        default => 'secondary',
    };

    $paymentColor = match ($serviceRequest->payment_status) {
        'paid' => 'success',
        'pending' => 'warning',
        'challenge' => 'danger',
        'failed' => 'danger',
        default => 'secondary',
    };

    $resolvePhotoUrl = function ($photo) {
        $normalize = function (?string $value) {
            if (! $value) {
                return null;
            }

            $value = trim($value);

            if ($value === '') {
                return null;
            }

            if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                return $value;
            }

            if (str_starts_with($value, '/')) {
                return asset(ltrim($value, '/'));
            }

            if (str_starts_with($value, 'mobile/service-requests/') || str_starts_with($value, 'storage/')) {
                return Storage::disk('public')->url(ltrim($value, '/'));
            }

            if (str_starts_with($value, 'assets/')) {
                return asset($value);
            }

            return Storage::disk('public')->url(ltrim($value, '/'));
        };

        $isLocalUri = function (?string $value) {
            if (! $value) {
                return false;
            }

            return str_starts_with($value, 'expo-file://')
                || str_starts_with($value, 'file://')
                || str_starts_with($value, 'content://');
        };

        if (is_string($photo)) {
            if ($isLocalUri($photo)) {
                return null;
            }

            $resolved = $normalize($photo);

            return $resolved ? [
                'uri' => $resolved,
                'file_name' => basename($resolved),
            ] : null;
        }

        if (! is_array($photo)) {
            return null;
        }

        $fileName = $photo['file_name'] ?? null;
        $url = $normalize($photo['url'] ?? null);
        $path = $normalize($photo['path'] ?? null);
        $uri = $photo['uri'] ?? null;

        if ($url) {
            return [
                'uri' => $url,
                'file_name' => $fileName ?? basename($url),
            ];
        }

        if ($path) {
            if ($isLocalUri($path)) {
                return null;
            }

            return [
                'uri' => str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
                    ? $path
                    : Storage::disk('public')->url(ltrim($path, '/')),
                'file_name' => $fileName ?? basename($path),
            ];
        }

        if ($isLocalUri($uri)) {
            $fallback = $fileName ? route('admin.mobile.service_requests.photo', ['file' => $fileName]) : null;

            return $fallback ? [
                'uri' => $fallback,
                'file_name' => $fileName,
            ] : null;
        }

        $resolvedUri = $normalize($uri);

        if (! $resolvedUri) {
            return null;
        }

        return [
            'uri' => $resolvedUri,
            'file_name' => $fileName ?? basename($resolvedUri),
        ];
    };

    $issuePhotos = collect($serviceRequest->issue_photos ?? [])
        ->map(fn ($photo) => $resolvePhotoUrl($photo))
        ->filter(fn ($photo) => is_array($photo) && ! empty($photo['uri']))
        ->values();
@endphp

<div class="row align-items-start">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Detail Pengajuan {{ $serviceRequest->transaction_code_label }}</h4>
                    <p class="text-muted mb-0">Review data pengajuan, download proposal PDF, lalu approve atau reject sesuai hasil pengecekan lapangan.</p>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-end" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.service_requests.download', $serviceRequest->id) }}" class="btn btn-primary btn-sm">
                        <i class="ri-download-2-line me-1"></i> Download PDF
                    </a>
                    <a href="{{ route('admin.mobile.service_requests.chat_user', $serviceRequest->id) }}" class="btn btn-info btn-sm">
                        <i class="ri-message-3-line me-1"></i> Chat User
                    </a>
                    <a href="{{ route('admin.mobile.service_requests.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4" style="gap: 12px;">
                    <div>
                        <div class="text-muted text-uppercase mb-1" style="font-size: 12px; letter-spacing: 1px;">Ringkasan Pengajuan</div>
                        <h4 class="mb-1">{{ $serviceRequest->service?->title ?? '-' }}</h4>
                        <p class="mb-0 text-muted">{{ $serviceRequest->needType?->name ?? '-' }}</p>
                        <div class="mt-2">
                            <span class="badge badge-sm badge-secondary">{{ $serviceRequest->transaction_code_label }}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end" style="gap: 8px;">
                        <span class="badge badge-sm badge-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}</span>
                        <span class="badge badge-sm badge-{{ $paymentColor }}">{{ ucfirst(str_replace('_', ' ', $serviceRequest->payment_status)) }}</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Pemesan</label>
                        <input type="text" class="form-control" value="{{ $serviceRequest->user?->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" value="{{ $serviceRequest->user?->phone ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="{{ $serviceRequest->user?->email ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Bangunan</label>
                        <input type="text" class="form-control" value="{{ $serviceRequest->building_label ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kode Pengajuan</label>
                        <input type="text" class="form-control" value="{{ $serviceRequest->transaction_code_label }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lokasi Survey</label>
                        <textarea
                            class="form-control"
                            readonly
                            rows="3"
                            style="min-height: 96px; resize: none; white-space: pre-wrap; line-height: 1.55; padding-top: 12px;"
                        >{{ $serviceRequest->survey_address ?? '-' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat</label>
                        @php
                            $surveyRegion = $serviceRequest->survey_region ?? data_get($serviceRequest->draft_payload, 'surveyRegion');
                            $regionLabel = collect([
                                data_get($surveyRegion, 'village.name'),
                                data_get($surveyRegion, 'district.name'),
                                data_get($surveyRegion, 'regency.name'),
                                data_get($surveyRegion, 'province.name'),
                            ])->filter(fn ($value) => is_string($value) && trim($value) !== '')->implode(', ');
                        @endphp
                        <textarea
                            class="form-control"
                            readonly
                            rows="3"
                            style="min-height: 96px; resize: none; white-space: pre-wrap; line-height: 1.55; padding-top: 12px;"
                        >{{ $regionLabel ?: '-' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Survey</label>
                        <input type="text" class="form-control" value="{{ optional($serviceRequest->survey_date)?->format('d M Y') ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Perkiraan Anggaran</label>
                        <input type="text" class="form-control" value="{{ $serviceRequest->budgetOption?->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kebutuhan</label>
                        <input type="text" class="form-control" value="{{ $serviceRequest->needType?->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan Masalah</label>
                        <textarea
                            class="form-control"
                            readonly
                            rows="5"
                            style="min-height: 140px; resize: none; white-space: pre-wrap; line-height: 1.65; padding-top: 14px; vertical-align: top;"
                        >{{ $serviceRequest->description ?? '-' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4 mb-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Biaya</h5>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 bg-light">
                        <div class="fw-bold">Biaya Survey</div>
                        <div class="text-muted">Rp{{ number_format((int) $serviceRequest->survey_fee, 0, ',', '.') }}</div>
                    </div>
                    <div class="p-3 bg-light">
                        <div class="fw-bold">Pajak</div>
                        <div class="text-muted">{{ (int) $serviceRequest->tax_percentage }}% (Rp{{ number_format((int) $serviceRequest->tax_amount, 0, ',', '.') }})</div>
                    </div>
                    <div class="p-3 bg-light">
                        <div class="fw-bold">Total</div>
                        <div class="text-muted">Rp{{ number_format((int) $serviceRequest->total_amount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Foto Masalah</h5>
                        <p class="text-muted mb-0">Lampiran foto yang diupload user dari aplikasi mobile.</p>
                    </div>
                    <span class="badge badge-sm badge-secondary">{{ $issuePhotos->count() }} foto</span>
                </div>

                @if ($issuePhotos->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($issuePhotos as $photo)
                            <div class="col-6">
                                <a href="{{ $photo['uri'] }}" target="_blank" class="text-decoration-none d-block">
                                    <div class="border bg-white shadow-sm overflow-hidden" style="height: 120px; border-radius: 12px;">
                                        <img
                                            src="{{ $photo['uri'] }}"
                                            alt="{{ $photo['file_name'] ?? 'Foto masalah' }}"
                                            style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                            onerror="this.closest('a').style.pointerEvents='none'; this.closest('a').querySelector('.photo-fallback').style.display='flex'; this.style.display='none';"
                                        >
                                        <div class="photo-fallback d-none h-100 w-100 align-items-center justify-content-center bg-light text-muted px-3 text-center" style="font-size: 12px;">
                                            Preview tidak tersedia
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-light text-muted">
                        Tidak ada foto masalah yang diupload.
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">Aksi Admin</h5>

                <form method="POST" action="{{ route('admin.mobile.service_requests.update_status', $serviceRequest->id) }}">
                    @csrf
                    <label class="form-label">Status Pengajuan</label>
                    <select name="status" class="form-control mb-3">
                        <option value="approved" @selected($serviceRequest->status === 'approved')>Approved</option>
                        <option value="completed" @selected($serviceRequest->status === 'completed')>Completed</option>
                        <option value="rejected" @selected($serviceRequest->status === 'rejected')>Rejected</option>
                    </select>

                    <label class="form-label">Catatan Admin</label>
                    <textarea class="form-control mb-3" name="admin_note" rows="4" placeholder="Opsional: catatan untuk user atau tim lapangan">{{ old('admin_note', $serviceRequest->admin_note) }}</textarea>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-save-3-line me-1"></i> Simpan Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
