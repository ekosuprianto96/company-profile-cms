@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Voucher</h4>
                    <p class="text-muted mb-0">Kelola voucher diskon untuk order <strong>jasa</strong> dan <strong>produk</strong> di aplikasi mobile.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="javascript:void(0)" id="tambahVoucher" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> Tambah Voucher
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
                    <table class="table w-100" id="tableVouchers">
                        <thead>
                            <tr>
                                <th>Voucher</th>
                                <th>Order</th>
                                <th>Diskon</th>
                                <th>Kuota</th>
                                <th>Kedaluwarsa</th>
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

    <div class="modal fade" id="modalVoucherEdit" tabindex="-1">
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

    <div class="modal fade" id="modalVoucherCreate" tabindex="-1">
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
        window.$vouchersTable = $('#tableVouchers').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.vouchers.data") }}'
            },
            columns: [
                {data: 'voucher', name: 'voucher'},
                {data: 'order_type', name: 'order_type', orderable: false, searchable: false},
                {data: 'discount', name: 'discount', orderable: false, searchable: false},
                {data: 'quota', name: 'quota', orderable: false, searchable: false},
                {data: 'expires', name: 'expires_at'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        window.$vouchersTable.on('draw', function() {
            const modalCreate = $.modalCustom({
                trigger: '#tambahVoucher',
                modal: '#modalVoucherCreate',
                options: { title: 'Tambah Voucher', backdrop: 'static', keyboard: false, focus: false, show: false }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editVoucher',
                modal: '#modalVoucherEdit',
                options: { title: 'Edit Voucher', bind: 'voucher', backdrop: 'static', keyboard: false, focus: false, show: false }
            });

            modalCreate.onShow(function() {
                $('#tambahVoucher').spinner('show');
                $.get('{{ route("admin.mobile.vouchers.forms") }}', { view: 'voucher-create' })
                    .done(function(response) { modalCreate.render(response); })
                    .fail(function(error) {
                        const response = error.responseJSON || {};
                        modalCreate.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                    })
                    .always(function() { $('#tambahVoucher').spinner('hide'); });
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-voucher=${id}]`).spinner();
                $.get('{{ route("admin.mobile.vouchers.forms") }}', { view: 'voucher-edit', id_voucher: id })
                    .done(function(response) { modalEdit.render(response); })
                    .fail(function(error) {
                        const response = error.responseJSON || {};
                        modalEdit.render(`<span class="alert my-3 d-block alert-danger">${response.message || 'Gagal memuat form.'}</span>`);
                    })
                    .always(function() { $(`[data-bind-voucher=${id}]`).spinner('hide'); });
            });
        });
    });

    function deleteVoucher(id_voucher) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: 'Voucher ini akan dihapus.',
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            showConfirmButton: true,
            showCancelButton: true,
            customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' }
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('{{ route("admin.mobile.vouchers.destroy") }}', {
                id_voucher,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                window.$vouchersTable.ajax.reload();
                $.toast({ heading: 'Sukses', text: response.message, showHideTransition: 'plain', position: 'top-right', icon: 'success' });
            })
            .fail(function(error) {
                const response = error.responseJSON || {};
                $.toast({ heading: 'Warning', text: response.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' });
            });
        });
    }
</script>

@push('ckeditor')
<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
<script>
    // Init CKEditor untuk Syarat & Ketentuan voucher di dalam modal.
    function initVoucherTermsEditor(selector) {
        const el = document.querySelector(selector);
        if (!el || typeof ClassicEditor === 'undefined') return;
        if (window.voucherTermsEditor) {
            try { window.voucherTermsEditor.destroy(); } catch (e) {}
            window.voucherTermsEditor = null;
        }
        ClassicEditor
            .create(el, {
                extraPlugins: [
                    function (editor) {
                        createCustomUploadAdapterPlugin({
                            url: '{{ route('admin.ckeditor.upload') }}',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        })(editor);
                        new ImageRemovePlugin(editor);
                    },
                ],
                removePlugins: ['Markdown'],
            })
            .then((editor) => { window.voucherTermsEditor = editor; })
            .catch(() => {});
    }
</script>
@endpush
@endsection
