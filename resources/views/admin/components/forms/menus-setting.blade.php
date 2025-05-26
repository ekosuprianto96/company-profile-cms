<form action="#" method="POST">
    <div class="form-group">
        <label for="roles" class="form-label">Setting Role Akses Menu</label>
        <select name="roles" id="roles" class="form-control" multiple="multiple">
            @foreach (App\Models\Role::where('an', 1)->get() as $role)
                <option {{ $role->hasMenu($menu->id_menu) ? 'selected' : '' }} value="{{ $role->id_role }}">{{ $role->nama }}</option>
            @endforeach
        </select>
        <div data-error="roles" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonAttach" class="btn btn-primary me-2">Simpan</button>
    </div>
</form>

<script>
    $('#roles').select2({
        width: '100%',
        dropdownParent: $('#modalSetting')
    });

    $(document).ready(function() {
        $('#buttonAttach').click(function() {
            $.post('{{ route('admin.menus.attach') }}', {
                roles: $('#roles').val(),
                id_menu: '{{ $menu->id_menu }}',
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                $('#modalSetting').modal('hide');
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

                if(response) {
                    const { message } = response;
                    
                    $('[data-error=roles]').find('span').text(message);

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

        $('#iconSelected').select2({
            witdh: '100%',
            templateResult: formatIcon,  // Tampilkan ikon di dropdown
            templateSelection: formatIcon, // Tampilkan ikon saat dipilih,
            dropdownParent: $('#modalUpdate')
        });

    })
    
    function formatIcon(option) {
        if (!option.id) {
            return option.text;
        }

        var $icon = $(
            '<span><i class="' + $(option.element).data('icon') + ' me-2"></i> <span>' + option.text + '</span></span>'
        );
        return $icon;
    }
</script>