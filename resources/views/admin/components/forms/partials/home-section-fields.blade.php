@php
    $formId = $formId ?? 'homeSectionForm';
    $s = $section ?? null;
    $curSource = old('source_type', $s->source_type ?? 'product');
    $curLayout = old('layout', $s->layout ?? 'slider');
    $curMode = old('selection_mode', $s->selection_mode ?? 'auto');
    $curFilter = old('auto_filter', $s->auto_filter ?? null);
    $selectedItemIds = $selectedItemIds ?? [];
@endphp

<form id="{{ $formId }}" class="forms-sample home-section-form">
    @csrf
    <div class="row">
        <div class="col-md-8 form-group">
            <label class="form-label">Judul Section</label>
            <input name="title" type="text" class="form-control" value="{{ old('title', $s->title ?? '') }}" placeholder="mis. Produk Pilihan">
            <div data-error="title"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Maks. Item</label>
            <input name="max_items" type="number" min="1" max="50" class="form-control" value="{{ old('max_items', $s->max_items ?? 8) }}">
            <div data-error="max_items"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Subjudul <small class="text-muted">(opsional)</small></label>
        <input name="subtitle" type="text" class="form-control" value="{{ old('subtitle', $s->subtitle ?? '') }}" placeholder="mis. Produk pilihan terbaik untukmu">
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Sumber Data</label>
            <select name="source_type" class="form-control js-source">
                @foreach ($sources as $key => $label)
                    <option value="{{ $key }}" @selected($curSource === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <div data-error="source_type"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Layout</label>
            <select name="layout" class="form-control">
                @foreach ($layouts as $key => $label)
                    <option value="{{ $key }}" @selected($curLayout === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Cara Pilih Data</label>
            <select name="selection_mode" class="form-control js-mode">
                @foreach ($selectionModes as $key => $label)
                    <option value="{{ $key }}" @selected($curMode === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group js-auto-wrap">
        <label class="form-label">Filter Otomatis</label>
        <select name="auto_filter" class="form-control js-auto-filter"></select>
        <small class="text-muted">Data diambil otomatis sesuai aturan ini.</small>
        <div data-error="auto_filter"><span class="text-danger" style="font-size:.8em"></span></div>
    </div>

    <div class="form-group js-manual-wrap" style="display:none;">
        <label class="form-label">Pilih Item</label>
        <select name="item_ids[]" class="form-control js-manual-items" multiple></select>
        <small class="text-muted">Urutan pemilihan = urutan tampil. Kosongkan filter otomatis saat mode ini.</small>
        <div data-error="item_ids"><span class="text-danger" style="font-size:.8em"></span></div>
    </div>

    <div class="row">
        <div class="col-md-8 form-group">
            <label class="form-label">Target "Lihat Semua" <small class="text-muted">(opsional, mis. /products)</small></label>
            <input name="view_all_target" type="text" class="form-control" value="{{ old('view_all_target', $s->view_all_target ?? '') }}" placeholder="/products">
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-control">
                <option value="1" @selected(old('is_active', $s->is_active ?? true))>Aktif</option>
                <option value="0" @selected($s && ! $s->is_active)>Nonaktif</option>
            </select>
        </div>
    </div>
</form>

<script>
(function () {
    const form = document.getElementById('{{ $formId }}');
    if (!form) return;
    const $form = $(form);
    const $modal = $form.closest('.modal');

    const AUTO_FILTERS = @json($autoFilters);
    const SELECTED_ITEMS = @json(array_values($selectedItemIds));
    const CURRENT_FILTER = @json($curFilter);
    const SOURCE_ITEMS_URL = '{{ route('admin.mobile.home_sections.source_items') }}';

    const $source = $form.find('.js-source');
    const $mode = $form.find('.js-mode');
    const $autoWrap = $form.find('.js-auto-wrap');
    const $autoFilter = $form.find('.js-auto-filter');
    const $manualWrap = $form.find('.js-manual-wrap');
    const $items = $form.find('.js-manual-items');

    let itemsLoadedFor = null; // cache source terakhir yang di-load untuk picker

    function rebuildAutoFilter() {
        const source = $source.val();
        const opts = AUTO_FILTERS[source] || {};
        const prev = $autoFilter.val() || CURRENT_FILTER;
        $autoFilter.empty();
        Object.keys(opts).forEach((key) => {
            $autoFilter.append(new Option(opts[key], key, false, String(key) === String(prev)));
        });
    }

    function ensureItemsSelect2() {
        if ($.fn.select2 && !$items.hasClass('select2-hidden-accessible')) {
            $items.select2({ width: '100%', placeholder: 'Pilih item…', closeOnSelect: false,
                dropdownParent: $modal.length ? $modal : $(document.body) });
        }
    }

    function loadItems(preselect) {
        const source = $source.val();
        $.get(SOURCE_ITEMS_URL, { source_type: source }).done(function (res) {
            const items = (res && res.items) || [];
            $items.empty();
            items.forEach((it) => $items.append(new Option(it.label, it.id, false, false)));
            ensureItemsSelect2();
            if (preselect && preselect.length) {
                $items.val(preselect.map(String)).trigger('change');
            }
            itemsLoadedFor = source;
        });
    }

    function syncMode() {
        const manual = $mode.val() === 'manual';
        $autoWrap.toggle(!manual);
        $manualWrap.toggle(manual);
        if (manual && itemsLoadedFor !== $source.val()) {
            loadItems(itemsLoadedFor === null ? SELECTED_ITEMS : []);
        }
    }

    $source.on('change', function () {
        rebuildAutoFilter();
        if ($mode.val() === 'manual') loadItems([]); // ganti source -> reset pilihan
    });
    $mode.on('change', syncMode);

    rebuildAutoFilter();
    syncMode();
})();
</script>
