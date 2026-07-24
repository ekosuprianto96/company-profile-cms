@include('admin.components.forms.partials.home-section-fields', ['section' => null, 'formId' => 'homeSectionFormCreate'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonAddHomeSection" class="btn btn-primary">Simpan Section</button>
</div>

<script>
    $(document).ready(function() {
        $('#homeSectionFormCreate').on('keyup change', 'input, select', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonAddHomeSection').click(function() {
            const fd = new FormData(document.getElementById('homeSectionFormCreate'));
            $.ajax({ url: '{{ route('admin.mobile.home_sections.store') }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalHomeSectionCreate').modal('hide');
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
