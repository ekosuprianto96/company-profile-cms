@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Produk</h4>
                    <p class="text-muted mb-0">Katalog produk untuk aplikasi mobile (dengan pengaturan bundle, cakupan layanan, &amp; metode pengiriman).</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.product_categories') }}" class="btn btn-light btn-sm"><i class="ri-price-tag-3-line me-1"></i> Kategori</a>
                    <a href="{{ route('admin.mobile.shipping_couriers') }}" class="btn btn-light btn-sm"><i class="ri-truck-line me-1"></i> Kurir</a>
                    <a href="javascript:void(0)" id="tambahProduct" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Produk</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table w-100" id="tableProducts">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Pengaturan</th>
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

    <div class="modal fade" id="modalProductEdit" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
    <div class="modal fade" id="modalProductCreate" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        window.$productsTable = $('#tableProducts').DataTable({
            processing: true, serverSide: true, pageLength: 25,
            ajax: { method: 'get', url: '{{ route("admin.mobile.products.data") }}' },
            columns: [
                {data:'product', name:'product'},
                {data:'category', name:'category', orderable:false, searchable:false},
                {data:'price', name:'price', orderable:false, searchable:false},
                {data:'stock', name:'stock'},
                {data:'settings', name:'settings', orderable:false, searchable:false},
                {data:'status', name:'status', orderable:false, searchable:false},
                {data:'action', name:'action', orderable:false, searchable:false}
            ]
        });

        window.$productsTable.on('draw', function() {
            const modalCreate = $.modalCustom({ trigger:'#tambahProduct', modal:'#modalProductCreate', options:{ title:'Tambah Produk', backdrop:'static', keyboard:false, focus:false, show:false } });
            const modalEdit = $.modalCustom({ trigger:'.editProduct', modal:'#modalProductEdit', options:{ title:'Edit Produk', bind:'product', backdrop:'static', keyboard:false, focus:false, show:false } });

            modalCreate.onShow(function() {
                $('#tambahProduct').spinner('show');
                $.get('{{ route("admin.mobile.products.forms") }}', { view:'product-create' })
                    .done((r)=>modalCreate.render(r))
                    .fail((e)=>modalCreate.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                    .always(()=>$('#tambahProduct').spinner('hide'));
            });
            modalEdit.onShow(function(id) {
                $(`[data-bind-product=${id}]`).spinner();
                $.get('{{ route("admin.mobile.products.forms") }}', { view:'product-edit', id_product:id })
                    .done((r)=>modalEdit.render(r))
                    .fail((e)=>modalEdit.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                    .always(()=>$(`[data-bind-product=${id}]`).spinner('hide'));
            });
        });
    });

    function deleteProduct(id_product) {
        Swal.fire({ title:'Kamu yakin?', text:'Produk ini akan dihapus.', icon:'warning', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', showConfirmButton:true, showCancelButton:true, customClass:{ cancelButton:'bg-danger', confirmButton:'bg-primary' } }).then((result)=>{
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.products.destroy") }}', { id_product, _token:'{{ csrf_token() }}' })
                .done((r)=>{ window.$productsTable.ajax.reload(); $.toast({ heading:'Sukses', text:r.message, showHideTransition:'plain', position:'top-right', icon:'success' }); })
                .fail((e)=>$.toast({ heading:'Warning', text:(e.responseJSON||{}).message||'Terjadi kesalahan.', showHideTransition:'slide', position:'top-right', icon:'warning' }));
        });
    }
</script>
@endsection
