@include('admin.components.forms.partials.form-fields-meta', ['form' => $form, 'formId' => 'formMetaEdit'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonEditForm" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).ready(function() {
        $('#formMetaEdit').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonEditForm').click(function() {
            $.post('{{ route('admin.mobile.forms.update', $form->id) }}', $('#formMetaEdit').serialize() + '&_token={{ csrf_token() }}')
                .done(function(r) {
                    $('#modalFormEdit').modal('hide');
                    $.toast({ heading: 'Sukses!', text: r.message, position: 'top-right', icon: 'success' });
                    setTimeout(() => location.reload(), 700);
                })
                .fail(function(e) {
                    const r = e.responseJSON || {};
                    if (r.errors) { $.parseErros(r.errors); }
                    $.toast({ heading: 'Warning', text: r.message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' });
                });
        });
    });
</script>
