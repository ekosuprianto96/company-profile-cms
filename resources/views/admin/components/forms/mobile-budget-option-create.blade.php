<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">Nama Pilihan Anggaran</label>
        <input name="name" type="text" class="form-control" id="name" placeholder="Contoh: 100 Juta - 300 Juta">
        <div data-error="name" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="min_amount">Nominal Minimum (Rp)</label>
                <input name="min_amount" type="number" min="0" class="form-control" id="min_amount" placeholder="Contoh: 100000000">
                <div data-error="min_amount" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="max_amount">Nominal Maksimum (Rp)</label>
                <input name="max_amount" type="number" min="0" class="form-control" id="max_amount" placeholder="Contoh: 300000000">
                <div data-error="max_amount" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
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
        <button type="button" id="buttonAddMobileBudgetOption" class="btn btn-primary me-2">Submit</button>
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

        $('#buttonAddMobileBudgetOption').click(function() {
            $.post('{{ route('admin.mobile.budget_options.store') }}', {
                name: $('[name=name]').val(),
                min_amount: $('[name=min_amount]').val(),
                max_amount: $('[name=max_amount]').val(),
                sort_order: $('[name=sort_order]').val(),
                is_active: $('[name=is_active]').val(),
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                $('#modalMobileBudgetOptionCreate').modal('hide');
                window.$mobileBudgetOptionsTable.ajax.reload();
                $.toast({
                    heading: 'Sukses!',
                    text: response.message,
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'success'
                });
            })
            .fail(function(error) {
                const response = error.responseJSON || {};
                if (response.errors) {
                    $.parseErros(response.errors);
                }

                $.toast({
                    heading: 'Warning',
                    text: response.message || 'Terjadi kesalahan.',
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'warning'
                });
            });
        });
    });
</script>

