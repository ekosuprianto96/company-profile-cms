<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">Nama Suplier</label>
        <input name="name" type="text" class="form-control" id="name" placeholder="Contoh: CV Mebel Jaya">
        <div data-error="name" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="contact_person">Narahubung <small class="text-muted">(opsional)</small></label>
                <input name="contact_person" type="text" class="form-control" id="contact_person" placeholder="Nama PIC">
                <div data-error="contact_person" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone">Telepon / WhatsApp <small class="text-muted">(opsional)</small></label>
                <input name="phone" type="text" class="form-control" id="phone" placeholder="0812xxxx">
                <div data-error="phone" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="email">Email <small class="text-muted">(opsional)</small></label>
        <input name="email" type="email" class="form-control" id="email" placeholder="supplier@email.com">
        <div data-error="email" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="address">Alamat <small class="text-muted">(opsional)</small></label>
        <input name="address" type="text" class="form-control" id="address" placeholder="Alamat suplier">
        <div data-error="address" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="notes">Catatan <small class="text-muted">(opsional)</small></label>
        <textarea name="notes" rows="2" class="form-control" id="notes" placeholder="Catatan internal tentang suplier"></textarea>
        <div data-error="notes" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="is_active">Status</label>
        <select name="is_active" id="is_active" class="form-control">
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
        </select>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" id="buttonAddSupplier" class="btn btn-primary me-2">Simpan</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('input, select, textarea').on('keyup change', function() {
            const field = $(this).attr('name');
            if (!field) return;
            $(this).removeClass('is-invalid');
            $(`[data-error=${field}]`).find('span').text('');
        });

        $('#buttonAddSupplier').click(function() {
            $.post('{{ route('admin.mobile.suppliers.store') }}', {
                name: $('[name=name]').val(),
                contact_person: $('[name=contact_person]').val(),
                phone: $('[name=phone]').val(),
                email: $('[name=email]').val(),
                address: $('[name=address]').val(),
                notes: $('[name=notes]').val(),
                is_active: $('[name=is_active]').val(),
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                $('#modalSupplierCreate').modal('hide');
                window.$suppliersTable.ajax.reload();
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
