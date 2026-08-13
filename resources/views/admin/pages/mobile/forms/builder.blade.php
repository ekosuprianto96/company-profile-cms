@extends('admin.layouts.main')

@section('content')
@php
    $datasourceLabels = collect(config('form_builder.datasources'))->map(fn ($d) => $d['label']);
    // Data ringkas untuk pratinjau mobile langsung (client-side).
    $fieldsJson = $form->fields->map(fn ($f) => [
        'id' => $f->id,
        'key' => $f->key,
        'label' => $f->label,
        'type' => $f->type,
        'placeholder' => $f->placeholder,
        'help_text' => $f->help_text,
        'is_required' => (bool) $f->is_required,
        'options' => $f->options_source === 'datasource'
            ? []
            : collect($f->options ?? [])->map(fn ($o) => ['label' => $o['label'] ?? '', 'value' => $o['value'] ?? ''])->values(),
        'options_source' => $f->options_source,
    ])->values();
@endphp

<style>
    .fb-item { border:1px solid #e8ecf1; border-radius:12px; background:#fff; }
    .fb-item + .fb-item { margin-top:10px; }
    .fb-handle { cursor:grab; color:#b6c0cc; }
    .fb-handle:active { cursor:grabbing; }
    .fb-placeholder { border:2px dashed #275a56; border-radius:12px; background:#eef5f4; height:64px; margin-top:10px; }
    .fb-key { font-family:monospace; font-size:.78em; color:#8a94a6; }
    /* Phone preview */
    .fb-phone { width:300px; height:600px; border:8px solid #0f172a; border-radius:34px; overflow:hidden; background:#f8fafc; display:flex; flex-direction:column; }
    .fb-phone-head { background:#fff; border-bottom:1px solid #eef1f5; padding:12px 16px; }
    .fb-phone-body { flex:1; overflow-y:auto; padding:14px; }
    .fb-phone-body::-webkit-scrollbar { width:0; }
    .fb-lbl { font-size:12.5px; font-weight:700; color:#0f172a; margin-bottom:5px; }
    .fb-req { color:#b3261e; }
    .fb-ctrl { border:1px solid #e2e8f0; background:#fff; border-radius:12px; padding:10px 12px; font-size:12.5px; color:#94a3b8; display:flex; align-items:center; justify-content:space-between; overflow:hidden; }
    .fb-hint { font-size:10px; color:#94a3b8; margin-top:4px; }
    .fb-sec { font-size:10.5px; font-weight:800; letter-spacing:.8px; text-transform:uppercase; color:#275a56; margin:18px 0 8px; }
    .fb-note { background:#eef5f4; color:#214f4b; border-radius:12px; padding:10px 12px; font-size:11.5px; }
    .fb-opt { border:1px solid #e2e8f0; border-radius:10px; padding:8px 11px; font-size:12px; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:8px; }
    .fb-dot { width:16px;height:16px;border:2px solid #cbd5e1;flex:none; } .fb-dot.rd{border-radius:50%} .fb-dot.sq{border-radius:5px}
    .fb-sticky { position:sticky; top:16px; }
</style>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">{{ $form->name }}</h4>
                    <p class="text-muted mb-0">
                        Seret kartu field untuk mengubah urutan. Urutan di sini = urutan tampil di aplikasi.
                        @if ($form->description) <br><small>{{ $form->description }}</small>@endif
                    </p>
                </div>
                <div class="d-flex align-items-center" style="gap:10px">
                    <a href="javascript:void(0)" id="tambahField" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Field</a>
                    <a href="{{ route('admin.mobile.forms') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar Form</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifikasi: editor khusus desktop --}}
    <div class="col-md-12 d-lg-none">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ri-computer-line" style="font-size:44px;color:#275a56"></i>
                <h5 class="mt-3 mb-1">Editor Form khusus Desktop</h5>
                <p class="text-muted mb-0">Penyusun form (drag &amp; drop) dan pratinjau mobile membutuhkan layar lebar.<br>Silakan buka halaman ini lewat komputer/laptop.</p>
            </div>
        </div>
    </div>

    {{-- Builder + preview: desktop only --}}
    <div class="col-md-12 d-none d-lg-block">
        <div class="row">
            <div class="col-lg-7 col-xl-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="fieldSortable">
                            @forelse ($form->fields as $field)
                                <div class="fb-item p-3 d-flex align-items-center" style="gap:12px" data-id="{{ $field->id }}">
                                    <i class="ri-draggable fb-handle" style="font-size:20px"></i>
                                    <div class="flex-fill">
                                        <div class="d-flex align-items-center flex-wrap" style="gap:8px">
                                            <span class="fw-semibold">{{ $field->label }}</span>
                                            <span class="badge badge-sm badge-info">{{ $fieldTypes[$field->type] ?? $field->type }}</span>
                                            @if ($field->is_required)<span class="badge badge-danger badge-sm">Wajib</span>@endif
                                            @if (in_array($field->type, $optionTypes, true))
                                                @if ($field->options_source === 'datasource')
                                                    <span class="badge badge-sm badge-primary"><i class="ri-database-2-line"></i> {{ $datasourceLabels[$field->options_source_key] ?? $field->options_source_key }}</span>
                                                @else
                                                    <span class="badge badge-light badge-sm">{{ count($field->options ?? []) }} opsi</span>
                                                @endif
                                            @endif
                                            @if ($field->conditional && !empty($field->conditional['field']))
                                                <span class="badge badge-sm" style="background:#fff4e5;color:#8a5a00;border:1px solid #ffd8a8;"><i class="ri-git-branch-line"></i> jika {{ $field->conditional['field'] }}</span>
                                            @endif
                                        </div>
                                        <span class="fb-key">{{ $field->key }}</span>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:6px">
                                        <a href="javascript:void(0)" data-bind-form-field="{{ $field->id }}" class="btn btn-success btn-xs editField" title="Edit"><i class="ri-pencil-line"></i></a>
                                        <a href="javascript:void(0)" onclick="deleteField({{ $field->id }})" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5" id="fbEmpty">Belum ada field. Klik <b>Tambah Field</b> untuk mulai menyusun form.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-xl-4 mb-4">
                <div class="fb-sticky">
                    <p class="text-muted mb-2" style="font-size:.8em"><i class="ri-smartphone-line me-1"></i> Pratinjau tampilan mobile</p>
                    <div class="fb-phone mx-auto">
                        <div class="fb-phone-head">
                            <div class="fw-bold" style="font-size:14px">{{ $form->name }}</div>
                            <div class="text-muted" style="font-size:10.5px">Pratinjau isian pengguna</div>
                        </div>
                        <div class="fb-phone-body" id="mobilePreview"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFieldEdit" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Edit Field</h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
    <div class="modal fade" id="modalFieldCreate" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Tambah Field</h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    let FORM_FIELDS = @json($fieldsJson);

    const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

    function previewControl(f) {
        // Placeholder bisa multi-baris (instruksi beranimasi di app). Di preview
        // statis ini cukup tampilkan BARIS PERTAMA agar tidak meluber keluar kotak.
        const ph = esc((f.placeholder || '').split('\n')[0].trim());
        const chev = '<i class="ri-arrow-down-s-line" style="color:#94a3b8"></i>';
        const opts = (f.options || []);
        const optRows = (shape) => f.options_source === 'datasource'
            ? `<div class="fb-opt"><span class="fb-dot ${shape}"></span> Opsi dari data</div>`
            : (opts.length ? opts.slice(0, 4).map((o) => `<div class="fb-opt"><span class="fb-dot ${shape}"></span> ${esc(o.label)}</div>`).join('') : `<div class="fb-opt"><span class="fb-dot ${shape}"></span> (belum ada opsi)</div>`);

        switch (f.type) {
            case 'textarea': return `<div class="fb-ctrl" style="height:56px;align-items:flex-start">${ph || 'Jawaban panjang…'}</div>`;
            case 'select': return `<div class="fb-ctrl">${ph || 'Pilih salah satu'} ${chev}</div>`;
            case 'multiselect': return `<div class="fb-ctrl">${ph || 'Pilih beberapa'} ${chev}</div>`;
            case 'radio': return optRows('rd');
            case 'checkbox_group': return optRows('sq');
            case 'checkbox': return `<div class="fb-opt"><span class="fb-dot sq"></span> ${ph || 'Ya'}</div>`;
            case 'date': return `<div class="fb-ctrl">${ph || 'Pilih tanggal'} <i class="ri-calendar-line" style="color:#94a3b8"></i></div>`;
            case 'time': return `<div class="fb-ctrl">${ph || 'Pilih jam'} <i class="ri-time-line" style="color:#94a3b8"></i></div>`;
            case 'datetime': return `<div class="fb-ctrl">${ph || 'Pilih tanggal & jam'} <i class="ri-calendar-line" style="color:#94a3b8"></i></div>`;
            case 'image': return `<div class="fb-ctrl" style="border-style:dashed;justify-content:center"><i class="ri-image-add-line me-1"></i> Unggah gambar</div>`;
            case 'file': return `<div class="fb-ctrl" style="border-style:dashed;justify-content:center"><i class="ri-upload-2-line me-1"></i> Unggah dokumen</div>`;
            case 'location': return `<div class="fb-ctrl" style="height:70px;align-items:flex-start"><i class="ri-map-pin-line me-1"></i> Peta + alamat</div>`;
            case 'number': return `<div class="fb-ctrl">${ph || '0'} <i class="ri-hashtag" style="color:#cbd5e1"></i></div>`;
            case 'email': return `<div class="fb-ctrl">${ph || 'email@contoh.com'} <i class="ri-mail-line" style="color:#cbd5e1"></i></div>`;
            case 'phone': return `<div class="fb-ctrl">${ph || '08xxxx'} <i class="ri-phone-line" style="color:#cbd5e1"></i></div>`;
            default: return `<div class="fb-ctrl">${ph || 'Jawaban singkat'}</div>`;
        }
    }

    function renderPreview() {
        const el = document.getElementById('mobilePreview');
        if (!el) return;
        if (!FORM_FIELDS.length) { el.innerHTML = '<div class="text-center text-muted py-5" style="font-size:12px">Belum ada field.</div>'; return; }
        el.innerHTML = FORM_FIELDS.map((f) => {
            if (f.type === 'section') return `<div class="fb-sec">${esc(f.label)}</div>`;
            if (f.type === 'note') return `<div class="fb-note mb-3">${esc(f.label)}</div>`;
            const req = f.is_required ? ' <span class="fb-req">*</span>' : '';
            const hint = f.help_text ? `<div class="fb-hint">${esc(f.help_text)}</div>` : '';
            return `<div style="margin-bottom:14px"><div class="fb-lbl">${esc(f.label)}${req}</div>${previewControl(f)}${hint}</div>`;
        }).join('');
    }

    $(document).ready(function () {
        renderPreview();

        const modalCreate = $.modalCustom({ trigger: '#tambahField', modal: '#modalFieldCreate', options: { title: 'Tambah Field', backdrop: 'static', keyboard: false, focus: false, show: false } });
        const modalEdit = $.modalCustom({ trigger: '.editField', modal: '#modalFieldEdit', options: { title: 'Edit Field', bind: 'form-field', backdrop: 'static', keyboard: false, focus: false, show: false } });

        modalCreate.onShow(function () {
            $.get('{{ route("admin.mobile.forms.forms") }}', { view: 'form-field-create', form_id: {{ $form->id }} })
                .done((r) => modalCreate.render(r))
                .fail((e) => modalCreate.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`));
        });
        modalEdit.onShow(function (id) {
            $.get('{{ route("admin.mobile.forms.forms") }}', { view: 'form-field-edit', id_form_field: id })
                .done((r) => modalEdit.render(r))
                .fail((e) => modalEdit.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`));
        });

        // Drag & drop (jQuery UI sortable sudah dimuat di layout).
        if ($.fn.sortable) {
            $('#fieldSortable').sortable({
                handle: '.fb-handle',
                placeholder: 'fb-placeholder',
                forcePlaceholderSize: true,
                axis: 'y',
                tolerance: 'pointer',
                update: function () {
                    const order = $('#fieldSortable > [data-id]').map(function () { return parseInt($(this).data('id'), 10); }).get();
                    // Susun ulang FORM_FIELDS sesuai urutan DOM lalu refresh preview.
                    FORM_FIELDS = order.map((id) => FORM_FIELDS.find((f) => f.id === id)).filter(Boolean);
                    renderPreview();
                    $.post('{{ route("admin.mobile.forms.fields.reorder_bulk") }}', { form_id: {{ $form->id }}, order, _token: '{{ csrf_token() }}' })
                        .done(() => $.toast({ heading: 'Tersimpan', text: 'Urutan field diperbarui.', position: 'top-right', icon: 'success' }))
                        .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal menyimpan urutan.', position: 'top-right', icon: 'warning' }));
                },
            });
        }
    });

    function deleteField(id) {
        Swal.fire({ title: 'Hapus field?', text: 'Field ini akan dihapus dari form.', icon: 'warning', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', showConfirmButton: true, showCancelButton: true, customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' } }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.forms.fields.destroy") }}', { id_form_field: id, _token: '{{ csrf_token() }}' })
                .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 600); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
@endsection
