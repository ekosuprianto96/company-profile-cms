<form class="forms-sample" action="#" method="POST">
    <div class="form-group">
      <label for="nama">Nama</label>
      <input name="nama" type="text" value="{{ $module->nama }}" class="form-control" id="nama" placeholder="Nama module">
      <div data-error="nama" class="invalid-fedback">
        <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
        <label for="icon">Icon</label>
        {{-- <input name="icon" value="{{ $module->icon }}" type="text" class="form-control" id="icon" placeholder="icon module"> --}}
        <select id="iconSelected" class="form-control" style="width: 100%" name="icon">
            @foreach(config('styles.icons') as $key => $value)
                <option @selected($module->icon === $value) value="{{ $value }}" data-icon="{{ $value }}">{{ $value }}</option>
            @endforeach
        </select>
        <div data-error="icon" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
    <div class="form-group">
      <label for="group_module">Group</label>
      <select name="id_group" id="group_module" class="form-control">
        <option value="">-- Pilih Group --</option>
        @foreach (App\Models\Group::get() as $group)
            <option 
                {{ trim($module->group->id_group) === trim($group->id_group) ? 'selected' : '' }} 
                value="{{ trim($group->id_group) }}"
            >
                {{ trim($group->nama) }}
            </option>
        @endforeach
      </select>
      <div data-error="id_group" class="invalid-fedback">
        <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="deskripsi" class="form-label">Keterangan</label>
      <textarea class="form-control" name="deskripsi" id="deskripsi" style="height: 120px" placeholder="Keterangan module">{{ $module->deskripsi }}</textarea>
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select name="an" id="status" class="form-control">
        <option {{ trim($module->an) == 1 ? 'selected' : '' }} value="1">Aktif</option>
        <option {{ trim($module->an) == 0 ? 'selected' : '' }} value="0">Tidak Aktif</option>
      </select>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="updateModule" class="btn btn-primary me-2">Submit</button>
    </div>
</form>
<script>
    $(document).ready(function() {
        const inputForms = $('input, select');
        $.each(inputForms, function(index, value) {
            if(value.tagName === 'INPUT') {
                $(value).keyup(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }else if(value.tagName === 'SELECT') {
                $(value).change(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }
        })

        $('#iconSelected').select2({
            witdh: '100%',
            templateResult: formatIcon,  // Tampilkan ikon di dropdown
            templateSelection: formatIcon, // Tampilkan ikon saat dipilih,
            dropdownParent: $('#modalUpdate')
        });

        $('#updateModule').click(function() {
            $.post('{{ route('admin.modules.update', $module->id_module) }}', {
                nama: $('[name=nama]').val(),
                icon: $('[name=icon]').val(),
                id_group: $('[name=id_group]').val(),
                deskripsi: $('[name=deskripsi]').val(),
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

                if(response) {
                    const { errors } = response;
                    
                    $.parseErros(errors);

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
    });

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