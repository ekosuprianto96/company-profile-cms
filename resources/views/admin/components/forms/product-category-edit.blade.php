<form id="productCategoryFormEdit" class="forms-sample">
    @csrf
    <div class="form-group"><label class="form-label">Nama Kategori</label><input name="name" type="text" class="form-control" value="{{ optional($category)->name }}"><div data-error="name"><span class="text-danger" style="font-size:.8em"></span></div></div>
    <div class="form-group"><label class="form-label">Ikon <small class="text-muted">(kelas Remix Icon, opsional)</small></label><input name="icon" type="text" class="form-control" value="{{ optional($category)->icon }}" placeholder="ri-sofa-line"></div>
    <div class="row">
        <div class="col-md-6 form-group"><label class="form-label">Urutan</label><input name="sort_order" type="number" min="0" class="form-control" value="{{ optional($category)->sort_order ?? 0 }}"></div>
        <div class="col-md-6 form-group"><label class="form-label">Status</label><select name="is_active" class="form-control"><option value="1" @selected(optional($category)->is_active)>Aktif</option><option value="0" @selected($category && ! $category->is_active)>Nonaktif</option></select></div>
    </div>
</form>
<div class="d-flex justify-content-end"><button type="button" id="buttonEditProductCategory" class="btn btn-primary">Simpan Perubahan</button></div>
<script>
    $(document).ready(function() {
        $('#productCategoryFormEdit').on('keyup change','input,select',function(){ const f=$(this).attr('name'); $(this).removeClass('is-invalid'); $(`[data-error="${f}"]`).find('span').text(''); });
        $('#buttonEditProductCategory').click(function() {
            $.post('{{ route('admin.mobile.product_categories.update', optional($category)->id) }}', $('#productCategoryFormEdit').serialize() + '&_token={{ csrf_token() }}')
                .done((r)=>{ $('#modalProductCategoryEdit').modal('hide'); window.$productCategoriesTable.ajax.reload(); $.toast({heading:'Sukses!',text:r.message,showHideTransition:'slide',position:'top-right',icon:'success'}); })
                .fail((e)=>{ const r=e.responseJSON||{}; if(r.errors){$.parseErros(r.errors);} $.toast({heading:'Warning',text:r.message||'Terjadi kesalahan.',showHideTransition:'slide',position:'top-right',icon:'warning'}); });
        });
    });
</script>
