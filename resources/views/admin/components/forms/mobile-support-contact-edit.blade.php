<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <label for="type">Tipe Kontak</label>
        <select name="type" id="type" class="form-control">
            @foreach (['whatsapp' => 'WhatsApp', 'email' => 'Email', 'phone' => 'Telepon', 'instagram' => 'Instagram', 'other' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(optional($contact)->type === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div data-error="type" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="label">Label</label>
        <input name="label" type="text" class="form-control" id="label" value="{{ optional($contact)->label }}">
        <div data-error="label" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="value">Nilai (nomor / email / username)</label>
        <input name="value" type="text" class="form-control" id="value" value="{{ optional($contact)->value }}">
        <div data-error="value" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input name="sort_order" type="number" min="0" class="form-control" id="sort_order" value="{{ optional($contact)->sort_order ?? 0 }}">
                <div data-error="sort_order" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="is_active">Status</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option value="1" @selected(optional($contact)->is_active)>Aktif</option>
                    <option value="0" @selected(! optional($contact)->is_active)>Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" id="buttonEditMobileSupportContact" class="btn btn-primary me-2">Simpan Perubahan</button>
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

        $('#buttonEditMobileSupportContact').click(function() {
            $.post('{{ route('admin.mobile.support_contacts.update', optional($contact)->id) }}', {
                type: $('[name=type]').val(),
                label: $('[name=label]').val(),
                value: $('[name=value]').val(),
                sort_order: $('[name=sort_order]').val(),
                is_active: $('[name=is_active]').val(),
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                $('#modalMobileSupportContactEdit').modal('hide');
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
