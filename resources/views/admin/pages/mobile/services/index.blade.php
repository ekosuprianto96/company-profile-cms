@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Layanan Mobile App</h4>
                    <p class="text-muted mb-0">Kelola layanan khusus aplikasi mobile. Data ini otomatis dipakai di Home dan Form Pengajuan mobile app.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="javascript:void(0)" id="tambahMobileService" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> Tambah Layanan Mobile
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
                    <table class="table w-100" id="tableMobileServices">
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Visual</th>
                                <th>Flags</th>
                                <th>Jenis Kebutuhan</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th>Updated By</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMobileServiceEdit" tabindex="-1">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content></div>
          </div>
        </div>
    </div>

    <div class="modal fade" id="modalMobileServiceCreate" tabindex="-1">
        <div class="modal-dialog modal-xl">
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
        window.$mobileServicesTable = $('#tableMobileServices').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.services.data") }}'
            },
            columns: [
                {data: 'title', name: 'title'},
                {data: 'visual', name: 'visual', orderable: false, searchable: false},
                {data: 'flags', name: 'flags', orderable: false, searchable: false},
                {data: 'need_types', name: 'need_types', orderable: false, searchable: false},
                {data: 'sort_order', name: 'sort_order'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'updated_by', name: 'updated_by', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        window.$mobileServicesTable.on('draw', function() {
            const modalCreate = $.modalCustom({
                trigger: '#tambahMobileService',
                modal: '#modalMobileServiceCreate',
                options: {
                    title: 'Tambah Layanan Mobile',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editMobileService',
                modal: '#modalMobileServiceEdit',
                options: {
                    title: 'Edit Layanan Mobile',
                    bind: 'mobile-service',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            modalCreate.onShow(function() {
                $('#tambahMobileService').spinner('show');
                $.get('{{ route("admin.mobile.services.forms") }}', {
                    view: 'mobile-service-create'
                })
                .done(function(response) {
                    modalCreate.render(response);
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};
                    modalCreate.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                })
                .always(function() {
                    $('#tambahMobileService').spinner('hide');
                });
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-mobile-service=${id}]`).spinner();
                $.get('{{ route("admin.mobile.services.forms") }}', {
                    view: 'mobile-service-edit',
                    id_mobile_service: id
                })
                .done(function(response) {
                    modalEdit.render(response);
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};
                    modalEdit.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                })
                .always(function() {
                    $(`[data-bind-mobile-service=${id}]`).spinner('hide');
                });
            });
        });
    });

    function deleteMobileService(id_mobile_service) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: 'Data layanan mobile ini akan dihapus permanen.',
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            showConfirmButton: true,
            showCancelButton: true,
            customClass: {
                cancelButton: 'bg-danger',
                confirmButton: 'bg-primary'
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('{{ route("admin.mobile.services.destroy") }}', {
                id_mobile_service,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                window.$mobileServicesTable.ajax.reload();

                $.toast({
                    heading: 'Sukses',
                    text: response.message,
                    showHideTransition: 'plain',
                    position: 'top-right',
                    icon: 'success'
                });
            })
            .fail(function(error) {
                const response = error.responseJSON || {};
                $.toast({
                    heading: 'Warning',
                    text: response.message || 'Terjadi kesalahan.',
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'warning'
                });
            });
        });
    }
</script>
@endsection
