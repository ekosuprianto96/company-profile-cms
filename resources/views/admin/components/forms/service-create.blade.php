<style>
    .ck-editor__editable {min-height: 250px;}
</style>
<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="title">Title</label>
                <input name="title" type="text" class="form-control" id="title" placeholder="Title">
                <div data-error="title" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="status">Status</label>
                <select name="an" id="status" class="form-control">
                  <option value="1">Aktif</option>
                  <option value="0">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="type_icon">Type Icon</label>
                {{-- <input name="icon" type="text" class="form-control" id="icon" placeholder="icon menu"> --}}
                <select id="type_icon" class="form-control" name="type_icon">
                    <option value="icon">Icon</option>
                    <option value="image">Image</option>
                </select>
                <div data-error="type_icon" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div id="icon_icon">
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
            </div>
            <div id="image_icon_wrapper" style="display: none">
                <div class="form-group">
                    <div style="width: 180px;height: 180px">
                        <x-admin.forms.image-upload 
                            :label="'Upload Icon'"
                            :id_input="'input_image_icon'"
                        />
                    </div>
                    <input type="hidden" name="file_name_input_image_icon" id="file_name_input_image_icon">
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
        ></textarea>
    </div>
    <div class="form-group">
        <label for="meta_descriptions" class="form-label">Keywords</label>
        <select name="keywords" id="keywords" class="form-control" multiple="multiple"></select>
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

        $('#type_icon').change(function() {
            const value = $(this).val();

            if(value === 'icon') {
                $('#icon_icon').show();
                $('#image_icon_wrapper').hide();
                $('#image_icon').val('');
            }else {
                $('#icon_icon').hide();
                $('#image_icon_wrapper').show();
            }
        })

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
            templateResult: formatIcon,  // Tampilkan ikon di dropdown
            templateSelection: formatIcon, // Tampilkan ikon saat dipilih,
            dropdownParent: $('#modalAdd')
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

        $('#buttonAddModule').click(function() {
            $.post('{{ route('admin.services.store') }}', {
                title: $('[name=title]').val(),
                content: editorInstance.getData(),
                keywords: $('[name=keywords]').val(),
                file_name: $('[name=file_name_input_image_upload]').val(),
                type_icon: $('[name=type_icon]').val(),
                url_icon: $('[name=file_name_input_image_icon]').val(),
                icon: $('[name=icon]').val(),
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