@include('admin.components.forms.partials.form-field-fields', ['field' => $field, 'formId' => 'formFieldEdit'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonEditField" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).ready(function() {
        $('#formFieldEdit').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonEditField').click(function() {
            const data = window.serializeFieldForm('formFieldEdit') + '&_token={{ csrf_token() }}';
            $.post('{{ route('admin.mobile.forms.fields.update', $field->id) }}', data)
                .done(function(r) {
                    $('#modalFieldEdit').modal('hide');
                    $.toast({ heading: 'Sukses!', text: r.message, position: 'top-right', icon: 'success' });
                    setTimeout(() => location.reload(), 600);
                })
                .fail(function(e) {
                    const r = e.responseJSON || {};
                    if (r.errors) { $.parseErros(r.errors); }
                    $.toast({ heading: 'Warning', text: r.message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' });
                });
        });
    });
</script>
