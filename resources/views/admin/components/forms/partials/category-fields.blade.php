@php
    $formId = $formId ?? 'categoryForm';
@endphp

<form id="{{ $formId }}" class="forms-sample">
    @csrf

    <div class="form-group">
        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
        <input name="name" type="text" class="form-control" value="{{ old('name', $category?->name) }}" placeholder="mis. Pekerjaan Sipil">
        <div class="invalid-feedback d-block" data-error="name"><span></span></div>
    </div>

    <div class="form-group">
        <label class="form-label">Induk Kategori</label>
        <select name="parent_id" class="form-control">
            <option value="">— Kategori Utama (tanpa induk) —</option>
            @foreach (($parentOptions ?? []) as $opt)
                <option value="{{ $opt['id'] }}" @selected((int) old('parent_id', $category?->parent_id) === $opt['id'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
        <small class="text-muted">Kosongkan untuk kategori utama. Pilih induk untuk menjadikannya sub-kategori.</small>
        <div class="invalid-feedback d-block" data-error="parent_id"><span></span></div>
    </div>

    <div class="row">
        <div class="col-md-7">
            @include('admin.components.forms.partials.icon-picker', [
                'iconFieldName' => 'icon',
                'iconLabel' => 'Ikon (MaterialIcons)',
                'iconRequired' => true,
                'selectedIcon' => old('icon', $category?->icon),
            ])
        </div>
        <div class="col-md-5 form-group">
            <label class="form-label">Urutan</label>
            <input name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $category?->sort_order ?? 0) }}">
            <div class="invalid-feedback d-block" data-error="sort_order"><span></span></div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="is_active" class="form-control">
            <option value="1" @selected(old('is_active', (string) ($category?->is_active ?? '1')) == '1')>Aktif</option>
            <option value="0" @selected(old('is_active', (string) ($category?->is_active ?? '1')) == '0')>Nonaktif</option>
        </select>
        <div class="invalid-feedback d-block" data-error="is_active"><span></span></div>
    </div>
</form>
