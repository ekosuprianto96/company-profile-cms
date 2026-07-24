@include('admin.components.forms.partials.home-section-fields', ['section' => $section, 'formId' => 'homeSectionFormEdit'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonEditHomeSection" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).ready(function() {
        $('#homeSectionFormEdit').on('keyup change', 'input, select', function() {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#buttonEditHomeSection').click(function() {
            const fd = new FormData(document.getElementById('homeSectionFormEdit'));
            $.ajax({ url: '{{ route('admin.mobile.home_sections.update', $section->id) }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function(r) {
                    $('#modalHomeSectionEdit').modal('hide');
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
