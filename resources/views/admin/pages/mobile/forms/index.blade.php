@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Form Builder</h4>
                    <p class="text-muted mb-0">Form pengajuan <b>khusus layanan</b>. Satu form bisa dipakai banyak layanan; aplikasi mobile merender input dari schema ini.</p>
                </div>
                <a href="javascript:void(0)" id="tambahForm" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Form</a>
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
                                <th>Form</th>
                                <th class="text-center">Field</th>
                                <th class="text-center">Dipakai Layanan</th>
                                <th>Status</th>
                                <th class="text-center" style="width:170px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($forms as $form)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $form->name }}</span>
                                        <div><small class="text-muted">{{ $form->slug }}</small></div>
                                        @if ($form->description)<div><small class="text-muted">{{ $form->description }}</small></div>@endif
                                    </td>
                                    <td class="text-center"><span class="badge badge-sm badge-info">{{ $form->fields_count }}</span></td>
                                    <td class="text-center">
                                        @if ($form->services_count > 0)
                                            <span class="badge badge-sm badge-primary">{{ $form->services_count }}</span>
                                        @else
                                            <span class="badge badge-light badge-sm">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($form->is_active)
                                            <span class="badge badge-success badge-sm">Aktif</span>
                                        @else
                                            <span class="badge badge-danger badge-sm">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center" style="gap:8px">
                                            <a href="{{ route('admin.mobile.forms.builder', $form->id) }}" class="btn btn-primary btn-xs" title="Builder Field"><i class="ri-layout-grid-line"></i></a>
                                            <a href="javascript:void(0)" data-bind-form="{{ $form->id }}" class="btn btn-success btn-xs editForm" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <a href="javascript:void(0)" onclick="duplicateForm({{ $form->id }})" class="btn btn-info btn-xs" title="Duplikat"><i class="ri-file-copy-line"></i></a>
                                            <a href="javascript:void(0)" onclick="deleteForm({{ $form->id }}, {{ $form->services_count }})" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada form. Klik <b>Tambah Form</b> untuk membuat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFormEdit" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Edit Form</h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
    <div class="modal fade" id="modalFormCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Tambah Form</h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        const modalCreate = $.modalCustom({ trigger: '#tambahForm', modal: '#modalFormCreate', options: { title: 'Tambah Form', backdrop: 'static', keyboard: false, focus: false, show: false } });
        const modalEdit = $.modalCustom({ trigger: '.editForm', modal: '#modalFormEdit', options: { title: 'Edit Form', bind: 'form', backdrop: 'static', keyboard: false, focus: false, show: false } });

        modalCreate.onShow(function() {
            $.get('{{ route("admin.mobile.forms.forms") }}', { view: 'form-create' })
                .done((r) => modalCreate.render(r))
                .fail((e) => modalCreate.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`));
        });
        modalEdit.onShow(function(id) {
            $.get('{{ route("admin.mobile.forms.forms") }}', { view: 'form-edit', id_form: id })
                .done((r) => modalEdit.render(r))
                .fail((e) => modalEdit.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`));
        });
    });

    function duplicateForm(id) {
        $.post('{{ route("admin.mobile.forms.duplicate") }}', { id_form: id, _token: '{{ csrf_token() }}' })
            .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 700); })
            .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal menduplikasi.', position: 'top-right', icon: 'warning' }));
    }

    function deleteForm(id, usedCount) {
        if (usedCount > 0) {
            Swal.fire({ title: 'Tidak bisa dihapus', text: `Form ini masih dipakai ${usedCount} layanan. Lepas dulu dari layanannya.`, icon: 'warning', confirmButtonText: 'Mengerti' });
            return;
        }
        Swal.fire({ title: 'Kamu yakin?', text: 'Form beserta seluruh field-nya akan dihapus.', icon: 'warning', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', showConfirmButton: true, showCancelButton: true, customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' } }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.forms.destroy") }}', { id_form: id, _token: '{{ csrf_token() }}' })
                .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 700); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
@endsection
