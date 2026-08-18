@extends('admin.layouts.main')

@section('content')
@php
    $optionalJson = collect($availableOptional)->values();
    $eventJson = collect($eventCatalog);
@endphp

<style>
    .st-item { border:1px solid #e8ecf1; border-radius:12px; background:#fff; }
    .st-item + .st-item { margin-top:10px; }
    .st-handle { cursor:grab; color:#b6c0cc; }
    .st-handle:active { cursor:grabbing; }
    .st-placeholder { border:2px dashed #275a56; border-radius:12px; background:#eef5f4; height:64px; margin-top:10px; }
    .st-core { border-left:4px solid #275a56; }
    .st-desc { font-size:.8em; color:#8a94a6; }
    /* Preview timeline ala mobile */
    .st-phone { width:300px; border:8px solid #0f172a; border-radius:34px; overflow:hidden; background:#f8fafc; }
    .st-phone-head { background:#fff; border-bottom:1px solid #eef1f5; padding:12px 16px; font-weight:700; font-size:13px; color:#0f172a; }
    .st-phone-body { padding:16px 14px; max-height:560px; overflow-y:auto; }
    .st-phone-body::-webkit-scrollbar { width:0; }
    .tl-row { display:flex; gap:10px; }
    .tl-rail { display:flex; flex-direction:column; align-items:center; }
    .tl-dot { width:18px; height:18px; border-radius:50%; border:2px solid #cbd5e1; background:#fff; flex:none; display:flex; align-items:center; justify-content:center; }
    .tl-dot.done { border-color:#275a56; background:#275a56; color:#fff; font-size:11px; }
    .tl-line { width:2px; flex:1; background:#e2e8f0; min-height:26px; }
    .tl-title { font-size:12.5px; font-weight:700; color:#0f172a; }
    .tl-desc { font-size:10.5px; color:#94a3b8; line-height:1.45; padding-bottom:14px; }
    .st-sticky { position:sticky; top:16px; }
</style>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">{{ $template->name }} @if ($template->is_default)<span class="badge badge-primary badge-sm align-middle">Default</span>@endif</h4>
                    <p class="text-muted mb-0">
                        Seret kartu step untuk mengubah urutan tampil di aplikasi. Step <b>wajib</b> (garis kiri hijau) tidak bisa dihapus — nama &amp; keterangannya tetap bisa diedit.
                        @if ($template->description) <br><small>{{ $template->description }}</small>@endif
                    </p>
                </div>
                <div class="d-flex align-items-center" style="gap:10px">
                    <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalStepAdd"><i class="ri-add-line me-1"></i> Tambah Step</a>
                    <a href="{{ route('admin.mobile.step_templates') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar Template</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="row">
            <div class="col-lg-7 col-xl-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @if (count($missingCore) > 0)
                            <div class="alert alert-warning py-2 px-3 mb-3">
                                <small><i class="ri-error-warning-line me-1"></i>Step wajib berikut belum ada di template ini:
                                    @foreach ($missingCore as $core)
                                        <a href="javascript:void(0)" onclick="restoreCore('{{ $core['key'] }}', '{{ $core['name'] }}')" class="badge badge-warning badge-sm text-decoration-none">{{ $core['name'] }} <i class="ri-add-line"></i></a>
                                    @endforeach
                                </small>
                            </div>
                        @endif

                        <div id="stepSortable">
                            @forelse ($template->steps as $step)
                                <div class="st-item p-3 d-flex align-items-center {{ $step->kind === 'core' ? 'st-core' : '' }}" style="gap:12px" data-id="{{ $step->id }}">
                                    <i class="ri-draggable st-handle" style="font-size:20px"></i>
                                    <div class="flex-fill">
                                        <div class="d-flex align-items-center flex-wrap" style="gap:8px">
                                            <span class="fw-semibold">{{ $step->name }}</span>
                                            @if ($step->kind === 'core')
                                                <span class="badge badge-sm" style="background:#eef5f4;color:#275a56;border:1px solid #cfe3e0;"><i class="ri-lock-line"></i> Wajib</span>
                                            @elseif ($step->kind === 'optional')
                                                <span class="badge badge-sm badge-info">Optional</span>
                                            @else
                                                <span class="badge badge-sm badge-primary">Custom</span>
                                            @endif
                                            @if ($step->trigger_status)
                                                <span class="badge badge-light badge-sm"><i class="ri-flashlight-line"></i> {{ $eventCatalog[$step->trigger_status] ?? $step->trigger_status }}</span>
                                            @else
                                                <span class="badge badge-sm" style="background:#fff4e5;color:#8a5a00;border:1px solid #ffd8a8;"><i class="ri-hand"></i> Dicentang manual admin</span>
                                            @endif
                                            @foreach ($step->actions ?? [] as $action)
                                                <span class="badge badge-success badge-sm"><i class="ri-notification-3-line"></i> {{ $actionCatalog[$action] ?? $action }}</span>
                                            @endforeach
                                        </div>
                                        @if ($step->description)<div class="st-desc mt-1">{{ $step->description }}</div>@endif
                                        @if ($step->kind === 'core' && isset($builtinLabels[$step->key]))
                                            <div class="st-desc mt-1"><i class="ri-settings-3-line"></i> Action bawaan: {{ $builtinLabels[$step->key] }}</div>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:6px">
                                        <a href="javascript:void(0)" class="btn btn-success btn-xs js-edit-step" title="Edit"
                                            data-id="{{ $step->id }}"
                                            data-kind="{{ $step->kind }}"
                                            data-name="{{ $step->name }}"
                                            data-description="{{ $step->description }}"
                                            data-trigger="{{ $step->trigger_status }}"
                                            data-actions='@json($step->actions ?? [])'><i class="ri-pencil-line"></i></a>
                                        @if ($step->kind !== 'core')
                                            <a href="javascript:void(0)" onclick="deleteStep({{ $step->id }})" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">Belum ada step.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-xl-4 mb-4">
                <div class="st-sticky">
                    <p class="text-muted mb-2" style="font-size:.8em"><i class="ri-smartphone-line me-1"></i> Pratinjau "Status Pengajuan" di aplikasi</p>
                    <div class="st-phone mx-auto">
                        <div class="st-phone-head">Status Pengajuan</div>
                        <div class="st-phone-body">
                            @foreach ($template->steps as $index => $step)
                                <div class="tl-row">
                                    <div class="tl-rail">
                                        <div class="tl-dot {{ $index === 0 ? 'done' : '' }}">@if ($index === 0)<i class="ri-check-line"></i>@endif</div>
                                        @if (! $loop->last)<div class="tl-line"></div>@endif
                                    </div>
                                    <div class="flex-fill">
                                        <div class="tl-title">{{ $step->name }}</div>
                                        <div class="tl-desc">{{ $step->description }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal tambah step --}}
    <div class="modal fade" id="modalStepAdd" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Tambah Step</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="form-group mb-3">
                <label class="form-label">Jenis Step</label>
                <select class="form-control" id="addSource">
                    <option value="custom">Step custom (buat sendiri)</option>
                    @if ($optionalJson->count())<option value="optional">Step siap pakai dari sistem</option>@endif
                </select>
            </div>

            <div class="form-group mb-3 d-none" id="addOptionalWrap">
                <label class="form-label">Pilih Step Siap Pakai</label>
                <select class="form-control" id="addOptionalKey">
                    @foreach ($optionalJson as $opt)
                        <option value="{{ $opt['key'] }}" data-name="{{ $opt['name'] }}" data-description="{{ $opt['description'] }}" data-trigger="{{ $opt['trigger_status'] }}">{{ $opt['name'] }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Trigger step siap pakai sudah ditentukan sistem; nama &amp; keterangan tetap bisa kamu ubah.</small>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Nama Step</label>
                <input type="text" class="form-control" id="addName" placeholder="mis. Menunggu Survey">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Keterangan</label>
                <textarea class="form-control" id="addDescription" rows="2" placeholder="Keterangan yang tampil di bawah nama step pada aplikasi user"></textarea>
            </div>
            <div class="form-group mb-3" id="addTriggerWrap">
                <label class="form-label">Kapan step ini tercentang?</label>
                <select class="form-control" id="addTrigger">
                    <option value="">— Dicentang manual oleh admin —</option>
                    @foreach ($eventCatalog as $key => $label)
                        <option value="{{ $key }}">Otomatis: {{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Action saat step tercentang</label>
                @foreach ($actionCatalog as $key => $label)
                    <label class="d-flex align-items-center mb-2" style="gap:10px; cursor:pointer;">
                        <input class="js-add-action" type="checkbox" value="{{ $key }}" style="width:16px; height:16px; flex:0 0 auto; margin:0; accent-color:#275a56;">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
                <small class="text-muted">Teks notifikasi diambil dari template "Step pengajuan tercapai" (menu Template Notifikasi) memakai nama &amp; keterangan step ini.</small>
            </div>
            <button type="button" class="btn btn-primary w-100" onclick="storeStep()">Tambah Step</button>
        </div>
    </div></div></div>

    {{-- Modal edit step --}}
    <div class="modal fade" id="modalStepEdit" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Step</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="editStepId">
            <div class="alert alert-info py-2 px-3 mb-3 d-none" id="editCoreNote"><small><i class="ri-lock-line me-1"></i>Step wajib bawaan sistem: trigger terkunci, tidak bisa dihapus. Nama, keterangan, dan action tambahan tetap bisa diubah.</small></div>
            <div class="form-group mb-3">
                <label class="form-label">Nama Step</label>
                <input type="text" class="form-control" id="editStepName">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Keterangan</label>
                <textarea class="form-control" id="editStepDescription" rows="2"></textarea>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Kapan step ini tercentang?</label>
                <select class="form-control" id="editStepTrigger">
                    <option value="">— Dicentang manual oleh admin —</option>
                    @foreach ($eventCatalog as $key => $label)
                        <option value="{{ $key }}">Otomatis: {{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Action saat step tercentang</label>
                @foreach ($actionCatalog as $key => $label)
                    <label class="d-flex align-items-center mb-2" style="gap:10px; cursor:pointer;">
                        <input class="js-edit-action" type="checkbox" value="{{ $key }}" style="width:16px; height:16px; flex:0 0 auto; margin:0; accent-color:#275a56;">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <button type="button" class="btn btn-primary w-100" onclick="updateStep()">Simpan Perubahan</button>
        </div>
    </div></div></div>
</div>

<script>
    $(document).ready(function () {
        // Drag & drop urutan (jQuery UI sortable sudah dimuat di layout).
        if ($.fn.sortable) {
            $('#stepSortable').sortable({
                handle: '.st-handle',
                placeholder: 'st-placeholder',
                update: function () {
                    const order = $('#stepSortable .st-item').map(function () { return $(this).data('id'); }).get();
                    $.post('{{ route("admin.mobile.step_templates.steps.reorder", $template->id) }}', { order, _token: '{{ csrf_token() }}' })
                        .done(() => $.toast({ heading: 'Sukses', text: 'Urutan step disimpan.', position: 'top-right', icon: 'success' }))
                        .fail(() => $.toast({ heading: 'Warning', text: 'Gagal menyimpan urutan.', position: 'top-right', icon: 'warning' }));
                },
            });
        }

        // Toggle jenis step di modal tambah.
        $('#addSource').on('change', function () {
            const optional = $(this).val() === 'optional';
            $('#addOptionalWrap').toggleClass('d-none', !optional);
            $('#addTriggerWrap').toggleClass('d-none', optional);
            if (optional) $('#addOptionalKey').trigger('change');
        });
        $('#addOptionalKey').on('change', function () {
            const $opt = $(this).find(':selected');
            $('#addName').val($opt.data('name'));
            $('#addDescription').val($opt.data('description'));
        });
    });

    function storeStep(extra) {
        const source = (extra && extra.source) || $('#addSource').val();
        const payload = {
            source,
            key: source === 'optional' ? $('#addOptionalKey').val() : (extra && extra.key) || null,
            name: (extra && extra.name) || $('#addName').val(),
            description: $('#addDescription').val(),
            trigger_status: source === 'custom' ? ($('#addTrigger').val() || null) : null,
            actions: $('.js-add-action:checked').map(function () { return this.value; }).get(),
            _token: '{{ csrf_token() }}'
        };
        $.post('{{ route("admin.mobile.step_templates.steps.store", $template->id) }}', payload)
            .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 600); })
            .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal menambah step.', position: 'top-right', icon: 'warning' }));
    }

    function restoreCore(key, name) {
        storeStep({ source: 'core', key, name });
    }

    $(document).on('click', '.js-edit-step', function () {
        const kind = $(this).data('kind');
        $('#editStepId').val($(this).data('id'));
        $('#editStepName').val($(this).data('name'));
        $('#editStepDescription').val($(this).data('description'));
        $('#editStepTrigger').val($(this).data('trigger') || '').prop('disabled', kind !== 'custom');
        $('#editCoreNote').toggleClass('d-none', kind !== 'core');
        const actions = $(this).data('actions') || [];
        $('.js-edit-action').each(function () { this.checked = actions.includes(this.value); });
        new bootstrap.Modal('#modalStepEdit').show();
    });

    function updateStep() {
        $.post('{{ url("admin/mobile/step-templates/steps/update") }}/' + $('#editStepId').val(), {
            name: $('#editStepName').val(),
            description: $('#editStepDescription').val(),
            trigger_status: $('#editStepTrigger').prop('disabled') ? null : ($('#editStepTrigger').val() || null),
            actions: $('.js-edit-action:checked').map(function () { return this.value; }).get(),
            _token: '{{ csrf_token() }}'
        })
        .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 600); })
        .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal menyimpan.', position: 'top-right', icon: 'warning' }));
    }

    function deleteStep(id) {
        Swal.fire({ title: 'Hapus step ini?', text: 'Pengajuan lama tidak terpengaruh (snapshot menempel di pengajuan).', icon: 'warning', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', showConfirmButton: true, showCancelButton: true, customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' } }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.step_templates.steps.destroy") }}', { id, _token: '{{ csrf_token() }}' })
                .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 600); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
@endsection
