@include('admin.components.forms.partials.form-fields-meta', ['form' => null, 'formId' => 'formMetaCreate'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonAddForm" class="btn btn-primary">Simpan Form</button>
</div>

<script>
    $(document).ready(function() {
        $('#formMetaCreate').on('keyup change', 'input, select, textarea', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonAddForm').click(function() {
            $.post('{{ route('admin.mobile.forms.store') }}', $('#formMetaCreate').serialize() + '&_token={{ csrf_token() }}')
                .done(function(r) {
                    $('#modalFormCreate').modal('hide');
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
