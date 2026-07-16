@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0"><div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
            <div><h4 class="card-title mb-1">Order Produk</h4><p class="text-muted mb-0">Pesanan produk dari aplikasi mobile — proses, kirim, dan konfirmasi pembayaran.</p></div>
            <a href="{{ route('admin.mobile.products') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Produk</a>
        </div></div>
    </div>
    <div class="col-md-12"><div class="card shadow-sm border-0"><div class="card-body"><div class="table-responsive">
        <table class="table w-100" id="tableProductOrders"><thead><tr><th>Order</th><th>Item</th><th>Total</th><th>Bayar</th><th>Status</th><th>Tanggal</th><th class="text-center">Action</th></tr></thead><tbody></tbody></table>
    </div></div></div></div>

    <div class="modal fade" id="modalProductOrderDetail" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-detail btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        window.$productOrdersTable = $('#tableProductOrders').DataTable({
            processing:true, serverSide:true, pageLength:25,
            ajax:{ method:'get', url:'{{ route("admin.mobile.product_orders.data") }}' },
            columns:[ {data:'order',name:'order'}, {data:'items',name:'items',orderable:false,searchable:false}, {data:'total',name:'total',orderable:false,searchable:false}, {data:'payment',name:'payment',orderable:false,searchable:false}, {data:'status',name:'status',orderable:false,searchable:false}, {data:'date',name:'created_at'}, {data:'action',name:'action',orderable:false,searchable:false} ]
        });
        window.$productOrdersTable.on('draw', function() {
            const md = $.modalCustom({ trigger:'.detailProductOrder', modal:'#modalProductOrderDetail', options:{ title:'Detail Pesanan', bind:'product-order', backdrop:'static', keyboard:false, focus:false, show:false } });
            md.onShow((id)=>{ $(`[data-bind-product-order=${id}]`).spinner(); $.get('{{ route("admin.mobile.product_orders.forms") }}',{view:'product-order-detail',id_product_order:id}).done((r)=>md.render(r)).fail((e)=>md.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat detail.'}</span>`)).always(()=>$(`[data-bind-product-order=${id}]`).spinner('hide')); });
        });
    });
</script>
@endsection
