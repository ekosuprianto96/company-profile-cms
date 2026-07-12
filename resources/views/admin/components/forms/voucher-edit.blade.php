@include('admin.components.forms.partials.voucher-fields', ['voucher' => $voucher, 'services' => $services, 'users' => $users, 'formId' => 'voucherFormEdit'])

<div class="d-flex justify-content-end mt-2">
    <button type="button" id="buttonEditVoucher" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).ready(function() {
        $('#voucherFormEdit').on('keyup change', 'input, select, textarea', function() {
            const field = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${field}"]`).find('span').text('');
        });

        $('#buttonEditVoucher').click(function() {
            const data = $('#voucherFormEdit').serialize() + '&_token={{ csrf_token() }}';
            $.post('{{ route('admin.mobile.vouchers.update', optional($voucher)->id) }}', data)
                .done(function(response) {
                    $('#modalVoucherEdit').modal('hide');
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
