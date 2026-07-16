@include('admin.components.forms.partials.product-fields', ['product' => null, 'categories' => $categories, 'services' => $services, 'formId' => 'productFormCreate'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonAddProduct" class="btn btn-primary">Simpan Produk</button>
</div>

<script>
    $(document).ready(function() {
        $('#productFormCreate').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonAddProduct').click(function() {
            const fd = new FormData(document.getElementById('productFormCreate'));
            $.ajax({ url: '{{ route('admin.mobile.products.store') }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalProductCreate').modal('hide');
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
