@php
    $thumbnailPath = public_path('assets/images/informasi/' . ($informasi->value->thumbnail->file ?? ''));
    $existsFile = !empty($informasi->value->thumbnail->file) && file_exists($thumbnailPath);
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
                <input name="title" value="{{ $informasi->value->title }}" type="text" class="form-control" id="title" placeholder="Title">
                <div data-error="title" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="style">Style</label>
                <select name="style" id="style" class="form-control">
                    <option @selected($informasi->value->style == 'horizontal-column') value="horizontal-column">Horizontal Column</option>
                    <option @selected($informasi->value->style == 'vertical-column') value="vertical-column">Vertical Column</option>
                </select>
                <div data-error="style" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
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
        >{{ $informasi->value->content }}</textarea>
    </div>
    <div class="form-group">
        <x-admin.forms.image-upload 
            :edit="true"
            :image="$existsFile ? image_url('informasi', $informasi->value->thumbnail->file) : null"
            :label="'Upload Image'"
            :id_input="'input_edit_image_upload'"
        />
        <input type="hidden" name="file_name_input_edit_image_upload" id="file_name_input_edit_image_upload">
        <input type="hidden" name="path_file_input_edit_image_upload" id="path_file_input_edit_image_upload" value="{{ $existsFile ? 'assets/images/informasi/'. $informasi->value->thumbnail->file : '' }}">
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
                title: $('[name=title]').val(),
                style: $('[name=style]').val(),
                content: editorInstance.getData(),
                thumbnail: $('[name=file_name_input_edit_image_upload]').val(),
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