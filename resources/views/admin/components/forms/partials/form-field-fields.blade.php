@php
    $fd = $field ?? null;
    $curType = old('type', $fd->type ?? 'text');
    $curSource = old('options_source', $fd->options_source ?? 'static');
    $val = $fd->validation ?? [];
    $cond = $fd->conditional ?? [];
    $opts = $fd->options ?? [];
    $siblingFields = $siblingFields ?? collect();
@endphp

<form id="{{ $formId }}" class="forms-sample form-field-form">
    @csrf
    <input type="hidden" name="form_id" value="{{ $fd->form_id ?? $ownerFormId ?? request('form_id') }}">

    <div class="row">
        <div class="col-md-7 form-group">
            <label class="form-label">Label Pertanyaan</label>
            <input name="label" type="text" class="form-control" value="{{ optional($fd)->label }}" placeholder="mis. Jenis Proyek">
            <div data-error="label"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
        <div class="col-md-5 form-group">
            <label class="form-label">Kunci <small class="text-muted">(kosong = otomatis)</small></label>
            <input name="key" type="text" class="form-control" value="{{ optional($fd)->key }}" placeholder="jenis_proyek">
            <div data-error="key"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 form-group">
            <label class="form-label">Tipe Input</label>
            <select name="type" class="form-control js-field-type">
                @foreach ($fieldTypes as $key => $label)
                    <option value="{{ $key }}" @selected($curType === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5 form-group js-required-wrap">
            <label class="form-label">Wajib Diisi</label>
            <select name="is_required" class="form-control">
                <option value="0" @selected(!optional($fd)->is_required)>Tidak</option>
                <option value="1" @selected(optional($fd)->is_required)>Ya</option>
            </select>
        </div>
    </div>

    <div class="row js-basic-wrap">
        <div class="col-md-6 form-group">
            <label class="form-label">Placeholder <small class="text-muted">(1 baris = 1 instruksi; ≥2 baris → animasi mengetik, kecuali select)</small></label>
            <textarea name="placeholder" rows="2" class="form-control" placeholder="Contoh: Jl. Merdeka No.10&#10;Sertakan RT/RW &amp; patokan">{{ optional($fd)->placeholder }}</textarea>
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Teks Bantuan</label>
            <input name="help_text" type="text" class="form-control" value="{{ optional($fd)->help_text }}" placeholder="Petunjuk singkat di bawah input">
        </div>
    </div>

    <div class="row js-basic-wrap">
        <div class="col-md-12 form-group">
            <label class="form-label">Peran Data <small class="text-muted">(opsional — isi kolom Order Layanan)</small></label>
            <select name="role" class="form-control">
                <option value="">— Tidak dipetakan —</option>
                @foreach (config('form_builder.field_roles', []) as $key => $label)
                    <option value="{{ $key }}" @selected(old('role', $fd->role ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <small class="text-muted">Tandai bila field ini mengisi data order (mis. Lokasi Survei, Tanggal, Foto). Order Layanan yang memegang status.</small>
        </div>
    </div>

    {{-- ===== Opsi (select/multiselect/radio/checkbox_group) ===== --}}
    <div class="js-options-wrap card border mb-3" style="display:none">
        <div class="card-body py-3">
            <h6 class="mb-2">Daftar Opsi</h6>
            <div class="form-group mb-2">
                <label class="form-label">Sumber Opsi</label>
                <select name="options_source" class="form-control js-options-source">
                    @foreach ($optionsSources as $key => $label)
                        <option value="{{ $key }}" @selected($curSource === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Dari master data --}}
            <div class="form-group js-datasource-wrap" style="display:none">
                <label class="form-label">Ambil Data Dari</label>
                <select name="options_source_key" class="form-control">
                    <option value="">— Pilih sumber data —</option>
                    @foreach ($datasources as $key => $meta)
                        <option value="{{ $key }}" @selected(optional($fd)->options_source_key === $key)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Opsi otomatis mengikuti master data ini (selalu terbaru).</small>
                <div data-error="options_source_key"><span class="text-danger" style="font-size:.8em"></span></div>
            </div>

            {{-- Manual --}}
            <div class="js-static-wrap" style="display:none">
                <label class="form-label">Opsi Manual <small class="text-muted">(seret <i class="ri-draggable"></i> untuk mengatur urutan)</small></label>
                <div class="js-option-rows">
                    @forelse ($opts as $i => $opt)
                        <div class="d-flex align-items-center mb-2 js-option-row" style="gap:8px">
                            <i class="ri-draggable js-opt-handle" style="cursor:grab;color:#b6c0cc;font-size:20px"></i>
                            <input type="text" class="form-control form-control-sm js-opt-label" placeholder="Label" value="{{ $opt['label'] ?? '' }}">
                            <input type="text" class="form-control form-control-sm js-opt-value" placeholder="Nilai (kosong = sama dgn label)" value="{{ $opt['value'] ?? '' }}">
                            <button type="button" class="btn btn-danger btn-xs js-opt-remove"><i class="ri-close-line"></i></button>
                        </div>
                    @empty
                        <div class="d-flex align-items-center mb-2 js-option-row" style="gap:8px">
                            <i class="ri-draggable js-opt-handle" style="cursor:grab;color:#b6c0cc;font-size:20px"></i>
                            <input type="text" class="form-control form-control-sm js-opt-label" placeholder="Label">
                            <input type="text" class="form-control form-control-sm js-opt-value" placeholder="Nilai (kosong = sama dgn label)">
                            <button type="button" class="btn btn-danger btn-xs js-opt-remove"><i class="ri-close-line"></i></button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-light btn-sm js-opt-add"><i class="ri-add-line"></i> Tambah Opsi</button>
            </div>
        </div>
    </div>

    {{-- ===== Validasi teks ===== --}}
    <div class="row js-val-text" style="display:none">
        <div class="col-md-6 form-group">
            <label class="form-label">Min. Karakter</label>
            <input name="validation[min_length]" type="number" min="0" class="form-control" value="{{ $val['min_length'] ?? '' }}">
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Maks. Karakter</label>
            <input name="validation[max_length]" type="number" min="0" class="form-control" value="{{ $val['max_length'] ?? '' }}">
        </div>
    </div>

    {{-- ===== Validasi angka ===== --}}
    <div class="row js-val-number" style="display:none">
        <div class="col-md-6 form-group">
            <label class="form-label">Nilai Minimum</label>
            <input name="validation[min]" type="number" class="form-control" value="{{ $val['min'] ?? '' }}">
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Nilai Maksimum</label>
            <input name="validation[max]" type="number" class="form-control" value="{{ $val['max'] ?? '' }}">
        </div>
    </div>

    {{-- ===== Validasi berkas ===== --}}
    <div class="row js-val-file" style="display:none">
        <div class="col-md-5 form-group">
            <label class="form-label">Tipe Berkas Diizinkan</label>
            <input name="validation[accept]" type="text" class="form-control" value="{{ $val['accept'] ?? '' }}" placeholder="pdf,doc,docx">
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Maks. Ukuran (MB)</label>
            <input name="validation[max_size_mb]" type="number" min="1" class="form-control" value="{{ $val['max_size_mb'] ?? '' }}" placeholder="10">
        </div>
        <div class="col-md-3 form-group">
            <label class="form-label">Maks. Jumlah</label>
            <input name="validation[max_files]" type="number" min="1" class="form-control" value="{{ $val['max_files'] ?? '' }}" placeholder="1">
        </div>
    </div>

    {{-- ===== Kondisional ===== --}}
    <div class="card border mb-2">
        <div class="card-body py-3">
            <h6 class="mb-2">Tampilkan Jika <small class="text-muted">(opsional)</small></h6>
            <div class="row">
                <div class="col-md-5 form-group mb-0">
                    <select name="conditional[field]" class="form-control">
                        <option value="">— Selalu tampil —</option>
                        @foreach ($siblingFields as $sib)
                            <option value="{{ $sib->key }}" @selected(($cond['field'] ?? null) === $sib->key)>{{ $sib->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-0">
                    <select name="conditional[operator]" class="form-control">
                        @foreach (['eq' => 'sama dengan', 'neq' => 'tidak sama dengan', 'filled' => 'terisi'] as $op => $opLabel)
                            <option value="{{ $op }}" @selected(($cond['operator'] ?? 'eq') === $op)>{{ $opLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group mb-0">
                    <input name="conditional[value]" type="text" class="form-control" value="{{ $cond['value'] ?? '' }}" placeholder="Nilai">
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    const form = document.getElementById('{{ $formId }}');
    if (!form) return;
    const $form = $(form);

    const OPTION_TYPES = @json($optionTypes);
    const DISPLAY_TYPES = @json($displayTypes);
    const FILE_TYPES = @json($fileTypes);
    const TEXT_TYPES = ['text', 'textarea', 'email', 'phone'];

    function sync() {
        const type = $form.find('.js-field-type').val();
        const isOption = OPTION_TYPES.includes(type);
        const isDisplay = DISPLAY_TYPES.includes(type);

        $form.find('.js-options-wrap').toggle(isOption);
        $form.find('.js-required-wrap').toggle(!isDisplay);
        $form.find('.js-basic-wrap').toggle(!isDisplay);
        $form.find('.js-val-text').toggle(TEXT_TYPES.includes(type));
        $form.find('.js-val-number').toggle(type === 'number');
        $form.find('.js-val-file').toggle(FILE_TYPES.includes(type));

        const source = $form.find('.js-options-source').val();
        $form.find('.js-datasource-wrap').toggle(isOption && source === 'datasource');
        $form.find('.js-static-wrap').toggle(isOption && source !== 'datasource');
    }

    function optionRowTemplate() {
        return '<div class="d-flex align-items-center mb-2 js-option-row" style="gap:8px">' +
            '<i class="ri-draggable js-opt-handle" style="cursor:grab;color:#b6c0cc;font-size:20px"></i>' +
            '<input type="text" class="form-control form-control-sm js-opt-label" placeholder="Label">' +
            '<input type="text" class="form-control form-control-sm js-opt-value" placeholder="Nilai (kosong = sama dgn label)">' +
            '<button type="button" class="btn btn-danger btn-xs js-opt-remove"><i class="ri-close-line"></i></button></div>';
    }

    // Drag & drop urutan opsi (jQuery UI sortable dimuat di layout). Urutan DOM =
    // urutan tersimpan (serializeFieldForm membaca baris sesuai urutan DOM).
    function initOptionSortable() {
        const $rows = $form.find('.js-option-rows');
        if (!$.fn.sortable || !$rows.length) return;
        if ($rows.hasClass('ui-sortable')) return;
        $rows.sortable({ handle: '.js-opt-handle', axis: 'y', tolerance: 'pointer', containment: 'parent' });
    }

    $form.on('change', '.js-field-type, .js-options-source', sync);
    $form.on('click', '.js-opt-add', function () { $form.find('.js-option-rows').append(optionRowTemplate()); });
    $form.on('click', '.js-opt-remove', function () {
        const $rows = $form.find('.js-option-row');
        if ($rows.length > 1) { $(this).closest('.js-option-row').remove(); }
        else { $rows.find('input').val(''); }
    });

    // Serialize opsi manual jadi options[i][label|value] sebelum submit.
    window.serializeFieldForm = function (formId) {
        const $f = $('#' + formId);
        $f.find('input[name^="options["]').remove();
        if (OPTION_TYPES.includes($f.find('.js-field-type').val()) && $f.find('.js-options-source').val() !== 'datasource') {
            $f.find('.js-option-row').each(function (i) {
                const label = $(this).find('.js-opt-label').val();
                const value = $(this).find('.js-opt-value').val();
                if (!label) return;
                $f.append(`<input type="hidden" name="options[${i}][label]" value="${$('<div>').text(label).html()}">`);
                $f.append(`<input type="hidden" name="options[${i}][value]" value="${$('<div>').text(value || label).html()}">`);
            });
        }
        return $f.serialize();
    };

    sync();
    initOptionSortable();
})();
</script>
