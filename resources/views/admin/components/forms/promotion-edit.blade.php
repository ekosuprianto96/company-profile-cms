@include('admin.components.forms.partials.promotion-fields', ['promotion' => $promotion, 'formId' => 'promotionFormEdit'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonEditPromotion" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).ready(function() {
        $('#promotionFormEdit').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonEditPromotion').click(function() {
            const fd = new FormData(document.getElementById('promotionFormEdit'));
            $.ajax({ url: '{{ route('admin.mobile.promotions.update', $promotion->id) }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalPromotionEdit').modal('hide');
                    window.$promotionsTable.ajax.reload();
                    $.toast({ heading: 'Sukses!', text: r.message, showHideTransition: 'slide', position: 'top-right', icon: 'success' });
                })
                .fail(function(e) {
                    const r = e.responseJSON || {};
                    if (r.errors) { $.parseErros(r.errors); }
                    $.toast({ heading: 'Warning', text: r.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' });
                });
        });
    });
</script>
