@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Kontak Bantuan &amp; Dukungan</h4>
                    <p class="text-muted mb-0">Kontak yang tampil di halaman <strong>Bantuan &amp; Dukungan</strong> aplikasi mobile (WhatsApp, Email, Telepon, dll).</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="javascript:void(0)" id="tambahMobileSupportContact" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> Tambah Kontak
                    </a>
                    <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table w-100" id="tableMobileSupportContacts">
                        <thead>
                            <tr>
                                <th>Kontak</th>
                                <th>Tipe</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMobileSupportContactEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content></div>
          </div>
        </div>
    </div>

    <div class="modal fade" id="modalMobileSupportContactCreate" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content></div>
          </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        window.$mobileSupportContactsTable = $('#tableMobileSupportContacts').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.support_contacts.data") }}'
            },
            columns: [
                {data: 'contact', name: 'contact'},
                {data: 'type', name: 'type', orderable: false, searchable: false},
                {data: 'sort_order', name: 'sort_order'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        window.$mobileSupportContactsTable.on('draw', function() {
            const modalCreate = $.modalCustom({
                trigger: '#tambahMobileSupportContact',
                modal: '#modalMobileSupportContactCreate',
                options: { title: 'Tambah Kontak', backdrop: 'static', keyboard: false, focus: false, show: false }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editMobileSupportContact',
                modal: '#modalMobileSupportContactEdit',
                options: { title: 'Edit Kontak', bind: 'mobile-support-contact', backdrop: 'static', keyboard: false, focus: false, show: false }
            });

            modalCreate.onShow(function() {
                $('#tambahMobileSupportContact').spinner('show');
                $.get('{{ route("admin.mobile.support_contacts.forms") }}', { view: 'mobile-support-contact-create' })
                    .done(function(response) { modalCreate.render(response); })
                    .fail(function(error) {
                        const response = error.responseJSON || {};
                        modalCreate.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                    })
                    .always(function() { $('#tambahMobileSupportContact').spinner('hide'); });
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-mobile-support-contact=${id}]`).spinner();
                $.get('{{ route("admin.mobile.support_contacts.forms") }}', { view: 'mobile-support-contact-edit', id_mobile_support_contact: id })
                    .done(function(response) { modalEdit.render(response); })
                    .fail(function(error) {
                        const response = error.responseJSON || {};
                        modalEdit.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                    })
                    .always(function() { $(`[data-bind-mobile-support-contact=${id}]`).spinner('hide'); });
            });
        });
    });

    function deleteMobileSupportContact(id_mobile_support_contact) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: 'Kontak dukungan ini akan dihapus.',
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            showConfirmButton: true,
            showCancelButton: true,
            customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' }
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('{{ route("admin.mobile.support_contacts.destroy") }}', {
                id_mobile_support_contact,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                window.$mobileSupportContactsTable.ajax.reload();
                $.toast({ heading: 'Sukses', text: response.message, showHideTransition: 'plain', position: 'top-right', icon: 'success' });
            })
            .fail(function(error) {
                const response = error.responseJSON || {};
                $.toast({ heading: 'Warning', text: response.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' });
            });
        });
    }
</script>
@endsection
