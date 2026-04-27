<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">Nama Jenis Kebutuhan</label>
        <input name="name" type="text" class="form-control" id="name" value="{{ $needType->name }}" placeholder="Contoh: Perencanaan">
        <div data-error="name" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="description">Deskripsi</label>
        <input name="description" type="text" class="form-control" id="description" value="{{ $needType->description }}" placeholder="Opsional">
        <div data-error="description" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input name="sort_order" type="number" min="0" class="form-control" id="sort_order" value="{{ $needType->sort_order }}">
                <div data-error="sort_order" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="is_active">Status</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option @selected($needType->is_active) value="1">Aktif</option>
                    <option @selected(!$needType->is_active) value="0">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" id="buttonUpdateMobileNeedType" class="btn btn-primary me-2">Submit</button>
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

        $('#buttonUpdateMobileNeedType').click(function() {
            $.post('{{ route('admin.mobile.service_need_types.update', $needType->id) }}', {
                name: $('[name=name]').val(),
                description: $('[name=description]').val(),
                sort_order: $('[name=sort_order]').val(),
                is_active: $('[name=is_active]').val(),
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                $('#modalMobileNeedTypeEdit').modal('hide');
                window.$mobileNeedTypesTable.ajax.reload();
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

