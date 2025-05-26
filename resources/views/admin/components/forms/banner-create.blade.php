<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
      <label for="title">Title</label>
      <input name="title" type="text" class="form-control" id="title" placeholder="Title">
      <div data-error="title" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="sub_title" class="form-label">Sub Title</label>
      <textarea class="form-control" name="sub_title" id="sub_title" style="height: 120px;line-height: 20px" placeholder="Sub title"></textarea>
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select name="an" id="status" class="form-control">
        <option value="1">Aktif</option>
        <option value="0">Tidak Aktif</option>
      </select>
    </div>
    <div class="form-group">
        <x-admin.forms.image-upload 
            :label="'Upload Image'"
            :id_input="'input_image_upload'"
        />
        <input type="hidden" name="file_name_input_image_upload" id="file_name_input_image_upload">
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonAddModule" class="btn btn-primary me-2">Submit</button>
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

        $('#buttonAddModule').click(function() {
            $.post('{{ route('admin.banners.store') }}', {
                title: $('[name=title]').val(),
                sub_title: $('[name=sub_title]').val(),
                file_name: $('[name=file_name_input_image_upload]').val(),
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