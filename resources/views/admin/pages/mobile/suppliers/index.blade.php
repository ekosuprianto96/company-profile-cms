@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Suplier</h4>
                    <p class="text-muted mb-0">Master data suplier (internal). Setiap produk dapat dikaitkan ke suplier untuk pelacakan sumber barang. <strong>Tidak ditampilkan ke pengguna aplikasi.</strong></p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="javascript:void(0)" id="tambahSupplier" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Suplier</a>
                    <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Overview</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table w-100" id="tableSuppliers">
                        <thead>
                            <tr>
                                <th>Suplier</th>
                                <th>Kontak</th>
                                <th>Produk</th>
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

    <div class="modal fade" id="modalSupplierEdit" tabindex="-1">
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

    <div class="modal fade" id="modalSupplierCreate" tabindex="-1">
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
        window.$suppliersTable = $('#tableSuppliers').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: { method: 'get', url: '{{ route("admin.mobile.suppliers.data") }}' },
            columns: [
                {data: 'supplier', name: 'name'},
                {data: 'contact', name: 'contact', orderable: false, searchable: false},
                {data: 'products_count', name: 'products_count', orderable: false, searchable: false},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        window.$suppliersTable.on('draw', function() {
            const modalCreate = $.modalCustom({
                trigger: '#tambahSupplier',
                modal: '#modalSupplierCreate',
                options: { title: 'Tambah Suplier', backdrop: 'static', keyboard: false, focus: false, show: false }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editSupplier',
                modal: '#modalSupplierEdit',
                options: { title: 'Edit Suplier', bind: 'supplier', backdrop: 'static', keyboard: false, focus: false, show: false }
            });

            modalCreate.onShow(function() {
                $('#tambahSupplier').spinner('show');
                $.get('{{ route("admin.mobile.suppliers.forms") }}', { view: 'supplier-create' })
                    .done(function(response) { modalCreate.render(response); })
                    .fail(function(error) {
                        const response = error.responseJSON || {};
                        modalCreate.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                    })
                    .always(function() { $('#tambahSupplier').spinner('hide'); });
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-supplier=${id}]`).spinner();
                $.get('{{ route("admin.mobile.suppliers.forms") }}', { view: 'supplier-edit', id_supplier: id })
                    .done(function(response) { modalEdit.render(response); })
                    .fail(function(error) {
                        const response = error.responseJSON || {};
                        modalEdit.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                    })
                    .always(function() { $(`[data-bind-supplier=${id}]`).spinner('hide'); });
            });
        });
    });

    function deleteSupplier(id_supplier) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: 'Suplier ini akan dihapus. Produk terkait tetap ada, hanya kehilangan info suplier.',
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            showConfirmButton: true,
            showCancelButton: true,
            customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' }
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('{{ route("admin.mobile.suppliers.destroy") }}', { id_supplier, _token: '{{ csrf_token() }}' })
            .done(function(response) {
                window.$suppliersTable.ajax.reload();
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
