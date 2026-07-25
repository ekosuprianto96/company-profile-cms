@php
    $slide = $slide ?? [];
    $rowNumber = is_numeric($index) ? ((int) $index + 1) : 1;
    $slideId = old("onboarding_slides.$index.id", data_get($slide, 'id', 'slide-' . $rowNumber));
    $title = old("onboarding_slides.$index.title", data_get($slide, 'title', ''));
    $subtitle = old("onboarding_slides.$index.subtitle", data_get($slide, 'subtitle', ''));
    $imagePath = old("onboarding_slides.$index.image_path", data_get($slide, 'image_path', ''));
    $sortOrder = old("onboarding_slides.$index.sort_order", data_get($slide, 'sort_order', $rowNumber));
    $imageUrl = $imagePath ? storageUrl($imagePath) : null;
@endphp

<div class="card border mb-3" data-onboarding-row>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge px-3 py-2" style="background:#eef5f4;color:#275a56;" data-onboarding-index>Slide #{{ $rowNumber }}</span>
            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-onboarding>
                <i class="ri-delete-bin-5-line me-1"></i> Hapus
            </button>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Gambar (rasio potret)</label>
                <div class="rounded border d-flex align-items-center justify-content-center overflow-hidden bg-light" style="height: 150px;" data-onboarding-preview>
                    @if ($imageUrl)
                        <img src="{{ $imageUrl }}" alt="preview" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <span class="text-muted small text-center px-2"><i class="ri-image-line d-block mb-1" style="font-size:22px;"></i>Belum ada gambar</span>
                    @endif
                </div>
                <input type="file" accept="image/*" class="form-control form-control-sm mt-2" data-onboarding-image>
                <input type="hidden" data-onboarding-field="image_path" value="{{ $imagePath }}">
                <input type="hidden" data-onboarding-field="id" value="{{ $slideId }}">
            </div>

            <div class="col-md-9">
                <div class="mb-2">
                    <label class="form-label small">Judul</label>
                    <input type="text" maxlength="150" class="form-control" data-onboarding-field="title" value="{{ $title }}" placeholder="mis. Wujudkan rumah impian...">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Subjudul</label>
                    <textarea rows="2" maxlength="300" class="form-control" data-onboarding-field="subtitle" placeholder="Deskripsi singkat slide">{{ $subtitle }}</textarea>
                </div>
                <div style="max-width: 140px;">
                    <label class="form-label small">Urutan</label>
                    <input type="number" min="0" class="form-control form-control-sm" data-onboarding-field="sort_order" value="{{ $sortOrder }}">
                </div>
            </div>
        </div>
    </div>
</div>
