@php
    $formOptions = $formOptions ?? collect();
    $stepTemplateOptions = $stepTemplateOptions ?? collect();
    $priceTypes = $priceTypes ?? [];
    $priceItems = ($priceItems ?? collect())->values();
    $selectedFormId = optional($service ?? null)->form_id;
    $selectedStepTemplateId = optional($service ?? null)->step_template_id;
@endphp

<div class="form-group">
    <label class="form-label">Form Pengajuan</label>
    <select name="form_id" class="form-control">
        <option value="">— Tanpa form (pakai form bawaan) —</option>
        @foreach ($formOptions as $opt)
            <option value="{{ $opt->id }}" @selected($selectedFormId == $opt->id)>{{ $opt->name }}</option>
        @endforeach
    </select>
    <small class="text-muted">Schema form ini yang dirender aplikasi mobile saat user mengajukan layanan. Satu form boleh dipakai banyak layanan.</small>
</div>

<div class="form-group">
    <label class="form-label">Template Rules Step</label>
    <select name="step_template_id" class="form-control">
        <option value="">— Pakai template default —</option>
        @foreach ($stepTemplateOptions as $opt)
            <option value="{{ $opt->id }}" @selected($selectedStepTemplateId == $opt->id)>{{ $opt->name }}{{ $opt->is_default ? ' (default)' : '' }}</option>
        @endforeach
    </select>
    <small class="text-muted">Langkah "Status Pengajuan" yang tampil ke user untuk layanan ini. Satu template boleh dipakai banyak layanan.</small>
</div>

<div class="card border mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h6 class="mb-0">Skema Harga Layanan</h6>
                <small class="text-muted">Komponen biaya layanan ini, mis. "Biaya Survei" atau "Biaya Konsultasi". Kosongkan bila layanan tidak memungut biaya di awal.</small>
            </div>
            <button type="button" class="btn btn-light btn-sm js-price-add"><i class="ri-add-line"></i> Tambah Biaya</button>
        </div>

        <div class="js-price-rows">
            @forelse ($priceItems as $item)
                <div class="row g-2 mb-2 js-price-row">
                    <div class="col-md-3">
                        <select class="form-control form-control-sm js-price-type">
                            @foreach ($priceTypes as $key => $label)
                                <option value="{{ $key }}" @selected($item->type === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm js-price-label" placeholder="Label tampil (mis. Biaya Survei)" value="{{ $item->label }}"></div>
                    <div class="col-md-3"><input type="number" min="0" class="form-control form-control-sm js-price-amount" placeholder="Nominal" value="{{ $item->amount }}"></div>
                    <div class="col-md-1">
                        <select class="form-control form-control-sm js-price-required">
                            <option value="1" @selected($item->is_required)>Wajib</option>
                            <option value="0" @selected(!$item->is_required)>Ops.</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-xs js-price-remove"><i class="ri-close-line"></i></button></div>
                </div>
            @empty
                <div class="row g-2 mb-2 js-price-row">
                    <div class="col-md-3">
                        <select class="form-control form-control-sm js-price-type">
                            @foreach ($priceTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm js-price-label" placeholder="Label tampil (mis. Biaya Survei)"></div>
                    <div class="col-md-3"><input type="number" min="0" class="form-control form-control-sm js-price-amount" placeholder="Nominal"></div>
                    <div class="col-md-1">
                        <select class="form-control form-control-sm js-price-required">
                            <option value="1">Wajib</option>
                            <option value="0">Ops.</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-xs js-price-remove"><i class="ri-close-line"></i></button></div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
(function () {
    const $wrap = $('.js-price-rows').last();
    if (!$wrap.length) return;
    const $card = $wrap.closest('.card');

    function template() {
        return $wrap.find('.js-price-row').first().clone().find('input').val('').end();
    }

    $card.on('click', '.js-price-add', function () { $wrap.append(template()); });
    $card.on('click', '.js-price-remove', function () {
        const $rows = $wrap.find('.js-price-row');
        if ($rows.length > 1) { $(this).closest('.js-price-row').remove(); }
        else { $rows.find('input').val(''); }
    });

    /** Dipakai form layanan saat submit: kumpulkan baris jadi price_items[i][...]. */
    window.collectServicePriceItems = function () {
        const items = [];
        $wrap.find('.js-price-row').each(function () {
            const label = $(this).find('.js-price-label').val();
            if (!label) return;
            items.push({
                type: $(this).find('.js-price-type').val(),
                label,
                amount: $(this).find('.js-price-amount').val() || 0,
                is_required: $(this).find('.js-price-required').val(),
            });
        });
        return items;
    };
})();
</script>
