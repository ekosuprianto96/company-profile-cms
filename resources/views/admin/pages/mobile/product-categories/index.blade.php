@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0"><div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
            <div><h4 class="card-title mb-1">Kategori Produk</h4><p class="text-muted mb-0">Master kategori untuk katalog produk mobile.</p></div>
            <div class="d-flex align-items-center" style="gap:10px;">
                <a href="javascript:void(0)" id="tambahProductCategory" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Kategori</a>
                <a href="{{ route('admin.mobile.products') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Produk</a>
            </div>
        </div></div>
    </div>
    <div class="col-md-12"><div class="card shadow-sm border-0"><div class="card-body"><div class="table-responsive">
        <table class="table w-100" id="tableProductCategories"><thead><tr><th>Kategori</th><th>Jumlah Produk</th><th>Urutan</th><th>Status</th><th class="text-center">Action</th></tr></thead><tbody></tbody></table>
    </div></div></div></div>

    <div class="modal fade" id="modalProductCategoryEdit" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" data-bind-content></div></div></div></div>
    <div class="modal fade" id="modalProductCategoryCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" data-bind-content></div></div></div></div>
</div>

<script>
    $(document).ready(function() {
        window.$productCategoriesTable = $('#tableProductCategories').DataTable({
            processing:true, serverSide:true, pageLength:25,
            ajax:{ method:'get', url:'{{ route("admin.mobile.product_categories.data") }}' },
            columns:[ {data:'category',name:'category'}, {data:'products_count',name:'products_count',orderable:false,searchable:false}, {data:'sort_order',name:'sort_order'}, {data:'status',name:'status',orderable:false,searchable:false}, {data:'action',name:'action',orderable:false,searchable:false} ]
        });
        window.$productCategoriesTable.on('draw', function() {
            const mc = $.modalCustom({ trigger:'#tambahProductCategory', modal:'#modalProductCategoryCreate', options:{ title:'Tambah Kategori', backdrop:'static', keyboard:false, focus:false, show:false } });
            const me = $.modalCustom({ trigger:'.editProductCategory', modal:'#modalProductCategoryEdit', options:{ title:'Edit Kategori', bind:'product-category', backdrop:'static', keyboard:false, focus:false, show:false } });
            mc.onShow(()=>{ $('#tambahProductCategory').spinner('show'); $.get('{{ route("admin.mobile.product_categories.forms") }}',{view:'product-category-create'}).done((r)=>mc.render(r)).fail((e)=>mc.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`)).always(()=>$('#tambahProductCategory').spinner('hide')); });
            me.onShow((id)=>{ $(`[data-bind-product-category=${id}]`).spinner(); $.get('{{ route("admin.mobile.product_categories.forms") }}',{view:'product-category-edit',id_product_category:id}).done((r)=>me.render(r)).fail((e)=>me.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`)).always(()=>$(`[data-bind-product-category=${id}]`).spinner('hide')); });
        });
    });
    function deleteProductCategory(id_product_category) {
        Swal.fire({ title:'Kamu yakin?', text:'Kategori ini akan dihapus.', icon:'warning', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', showConfirmButton:true, showCancelButton:true, customClass:{cancelButton:'bg-danger',confirmButton:'bg-primary'} }).then((res)=>{
            if(!res.isConfirmed) return;
            $.post('{{ route("admin.mobile.product_categories.destroy") }}',{ id_product_category, _token:'{{ csrf_token() }}' }).done((r)=>{ window.$productCategoriesTable.ajax.reload(); $.toast({heading:'Sukses',text:r.message,showHideTransition:'plain',position:'top-right',icon:'success'}); }).fail((e)=>$.toast({heading:'Warning',text:(e.responseJSON||{}).message||'Terjadi kesalahan.',showHideTransition:'slide',position:'top-right',icon:'warning'}));
        });
    }
</script>
@endsection
