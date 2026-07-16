@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0"><div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
            <div><h4 class="card-title mb-1">Kurir Pengiriman</h4><p class="text-muted mb-0">Kurir internal (aktif) &amp; jasa kurir pihak ke-3 (menunggu integrasi API).</p></div>
            <div class="d-flex align-items-center" style="gap:10px;">
                <a href="javascript:void(0)" id="tambahShippingCourier" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Kurir</a>
                <a href="{{ route('admin.mobile.products') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Produk</a>
            </div>
        </div></div>
    </div>
    <div class="col-md-12"><div class="card shadow-sm border-0"><div class="card-body"><div class="table-responsive">
        <table class="table w-100" id="tableShippingCouriers"><thead><tr><th>Kurir</th><th>Tipe</th><th>Estimasi</th><th>Ongkir Dasar</th><th>Status</th><th class="text-center">Action</th></tr></thead><tbody></tbody></table>
    </div></div></div></div>

    <div class="modal fade" id="modalShippingCourierEdit" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" data-bind-content></div></div></div></div>
    <div class="modal fade" id="modalShippingCourierCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" data-bind-content></div></div></div></div>
</div>

<script>
    $(document).ready(function() {
        window.$shippingCouriersTable = $('#tableShippingCouriers').DataTable({
            processing:true, serverSide:true, pageLength:25,
            ajax:{ method:'get', url:'{{ route("admin.mobile.shipping_couriers.data") }}' },
            columns:[ {data:'courier',name:'courier'}, {data:'type',name:'type',orderable:false,searchable:false}, {data:'etd',name:'etd',orderable:false,searchable:false}, {data:'base_cost',name:'base_cost',orderable:false,searchable:false}, {data:'status',name:'status',orderable:false,searchable:false}, {data:'action',name:'action',orderable:false,searchable:false} ]
        });
        window.$shippingCouriersTable.on('draw', function() {
            const mc = $.modalCustom({ trigger:'#tambahShippingCourier', modal:'#modalShippingCourierCreate', options:{ title:'Tambah Kurir', backdrop:'static', keyboard:false, focus:false, show:false } });
            const me = $.modalCustom({ trigger:'.editShippingCourier', modal:'#modalShippingCourierEdit', options:{ title:'Edit Kurir', bind:'shipping-courier', backdrop:'static', keyboard:false, focus:false, show:false } });
            mc.onShow(()=>{ $('#tambahShippingCourier').spinner('show'); $.get('{{ route("admin.mobile.shipping_couriers.forms") }}',{view:'shipping-courier-create'}).done((r)=>mc.render(r)).fail((e)=>mc.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`)).always(()=>$('#tambahShippingCourier').spinner('hide')); });
            me.onShow((id)=>{ $(`[data-bind-shipping-courier=${id}]`).spinner(); $.get('{{ route("admin.mobile.shipping_couriers.forms") }}',{view:'shipping-courier-edit',id_shipping_courier:id}).done((r)=>me.render(r)).fail((e)=>me.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`)).always(()=>$(`[data-bind-shipping-courier=${id}]`).spinner('hide')); });
        });
    });
    function deleteShippingCourier(id_shipping_courier) {
        Swal.fire({ title:'Kamu yakin?', text:'Kurir ini akan dihapus.', icon:'warning', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', showConfirmButton:true, showCancelButton:true, customClass:{cancelButton:'bg-danger',confirmButton:'bg-primary'} }).then((res)=>{
            if(!res.isConfirmed) return;
            $.post('{{ route("admin.mobile.shipping_couriers.destroy") }}',{ id_shipping_courier, _token:'{{ csrf_token() }}' }).done((r)=>{ window.$shippingCouriersTable.ajax.reload(); $.toast({heading:'Sukses',text:r.message,showHideTransition:'plain',position:'top-right',icon:'success'}); }).fail((e)=>$.toast({heading:'Warning',text:(e.responseJSON||{}).message||'Terjadi kesalahan.',showHideTransition:'slide',position:'top-right',icon:'warning'}));
        });
    }
</script>
@endsection
