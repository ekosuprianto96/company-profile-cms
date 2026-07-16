<form id="shippingCourierFormEdit" class="forms-sample">
    @csrf
    <div class="row">
        <div class="col-md-8 form-group"><label class="form-label">Nama Kurir</label><input name="name" type="text" class="form-control" value="{{ optional($courier)->name }}"><div data-error="name"><span class="text-danger" style="font-size:.8em"></span></div></div>
        <div class="col-md-4 form-group"><label class="form-label">Kode</label><input name="code" type="text" class="form-control" value="{{ optional($courier)->code }}"></div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group"><label class="form-label">Tipe</label><select name="is_third_party" class="form-control"><option value="0" @selected(!optional($courier)->is_third_party)>Kurir Internal</option><option value="1" @selected(optional($courier)->is_third_party)>Jasa Kurir (pihak ke-3)</option></select></div>
        <div class="col-md-6 form-group"><label class="form-label">Estimasi (ETD)</label><input name="etd" type="text" class="form-control" value="{{ optional($courier)->etd }}" placeholder="2–3 hari"></div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group"><label class="form-label">Ongkir Dasar (Rp)</label><input name="base_cost" type="number" min="0" class="form-control" value="{{ optional($courier)->base_cost ?? 0 }}"></div>
        <div class="col-md-3 form-group"><label class="form-label">Urutan</label><input name="sort_order" type="number" min="0" class="form-control" value="{{ optional($courier)->sort_order ?? 0 }}"></div>
        <div class="col-md-3 form-group"><label class="form-label">Status</label><select name="is_active" class="form-control"><option value="1" @selected(optional($courier)->is_active)>Aktif</option><option value="0" @selected($courier && ! $courier->is_active)>Nonaktif</option></select></div>
    </div>
</form>
<div class="d-flex justify-content-end"><button type="button" id="buttonEditShippingCourier" class="btn btn-primary">Simpan Perubahan</button></div>
<script>
    $(document).ready(function() {
        $('#shippingCourierFormEdit').on('keyup change','input,select',function(){ const f=$(this).attr('name'); $(this).removeClass('is-invalid'); $(`[data-error="${f}"]`).find('span').text(''); });
        $('#buttonEditShippingCourier').click(function() {
            $.post('{{ route('admin.mobile.shipping_couriers.update', optional($courier)->id) }}', $('#shippingCourierFormEdit').serialize() + '&_token={{ csrf_token() }}')
                .done((r)=>{ $('#modalShippingCourierEdit').modal('hide'); window.$shippingCouriersTable.ajax.reload(); $.toast({heading:'Sukses!',text:r.message,showHideTransition:'slide',position:'top-right',icon:'success'}); })
                .fail((e)=>{ const r=e.responseJSON||{}; if(r.errors){$.parseErros(r.errors);} $.toast({heading:'Warning',text:r.message||'Terjadi kesalahan.',showHideTransition:'slide',position:'top-right',icon:'warning'}); });
        });
    });
</script>
