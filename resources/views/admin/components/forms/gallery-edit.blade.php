@php
    $image = public_path('assets/images/galleries/'.$gallery->image);
    $existsFile = !empty($gallery->image) && file_exists($image);
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
                <input name="title" value="{{ $gallery->title }}" type="text" class="form-control" id="title" placeholder="Title">
                <div data-error="title" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
              </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="status">Status</label>
                <select name="an" id="status" class="form-control">
                    <option @selected($gallery->an == 1) value="1">Aktif</option>
                    <option @selected($gallery->an == 0) value="0">Tidak Aktif</option>
                </select>
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
        >{{ $gallery->content }}</textarea>
    </div>
    <div class="form-group">
        <label for="meta_descriptions" class="form-label">Keywords</label>
        <select name="keywords" id="keywords" class="form-control" multiple="multiple">
            @foreach (explode(',', $gallery->keywords ?? '') as $keyword)
                <option selected value="{{ $keyword }}">{{ $keyword }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <x-admin.forms.image-upload 
            :edit="true"
            :image="$existsFile ? image_url('galleries', $gallery->image) : null"
            :label="'Upload Image'"
            :id_input="'input_edit_image_upload'"
        />
        <input type="hidden" name="file_name_input_edit_image_upload" id="file_name_input_edit_image_upload">
        <input type="hidden" name="path_file_input_edit_image_upload" id="path_file_input_edit_image_upload" value="{{ $existsFile ? 'assets/images/galleries/'. $gallery->image : '' }}">
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

        $('#buttonUpdate').click(function() {
            $.post('{{ route('admin.galleries.update', $gallery->id) }}', {
                title: $('[name=title]').val(),
                content: editorInstance.getData(),
                keywords: $('[name=keywords]').val(),
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