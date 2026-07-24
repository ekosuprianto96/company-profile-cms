@php
    $stars = function ($rating) {
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            $out .= '<i class="ri-star-fill" style="color:' . ($i <= $rating ? '#c8915c' : '#d9d9d9') . '"></i>';
        }
        return $out;
    };
@endphp

<div class="d-flex align-items-center mb-3" style="gap:12px">
    @if ($review->product?->primary_image)
        <img src="{{ storageUrl($review->product->primary_image) }}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:10px;">
    @else
        <span class="d-inline-flex align-items-center justify-content-center bg-light text-muted" style="width:56px;height:56px;border-radius:10px;"><i class="ri-image-line"></i></span>
    @endif
    <div>
        <div class="fw-bold">{{ $review->product?->name ?? '-' }}</div>
        <small class="text-muted">Rata-rata {{ number_format($review->product?->rating ?? 0, 1) }}★ dari {{ $review->product?->review_count ?? 0 }} ulasan</small>
    </div>
</div>

<div class="card border-0 bg-light mb-3"><div class="card-body py-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div style="font-size:20px;letter-spacing:2px">{!! $stars($review->rating) !!}</div>
        <span class="badge badge-light">{{ $review->rating }}/5</span>
    </div>
    @if ($review->comment)
        <p class="mb-0" style="white-space:pre-line">{{ $review->comment }}</p>
    @else
        <p class="mb-0 text-muted fst-italic">Pembeli tidak menulis komentar.</p>
    @endif
</div></div>

<div class="row small">
    <div class="col-6"><span class="text-muted d-block">Pembeli</span><span class="fw-semibold">{{ optional($review->user)->name ?? 'Pengguna' }}</span></div>
    <div class="col-6"><span class="text-muted d-block">No. Pesanan</span><span class="fw-semibold">{{ optional($review->order)->order_number ?? '-' }}</span></div>
    <div class="col-6 mt-2"><span class="text-muted d-block">Tanggal</span><span class="fw-semibold">{{ optional($review->created_at)->format('d M Y, H:i') }}</span></div>
</div>
