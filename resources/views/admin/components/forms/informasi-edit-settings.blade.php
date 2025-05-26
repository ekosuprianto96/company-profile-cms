@php
    $appLogo = public_path('assets/images/informasi/' . ($informasi->value->app_logo->file ?? ''));
    $existsFile = !empty($informasi->value->app_logo->file) && file_exists($appLogo);

    $icon = public_path('assets/images/informasi/' . ($informasi->value->favicon->file ?? ''));
    $existsFileIcon = !empty($informasi->value->favicon->file) && file_exists($icon);
@endphp
<style>
    .ck-editor__editable {min-height: 250px;}
</style>

<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
      <label for="app_name">Nama Aplikasi</label>
      <input name="app_name" value="{{ $informasi->value->app_name }}" type="text" class="form-control" id="app_name" placeholder="Nama Aplikasi">
      <div data-error="app_name" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="background_menu_header">Background Menu Header</label>
                <input name="background_menu_header" value="{{ $informasi->value->background_menu_header }}" type="color" class="form-control" id="background_menu_header">
                <div data-error="background_menu_header" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="text_menu_header">Text Menu Header</label>
                <input name="text_menu_header" value="{{ $informasi->value->text_menu_header }}" type="color" class="form-control" id="text_menu_header">
                <div data-error="text_menu_header" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="text_hover_menu_header">Text Hover Menu Header</label>
                <input name="text_hover_menu_header" value="{{ $informasi->value->text_hover_menu_header }}" type="color" class="form-control" id="text_hover_menu_header">
                <div data-error="text_hover_menu_header" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <x-admin.forms.image-upload 
            :edit="true"
            :image="$existsFileIcon ? image_url('informasi', $informasi->value->favicon->file) : null"
            :label="'Favicon Website'"
            :notice="'Rekomendasi: 512px x 512px'"
            :id_input="'input_edit_image_upload_favicon'"
        />
        <input type="hidden" name="file_name_input_edit_image_upload_favicon" id="file_name_input_edit_image_upload_favicon">
        <input type="hidden" name="path_file_input_edit_image_upload_favicon" id="path_file_input_edit_image_upload_favicon" value="{{ $existsFileIcon ? 'assets/images/informasi/'. $informasi->value->favicon->file : '' }}">
    </div>
    <div class="form-group">
        <x-admin.forms.image-upload 
            :edit="true"
            :image="$existsFile ? image_url('informasi', $informasi->value->app_logo->file) : null"
            :label="'Logo Aplikasi'"
            :notice="'Rekomendasi: 512px x 512px'"
            :id_input="'input_edit_image_upload'"
        />
        <input type="hidden" name="file_name_input_edit_image_upload" id="file_name_input_edit_image_upload">
        <input type="hidden" name="path_file_input_edit_image_upload" id="path_file_input_edit_image_upload" value="{{ $existsFile ? 'assets/images/informasi/'. $informasi->value->app_logo->file : '' }}">
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
            $.post('{{ route('admin.informasi.update', $informasi->id) }}', {
                app_name: $('[name=app_name]').val(),
                background_menu_header: $('[name=background_menu_header]').val(),
                text_menu_header: $('[name=text_menu_header]').val(),
                text_hover_menu_header: $('[name=text_hover_menu_header]').val(),
                app_logo: $('[name=file_name_input_edit_image_upload]').val(),
                favicon: $('[name=file_name_input_edit_image_upload_favicon]').val(),
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                $('#modalUpdate').modal('hide');
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