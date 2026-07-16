<form id="productCategoryFormCreate" class="forms-sample">
    @csrf
    <div class="form-group"><label class="form-label">Nama Kategori</label><input name="name" type="text" class="form-control" placeholder="Sofa"><div data-error="name"><span class="text-danger" style="font-size:.8em"></span></div></div>
    <div class="form-group"><label class="form-label">Ikon <small class="text-muted">(kelas Remix Icon, opsional)</small></label><input name="icon" type="text" class="form-control" placeholder="ri-sofa-line"></div>
    <div class="row">
        <div class="col-md-6 form-group"><label class="form-label">Urutan</label><input name="sort_order" type="number" min="0" class="form-control" value="0"></div>
        <div class="col-md-6 form-group"><label class="form-label">Status</label><select name="is_active" class="form-control"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
    </div>
</form>
<div class="d-flex justify-content-end"><button type="button" id="buttonAddProductCategory" class="btn btn-primary">Simpan</button></div>
<script>
    $(document).ready(function() {
        $('#productCategoryFormCreate').on('keyup change','input,select',function(){ const f=$(this).attr('name'); $(this).removeClass('is-invalid'); $(`[data-error="${f}"]`).find('span').text(''); });
        $('#buttonAddProductCategory').click(function() {
            $.post('{{ route('admin.mobile.product_categories.store') }}', $('#productCategoryFormCreate').serialize() + '&_token={{ csrf_token() }}')
                .done((r)=>{ $('#modalProductCategoryCreate').modal('hide'); window.$productCategoriesTable.ajax.reload(); $.toast({heading:'Sukses!',text:r.message,showHideTransition:'slide',position:'top-right',icon:'success'}); })
                .fail((e)=>{ const r=e.responseJSON||{}; if(r.errors){$.parseErros(r.errors);} $.toast({heading:'Warning',text:r.message||'Terjadi kesalahan.',showHideTransition:'slide',position:'top-right',icon:'warning'}); });
        });
    });
</script>
