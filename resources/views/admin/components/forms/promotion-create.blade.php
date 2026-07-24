@include('admin.components.forms.partials.promotion-fields', ['promotion' => null, 'formId' => 'promotionFormCreate'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonAddPromotion" class="btn btn-primary">Simpan Promosi</button>
</div>

<script>
    $(document).ready(function() {
        $('#promotionFormCreate').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonAddPromotion').click(function() {
            const fd = new FormData(document.getElementById('promotionFormCreate'));
            $.ajax({ url: '{{ route('admin.mobile.promotions.store') }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalPromotionCreate').modal('hide');
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
