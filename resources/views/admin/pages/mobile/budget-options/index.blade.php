@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Master Perkiraan Anggaran</h4>
                    <p class="text-muted mb-0">Master ini dipakai di form pengajuan mobile sebagai pilihan anggaran yang disiapkan user.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="javascript:void(0)" id="tambahMobileBudgetOption" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> Tambah Pilihan Anggaran
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
                    <table class="table w-100" id="tableMobileBudgetOptions">
                        <thead>
                            <tr>
                                <th>Nama Pilihan</th>
                                <th>Range Nominal</th>
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

    <div class="modal fade" id="modalMobileBudgetOptionEdit" tabindex="-1">
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

    <div class="modal fade" id="modalMobileBudgetOptionCreate" tabindex="-1">
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
        window.$mobileBudgetOptionsTable = $('#tableMobileBudgetOptions').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.budget_options.data") }}'
            },
            columns: [
                {data: 'name', name: 'name'},
                {data: 'range', name: 'range', orderable: false, searchable: false},
                {data: 'sort_order', name: 'sort_order'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        window.$mobileBudgetOptionsTable.on('draw', function() {
            const modalCreate = $.modalCustom({
                trigger: '#tambahMobileBudgetOption',
                modal: '#modalMobileBudgetOptionCreate',
                options: {
                    title: 'Tambah Pilihan Anggaran',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editMobileBudgetOption',
                modal: '#modalMobileBudgetOptionEdit',
                options: {
                    title: 'Edit Pilihan Anggaran',
                    bind: 'mobile-budget-option',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            modalCreate.onShow(function() {
                $('#tambahMobileBudgetOption').spinner('show');
                $.get('{{ route("admin.mobile.budget_options.forms") }}', { view: 'mobile-budget-option-create' })
                    .done(function(response) {
                        modalCreate.render(response);
                    })
                    .fail(function(error) {
                        const response = error.responseJSON || {};
                        modalCreate.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                    })
                    .always(function() {
                        $('#tambahMobileBudgetOption').spinner('hide');
                    });
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-mobile-budget-option=${id}]`).spinner();
                $.get('{{ route("admin.mobile.budget_options.forms") }}', {
                    view: 'mobile-budget-option-edit',
                    id_mobile_budget_option: id
                })
                .done(function(response) {
                    modalEdit.render(response);
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};
                    modalEdit.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                })
                .always(function() {
                    $(`[data-bind-mobile-budget-option=${id}]`).spinner('hide');
                });
            });
        });
    });

    function deleteMobileBudgetOption(id_mobile_budget_option) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: 'Pilihan anggaran ini akan dihapus.',
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

            $.post('{{ route("admin.mobile.budget_options.destroy") }}', {
                id_mobile_budget_option,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                window.$mobileBudgetOptionsTable.ajax.reload();
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

