@include('admin.components.forms.partials.category-fields', ['category' => $category, 'formId' => 'categoryFormEdit'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonUpdateCategory" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).ready(function() {
        $('#categoryFormEdit').on('keyup change', 'input, select', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonUpdateCategory').click(function() {
            const fd = new FormData(document.getElementById('categoryFormEdit'));
            $.ajax({ url: '{{ route('admin.mobile.categories.update', $category->id) }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalCategoryEdit').modal('hide');
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
