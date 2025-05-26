<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
      <label for="nama">Nama</label>
      <input name="nama" type="text" class="form-control" id="nama" placeholder="Nama menu">
      <div data-error="nama" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="icon">Icon</label>
      {{-- <input name="icon" type="text" class="form-control" id="icon" placeholder="icon menu"> --}}
      <select id="iconSelected" class="form-control" name="icon">
        @foreach(config('styles.icons') as $key => $value)
            <option value="{{ $value }}" data-icon="{{ $value }}">{{ $value }}</option>
        @endforeach
      </select>
      <div data-error="icon" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="url">URL Menu</label>
      <input name="url" type="url" class="form-control" id="url" placeholder="url menu">
      <div data-error="url" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="id_module" class="mb-0">Module</label>
      <span class="d-block text-danger mb-2" style="font-size: 0.8em">*Silahkan tambah module <a style="text-decoration: underline;color: blue" href="{{ route('admin.modules.index') }}">disini</a>, jika belum tersedia.</span>
      <select name="id_module" id="id_module" class="form-control">
        <option value="">-- Pilih Menu --</option>
        @foreach (App\Models\Modules::where('an', 1)->get() as $module)
            <option
                value="{{ trim($module->id_module) }}"
            >
                {{ trim($module->nama) }}
            </option>
        @endforeach
      </select>
      <div data-error="id_module" class="invalid-fedback">
        <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="deskripsi" class="form-label">Keterangan</label>
      <textarea class="form-control" name="deskripsi" id="deskripsi" style="height: 120px" placeholder="Keterangan module"></textarea>
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select name="an" id="status" class="form-control">
        <option value="1">Aktif</option>
        <option value="0">Tidak Aktif</option>
      </select>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonAdd" class="btn btn-primary me-2">Submit</button>
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
        });

        $('#iconSelected').select2({
            witdh: '100%',
            templateResult: formatIcon,  // Tampilkan ikon di dropdown
            templateSelection: formatIcon, // Tampilkan ikon saat dipilih,
            dropdownParent: $('#modalAdd')
        });

        $('#buttonAdd').click(function() {
            $.post('{{ route('admin.menus.store') }}', {
                nama: $('[name=nama]').val(),
                icon: $('[name=icon]').val(),
                id_module: $('[name=id_module]').val(),
                url: $('[name=url]').val(),
                deskripsi: $('[name=deskripsi]').val(),
                an: $('[name=an]').val(),
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                $('#modalAdd').modal('hide');
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
                    
                    if(errors) {
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