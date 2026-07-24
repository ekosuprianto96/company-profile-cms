@include('admin.components.forms.partials.voucher-fields', ['voucher' => null, 'services' => $services, 'users' => $users, 'formId' => 'voucherFormCreate'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonAddVoucher" class="btn btn-primary">Simpan Voucher</button>
</div>

<script>
    $(document).ready(function() {
        $('#voucherFormCreate').on('keyup change', 'input, select, textarea', function() {
            const field = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${field}"]`).find('span').text('');
        });

        initVoucherTermsEditor('#voucherFormCreate_terms');

        $('#buttonAddVoucher').click(function() {
            if (window.voucherTermsEditor) window.voucherTermsEditor.updateSourceElement();
            const data = $('#voucherFormCreate').serialize() + '&_token={{ csrf_token() }}';
            $.post('{{ route('admin.mobile.vouchers.store') }}', data)
                .done(function(response) {
                    $('#modalVoucherCreate').modal('hide');
                    window.$vouchersTable.ajax.reload();
                    $.toast({ heading: 'Sukses!', text: response.message, showHideTransition: 'slide', position: 'top-right', icon: 'success' });
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};
                    if (response.errors) { $.parseErros(response.errors); }
                    $.toast({ heading: 'Warning', text: response.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' });
                });
        });
    });
</script>
