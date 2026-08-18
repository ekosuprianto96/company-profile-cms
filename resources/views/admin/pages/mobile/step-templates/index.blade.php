@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Template Rules Step</h4>
                    <p class="text-muted mb-0">Template langkah <b>Status Pengajuan</b>. Satu template bisa dipakai banyak layanan; step wajib bawaan sistem selalu ada, admin bisa menambah step optional/custom beserta action-nya.</p>
                </div>
                <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTemplateCreate"><i class="ri-add-line me-1"></i> Tambah Template</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle w-100">
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th class="text-center">Step</th>
                                <th class="text-center">Dipakai Layanan</th>
                                <th>Status</th>
                                <th class="text-center" style="width:200px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($templates as $template)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $template->name }}</span>
                                        @if ($template->is_default)<span class="badge badge-primary badge-sm ms-1">Default</span>@endif
                                        @if ($template->description)<div><small class="text-muted">{{ $template->description }}</small></div>@endif
                                    </td>
                                    <td class="text-center"><span class="badge badge-sm badge-info">{{ $template->steps_count }}</span></td>
                                    <td class="text-center">
                                        @if ($template->services_count > 0)
                                            <span class="badge badge-sm badge-primary">{{ $template->services_count }}</span>
                                        @else
                                            <span class="badge badge-light badge-sm">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($template->is_active)
                                            <span class="badge badge-success badge-sm">Aktif</span>
                                        @else
                                            <span class="badge badge-danger badge-sm">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center" style="gap:8px">
                                            <a href="{{ route('admin.mobile.step_templates.builder', $template->id) }}" class="btn btn-primary btn-xs" title="Atur Step"><i class="ri-list-check-2"></i></a>
                                            <a href="javascript:void(0)" class="btn btn-success btn-xs js-edit-template" title="Edit"
                                                data-id="{{ $template->id }}"
                                                data-name="{{ $template->name }}"
                                                data-description="{{ $template->description }}"
                                                data-active="{{ $template->is_active ? 1 : 0 }}"
                                                data-default="{{ $template->is_default ? 1 : 0 }}"><i class="ri-pencil-line"></i></a>
                                            <a href="javascript:void(0)" onclick="duplicateTemplate({{ $template->id }})" class="btn btn-info btn-xs" title="Duplikat"><i class="ri-file-copy-line"></i></a>
                                            <a href="javascript:void(0)" onclick="deleteTemplate({{ $template->id }}, {{ $template->services_count }}, {{ $template->is_default ? 1 : 0 }})" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada template. Klik <b>Tambah Template</b> untuk membuat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal tambah --}}
    <div class="modal fade" id="modalTemplateCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Tambah Template</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="form-group mb-3">
                <label class="form-label">Nama Template</label>
                <input type="text" class="form-control" id="createName" placeholder="mis. Alur Survey">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" id="createDescription" rows="2" placeholder="Keterangan singkat template ini"></textarea>
            </div>
            <div class="alert alert-info py-2 px-3 mb-3"><small><i class="ri-information-line me-1"></i>Step wajib bawaan sistem (Draft, Waiting Payment, Diproses Admin, Disetujui, Completed) otomatis terpasang dan tidak bisa dihapus — nama & keterangannya bisa kamu ubah di halaman Atur Step.</small></div>
            <button type="button" class="btn btn-primary w-100" onclick="storeTemplate()">Simpan & Atur Step</button>
        </div>
    </div></div></div>

    {{-- Modal edit --}}
    <div class="modal fade" id="modalTemplateEdit" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Template</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="form-group mb-3">
                <label class="form-label">Nama Template</label>
                <input type="text" class="form-control" id="editName">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" id="editDescription" rows="2"></textarea>
            </div>
            <div class="row">
                <div class="col-6 form-group mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" id="editActive">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="col-6 form-group mb-3">
                    <label class="form-label">Jadikan Default</label>
                    <select class="form-control" id="editDefault">
                        <option value="0">Tidak</option>
                        <option value="1">Ya (untuk layanan tanpa template)</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-primary w-100" onclick="updateTemplate()">Simpan Perubahan</button>
        </div>
    </div></div></div>
</div>

<script>
    $(document).on('click', '.js-edit-template', function () {
        $('#editId').val($(this).data('id'));
        $('#editName').val($(this).data('name'));
        $('#editDescription').val($(this).data('description'));
        $('#editActive').val(String($(this).data('active')));
        $('#editDefault').val(String($(this).data('default')));
        new bootstrap.Modal('#modalTemplateEdit').show();
    });

    function storeTemplate() {
        $.post('{{ route("admin.mobile.step_templates.store") }}', {
            name: $('#createName').val(),
            description: $('#createDescription').val(),
            _token: '{{ csrf_token() }}'
        })
        .done((r) => { window.location = '{{ url("admin/mobile/step-templates") }}/' + r.id + '/builder'; })
        .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal menyimpan.', position: 'top-right', icon: 'warning' }));
    }

    function updateTemplate() {
        $.post('{{ url("admin/mobile/step-templates/update") }}/' + $('#editId').val(), {
            name: $('#editName').val(),
            description: $('#editDescription').val(),
            is_active: $('#editActive').val(),
            is_default: $('#editDefault').val(),
            _token: '{{ csrf_token() }}'
        })
        .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 600); })
        .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal menyimpan.', position: 'top-right', icon: 'warning' }));
    }

    function duplicateTemplate(id) {
        $.post('{{ route("admin.mobile.step_templates.duplicate") }}', { id, _token: '{{ csrf_token() }}' })
            .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 700); })
            .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal menduplikasi.', position: 'top-right', icon: 'warning' }));
    }

    function deleteTemplate(id, usedCount, isDefault) {
        if (isDefault) {
            Swal.fire({ title: 'Tidak bisa dihapus', text: 'Ini template default. Jadikan template lain sebagai default terlebih dulu.', icon: 'warning', confirmButtonText: 'Mengerti' });
            return;
        }
        if (usedCount > 0) {
            Swal.fire({ title: 'Tidak bisa dihapus', text: `Template ini masih dipakai ${usedCount} layanan. Lepas dulu dari layanannya.`, icon: 'warning', confirmButtonText: 'Mengerti' });
            return;
        }
        Swal.fire({ title: 'Kamu yakin?', text: 'Template beserta seluruh step-nya akan dihapus. Pengajuan lama tidak terpengaruh (snapshot menempel di pengajuan).', icon: 'warning', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', showConfirmButton: true, showCancelButton: true, customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' } }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.step_templates.destroy") }}', { id, _token: '{{ csrf_token() }}' })
                .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 700); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
@endsection
