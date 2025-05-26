@php
$thumbnail = public_path('assets/images/banners/' . ($banner->image_url ?? ''));
$existsFile = !empty($banner->image_url) && file_exists($thumbnail);
@endphp

<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
      <label for="title">Title</label>
      <input name="title" value="{{ $banner->title }}" type="text" class="form-control" id="title" placeholder="Title">
      <div data-error="title" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
      <label for="sub_title" class="form-label">Sub Title</label>
      <textarea class="form-control" name="sub_title" id="sub_title" style="height: 120px;line-height: 20px" placeholder="Sub title">{{ $banner->sub_title }}</textarea>
      <div data-error="sub_title" class="invalid-fedback">
        <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="form-group">
        <label for="status">Status</label>
        <select name="an" id="status" class="form-control">
            <option @selected($banner->an == 1) value="1">Aktif</option>
            <option @selected($banner->an == 0) value="0">Tidak Aktif</option>
        </select>
    </div>
    <div class="form-group">
        <x-admin.forms.image-upload 
            :edit="true"
            :image="$existsFile ? image_url('banners', $banner->image_url) : null"
            :label="'Upload Image'"
            :id_input="'input_edit_image_upload'"
        />
        <input type="hidden" name="file_name_input_edit_image_upload" id="file_name_input_edit_image_upload">
        <input type="hidden" name="path_file_input_edit_image_upload" id="path_file_input_edit_image_upload" value="{{ $existsFile ? 'assets/images/banners/'. $banner->image_url : '' }}">
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonUpdate" class="btn btn-primary me-2">Submit</button>
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

        $('#buttonUpdate').click(function() {
            $.post('{{ route('admin.banners.update', $banner->id) }}', {
                title: $('[name=title]').val(),
                sub_title: $('[name=sub_title]').val(),
                an: $('[name=an]').val(),
                file_name: $('[name=file_name_input_edit_image_upload]').val(),
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