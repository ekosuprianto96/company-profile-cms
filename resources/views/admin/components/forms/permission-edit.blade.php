<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <label for="display_name">Display</label>
        <input name="display_name" value="{{ $permission->display_name }}" type="text" class="form-control" id="display_name" placeholder="Display Name...">
        <div data-error="display_name" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
    <div class="form-group">
        <label for="name">Name</label>
        <input name="name" value="{{ $permission->name }}" type="text" class="form-control" id="name" placeholder="Name...">
        <div data-error="name" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
    <div class="form-group">
        <label for="status">Status</label>
        <select name="an" id="status" class="form-control">
            <option {{ trim($permission->an) == 1 ? 'selected' : '' }} value="1">Aktif</option>
            <option {{ trim($permission->an) == 0 ? 'selected' : '' }} value="0">Tidak Aktif</option>
        </select>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonUpdate" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        const inputForms = $('input, select');
        $.each(inputForms, function(index, value) {
            if (value.tagName === 'INPUT') {
                $(value).keyup(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            } else if (value.tagName === 'SELECT') {
                $(value).change(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }
        });

        $('#buttonUpdate').click(function() {
            $.post('{{ route('admin.permissions.update', $permission->id) }}', {
                    display_name: $('[name=display_name]').val(),
                    name: $('[name=name]').val(),
                    an: $('[name=an]').val(),
                    _token: '{{ csrf_token() }}'
                }).done(function(response) {
                $('#modalUpdate').modal('hide');
                $table.ajax.reload();
                $.toast({
                    heading: 'Sukses!',
                    text: response.message,
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'success'
                });
            }).fail(function(error) {
                const response = error.responseJSON;

                if (response) {
                    const {
                        errors
                    } = response;

                    if (errors) {
                        $.parseErros(errors);
                    }

                    $.toast({
                        heading: 'Warning',
                        text: response.message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                }
            })
        });
    })
</script>