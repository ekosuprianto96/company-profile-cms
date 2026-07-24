@include('admin.components.forms.partials.category-fields', ['category' => null, 'formId' => 'categoryFormCreate'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonAddCategory" class="btn btn-primary">Simpan Kategori</button>
</div>

<script>
    $(document).ready(function() {
        $('#categoryFormCreate').on('keyup change', 'input, select', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonAddCategory').click(function() {
            const fd = new FormData(document.getElementById('categoryFormCreate'));
            $.ajax({ url: '{{ route('admin.mobile.categories.store') }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalCategoryCreate').modal('hide');
                    $.toast({ heading: 'Sukses!', text: r.message, showHideTransition: 'slide', position: 'top-right', icon: 'success' });
                    setTimeout(() => location.reload(), 700);
                })
                .fail(function(e) {
                    const r = e.responseJSON || {};
                    if (r.errors) { $.parseErros(r.errors); }
                    $.toast({ heading: 'Warning', text: r.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' });
                });
        });
    });
</script>
