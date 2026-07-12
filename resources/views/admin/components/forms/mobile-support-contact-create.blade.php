<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <label for="type">Tipe Kontak</label>
        <select name="type" id="type" class="form-control">
            <option value="whatsapp">WhatsApp</option>
            <option value="email">Email</option>
            <option value="phone">Telepon</option>
            <option value="instagram">Instagram</option>
            <option value="other">Lainnya</option>
        </select>
        <div data-error="type" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="label">Label</label>
        <input name="label" type="text" class="form-control" id="label" placeholder="Contoh: WhatsApp Admin">
        <div data-error="label" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="value">Nilai (nomor / email / username)</label>
        <input name="value" type="text" class="form-control" id="value" placeholder="Contoh: +6281234567890 atau support@maninjau.app">
        <small class="text-muted">WhatsApp/Telepon: nomor. Email: alamat email. Instagram: username (tanpa @).</small>
        <div data-error="value" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input name="sort_order" type="number" min="0" class="form-control" id="sort_order" value="0">
                <div data-error="sort_order" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="is_active">Status</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" id="buttonAddMobileSupportContact" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('input, select').on('keyup change', function() {
            const field = $(this).attr('name');
            if (!field) return;
            $(this).removeClass('is-invalid');
            $(`[data-error=${field}]`).find('span').text('');
        });

        $('#buttonAddMobileSupportContact').click(function() {
            $.post('{{ route('admin.mobile.support_contacts.store') }}', {
                type: $('[name=type]').val(),
                label: $('[name=label]').val(),
                value: $('[name=value]').val(),
                sort_order: $('[name=sort_order]').val(),
                is_active: $('[name=is_active]').val(),
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                $('#modalMobileSupportContactCreate').modal('hide');
                window.$mobileSupportContactsTable.ajax.reload();
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
