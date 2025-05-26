@php
    $image = public_path('assets/images/services/'.$service->image);
    $existsFile = !empty($service->image) && file_exists($image);
@endphp

<style>
    .ck-editor__editable {min-height: 250px;}
</style>

<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="title">Title</label>
                <input name="title" value="{{ $service->title }}" type="text" class="form-control" id="title" placeholder="Title">
                <div data-error="title" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
              </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="status">Status</label>
                <select name="an" id="status" class="form-control">
                    <option @selected($service->an == 1) value="1">Aktif</option>
                    <option @selected($service->an == 0) value="0">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="icon">Icon</label>
        {{-- <input name="icon" type="text" class="form-control" id="icon" placeholder="icon menu"> --}}
        <select id="iconSelected" class="form-control" name="icon">
          @foreach(config('styles.icons') as $key => $value)
              <option {{ $service->icon == $value ? 'selected' : '' }} value="{{ $value }}" data-icon="{{ $value }}">{{ $value }}</option>
          @endforeach
        </select>
        <div data-error="icon" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
    <div class="form-group">
        <label for="content" class="form-label">Content</label>
        <textarea 
            class="form-control" 
            name="content" 
            id="content" 
            style="height: 250px" 
            placeholder="Tulis deskripsi disini..."
        >
            {{ $service->content }}
        </textarea>
    </div>
    <div class="form-group">
        <label for="keywords" class="form-label">Keywords</label>
        <select name="keywords" id="keywords" class="form-control" multiple="multiple">
            @foreach (explode(',', $service->keywords ?? '') as $keyword)
                <option selected value="{{ $keyword }}">{{ $keyword }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <x-admin.forms.image-upload 
            :edit="true"
            :image="$existsFile ? image_url('services', $service->image) : null"
            :label="'Upload Image'"
            :id_input="'input_edit_image_upload'"
        />
        <input type="hidden" name="file_name_input_edit_image_upload" id="file_name_input_edit_image_upload">
        <input type="hidden" name="path_file_input_edit_image_upload" id="path_file_input_edit_image_upload" value="{{ $existsFile ? 'assets/images/services/'. $service->image : '' }}">
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonUpdate" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>

    var editorInstance;
    ClassicEditor
    .create( document.querySelector( '#content'), {
        extraPlugins: [
            function(editor) {
                createCustomUploadAdapterPlugin({
                    url: '{{ route('admin.ckeditor.upload') }}',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })(editor);

                new ImageRemovePlugin(editor);
            }
        ],
        removePlugins: ['Markdown'], // Matikan plugin Markdown
    })
    .then( editor => {
        editorInstance = editor;

        editor.on('image:removed', (event, {imageRemoved}) => {
            fetch('{{ route('admin.ckeditor.cleanup') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    images: imageRemoved
                })
            });
        });
    });

    $(document).ready(function() {
        $('#keywords').select2({
            width: '100%',
            tags: true
        });

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
            width: '100%',
            templateResult: formatIcon,  // Tampilkan ikon di dropdown
            templateSelection: formatIcon, // Tampilkan ikon saat dipilih,
            dropdownParent: $('#modalUpdate')
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

        $('#buttonUpdate').click(function() {
            $.post('{{ route('admin.services.update', $service->id) }}', {
                title: $('[name=title]').val(),
                content: editorInstance.getData(),
                keywords: $('[name=keywords]').val(),
                icon: $('[name=icon]').val(),
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