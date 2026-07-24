@php
    $formId = $formId ?? 'promotionForm';
    $bannerUrl = $promotion?->banner_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($promotion->banner_image) : null;
    $coverUrl = $promotion?->cover_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($promotion->cover_image) : null;
    $fmt = fn ($date) => $date?->format('Y-m-d\TH:i');
@endphp

<form id="{{ $formId }}" class="forms-sample" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label class="form-label">Judul Promosi <span class="text-danger">*</span></label>
        <input name="title" type="text" class="form-control" value="{{ old('title', $promotion?->title) }}" placeholder="mis. Super Promo Ramadan">
        <div class="invalid-feedback d-block" data-error="title"><span></span></div>
    </div>

    <div class="form-group">
        <label class="form-label">Penempatan <span class="text-danger">*</span></label>
        <select name="placement" class="form-control">
            <option value="promo" @selected(old('placement', $promotion?->placement ?? 'promo') === 'promo')>Section Promosi — strip di tengah beranda</option>
            <option value="hero" @selected(old('placement', $promotion?->placement) === 'hero')>Slider Utama — banner besar paling atas</option>
        </select>
        <small class="text-muted">Slider utama memakai gambar besar (rasio ±4:3). Section promosi memakai strip lebar (±3:1).</small>
        <div class="invalid-feedback d-block" data-error="placement"><span></span></div>
    </div>

    <div class="form-group">
        <label class="form-label">Ringkasan</label>
        <input name="summary" type="text" class="form-control" value="{{ old('summary', $promotion?->summary) }}" placeholder="Satu kalimat singkat, tampil di bawah judul">
        <div class="invalid-feedback d-block" data-error="summary"><span></span></div>
    </div>

    <div class="form-group">
        <label class="form-label">Detail &amp; Syarat Ketentuan</label>
        <textarea name="content" rows="6" class="form-control" placeholder="Penjelasan promo, cara ikut, dan syarat & ketentuan.">{{ old('content', $promotion?->content) }}</textarea>
        <div class="invalid-feedback d-block" data-error="content"><span></span></div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label">Gambar Banner <span class="text-danger">*</span></label>
            <input name="banner_image" type="file" accept="image/*" class="form-control">
            <small class="text-muted">Artwork yang tampil di beranda, sesuai penempatan di atas.</small>
            @if ($bannerUrl)
                <div class="mt-2"><img src="{{ $bannerUrl }}" style="height:44px;border-radius:6px;object-fit:cover;"></div>
            @endif
            <div class="invalid-feedback d-block" data-error="banner_image"><span></span></div>
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Gambar Halaman Detail</label>
            <input name="cover_image" type="file" accept="image/*" class="form-control">
            <small class="text-muted">Opsional — bila kosong, banner beranda yang dipakai.</small>
            @if ($coverUrl)
                <div class="mt-2"><img src="{{ $coverUrl }}" style="height:44px;border-radius:6px;object-fit:cover;"></div>
            @endif
            <div class="invalid-feedback d-block" data-error="cover_image"><span></span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label">Mulai Tayang</label>
            <input name="starts_at" type="datetime-local" class="form-control" value="{{ old('starts_at', $fmt($promotion?->starts_at)) }}">
            <small class="text-muted">Kosongkan bila langsung tayang.</small>
            <div class="invalid-feedback d-block" data-error="starts_at"><span></span></div>
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Berakhir</label>
            <input name="ends_at" type="datetime-local" class="form-control" value="{{ old('ends_at', $fmt($promotion?->ends_at)) }}">
            <small class="text-muted">Kosongkan bila tanpa batas waktu.</small>
            <div class="invalid-feedback d-block" data-error="ends_at"><span></span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label">Label Tombol</label>
            <input name="cta_label" type="text" class="form-control" value="{{ old('cta_label', $promotion?->cta_label) }}" placeholder="mis. Ajukan Sekarang">
            <div class="invalid-feedback d-block" data-error="cta_label"><span></span></div>
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Tujuan Tombol</label>
            <input name="cta_url" type="text" class="form-control" value="{{ old('cta_url', $promotion?->cta_url) }}" placeholder="mis. /service-request atau /products">
            <small class="text-muted">Rute di dalam aplikasi. Kosongkan bila tanpa tombol.</small>
            <div class="invalid-feedback d-block" data-error="cta_url"><span></span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label">Urutan</label>
            <input name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $promotion?->sort_order ?? 0) }}">
            <small class="text-muted">Makin kecil, makin awal tampil.</small>
            <div class="invalid-feedback d-block" data-error="sort_order"><span></span></div>
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-control">
                <option value="1" @selected(old('is_active', $promotion?->is_active ?? true))>Aktif</option>
                <option value="0" @selected(!old('is_active', $promotion?->is_active ?? true))>Nonaktif</option>
            </select>
            <div class="invalid-feedback d-block" data-error="is_active"><span></span></div>
        </div>
    </div>
</form>
