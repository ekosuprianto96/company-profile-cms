@include('admin.components.forms.partials.form-field-fields', ['field' => null, 'formId' => 'formFieldCreate', 'ownerFormId' => request('form_id')])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonAddField" class="btn btn-primary">Simpan Field</button>
</div>

<script>
    $(document).ready(function() {
        $('#formFieldCreate').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonAddField').click(function() {
            const data = window.serializeFieldForm('formFieldCreate') + '&_token={{ csrf_token() }}';
            $.post('{{ route('admin.mobile.forms.fields.store') }}', data)
                .done(function(r) {
                    $('#modalFieldCreate').modal('hide');
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
