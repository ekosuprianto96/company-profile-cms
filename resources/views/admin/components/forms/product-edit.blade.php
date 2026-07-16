@include('admin.components.forms.partials.product-fields', ['product' => $product, 'categories' => $categories, 'services' => $services, 'formId' => 'productFormEdit'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonEditProduct" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).ready(function() {
        $('#productFormEdit').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonEditProduct').click(function() {
            const fd = new FormData(document.getElementById('productFormEdit'));
            $.ajax({ url: '{{ route('admin.mobile.products.update', optional($product)->id) }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalProductEdit').modal('hide');
                    window.$productsTable.ajax.reload();
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
