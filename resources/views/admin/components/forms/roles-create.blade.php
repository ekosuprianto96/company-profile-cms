<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
      <label for="nama">Nama</label>
      <input name="nama" type="text" class="form-control" id="nama" placeholder="Nama role">
      <div data-error="nama" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="deskripsi" class="form-label">Keterangan</label>
      <textarea class="form-control" name="deskripsi" id="deskripsi" style="height: 120px" placeholder="Keterangan role"></textarea>
      <div data-error="deskripsi" class="invalid-fedback">
        <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
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

        $('#buttonAdd').click(function() {
            $.post('{{ route('admin.roles.store') }}', {
                nama: $('[name=nama]').val(),
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
</script>