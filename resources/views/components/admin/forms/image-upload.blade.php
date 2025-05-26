@props([
    'id_input' => 'file_upload',
    'edit' => false,
    'image' => null,
    'path_file' => null,
    'label' => 'Image',
    'notice' => null
])

<style>
    #{{ $id_input }}_preview:hover > #{{ $id_input }}_action {
        top: 0%;
        transition: 0.3s ease-in-out;
    }

    #{{ $id_input }}_action {
        top: 100%;
        transition: all 0.3s ease-in-out;
    }
</style>

<label for="">{{ $label }}</label>
@if(isset($notice)) 
    <span class="text-danger d-block mb-2" style="font-size: 0.7em">{{ $notice }}</span> 
@endif
<div 
    class="border rounded position-relative overflow-hidden" 
    id="{{ $id_input }}_container" 
    style="height: {{ $edit && $image ? '100%' : '150px' }};width: {{ $edit && $image ? '250px' : '100%' }};background-color: rgb(247, 247, 247)"
>
    <input type="hidden" name="edit_{{ $id_input }}" value="{{ (int) $edit }}">
    @if($edit && $image)
        <input type="file" accept=".jpg,.jpeg,.png,.svg,.webp" class="position-absolute" id="{{ $id_input }}" style="opacity: 0;top:0;right:0;bottom:0;left:0">
        <div id="{{ $id_input }}_no_image" class="w-100 h-100 d-none justify-content-center flex-column align-items-center">
            <i class="ri-image-add-line" style="font-size: 2em"></i>
            <span class="d-block mt-2" style="font-size: 0.8em;font-weight: 400">Click atau drag file gamba disini Testing.</span>
        </div>
        <div id="{{ $id_input }}_preview" class="w-100 p-2 justify-content-center align-items-center overflow-hidden position-relative h-100">
            <img id="{{ $id_input }}_image_preview" src="{{ $image }}" width="100%">
            <div id="{{ $id_input }}_action" style="position: absolute;background-color: rgba(95, 95, 95, 0.459);left: 0;right: 0;height: 100%;">
                <div class="d-flex justify-content-center align-items-center h-100 w-100">
                    <button id="{{ $id_input }}_remove" type="button" title="Hapus Gambar" style="background-color: white;border: none;width: 50px;height: 50px;border-radius: 50%">
                        <i class="ri-delete-bin-line text-danger" style="font-size: 1.8em"></i>
                    </button>
                </div>
            </div>
            <div id="{{ $id_input }}_overlay_progress" style="position: absolute;background-color: rgb(23, 152, 238);opacity: 0.5;height: 100%;top: 0;left: 0;"></div>
        </div>
    @else
        <input type="file" accept=".jpg,.jpeg,.png,.svg,.webp" class="position-absolute" id="{{ $id_input }}" style="opacity: 0;top:0;right:0;bottom:0;left:0">
        <div id="{{ $id_input }}_no_image" class="w-100 h-100 d-flex justify-content-center flex-column align-items-center">
            <i class="ri-image-add-line" style="font-size: 2em"></i>
            <span class="d-block mt-2" style="font-size: 0.8em;font-weight: 400">Click atau drag file gamba disini.</span>
        </div>
        <div id="{{ $id_input }}_preview" class="w-100 p-2 d-none justify-content-center align-items-center overflow-hidden position-relative h-100">
            <img id="{{ $id_input }}_image_preview" width="250px">
            <div id="{{ $id_input }}_action" style="position: absolute;background-color: rgba(95, 95, 95, 0.459);left: 0;right: 0;height: 100%;">
                <div class="d-flex justify-content-center align-items-center h-100 w-100">
                    <button id="{{ $id_input }}_remove" type="button" title="Hapus Gambar" style="background-color: white;border: none;width: 50px;height: 50px;border-radius: 50%">
                        <i class="ri-delete-bin-line text-danger" style="font-size: 1.8em"></i>
                    </button>
                </div>
            </div>
            <div id="{{ $id_input }}_overlay_progress" style="position: absolute;background-color: rgb(23, 152, 238);opacity: 0.5;height: 100%;top: 0;left: 0;display: none"></div>
        </div>
    @endif
</div>

<script>
    (function($) {
        'use strict'

        $(document).ready(function() {
            $('#{{ $id_input }}').change(async function(e) {
                e.preventDefault();
                const file = e.target.files[0];

                if(onUploadImage(true)) {
                    $('#{{ $id_input }}_image_preview').attr('src', URL.createObjectURL(file));
                }

                $.uploadImage({
                    url: "{{ route('admin.upload_file') }}",
                    file: file,
                    token: '{{ csrf_token() }}',
                    onProgress: (progress) => {
                        $('#{{ $id_input }}_overlay_progress').css('width', `${progress}%`);
                    }
                }).then(response => {
                    const { data = null } = response || {};

                    const { file_name = null } = data || {};
                    if(file_name) {
                        $('#{{ $id_input }}_overlay_progress')
                        .hide()
                        .css('width', '0');

                        $('#{{ $id_input }}_action')
                        .show();

                        $('#file_name_{{ $id_input }}').val(file_name);

                        @if($edit)
                            $('[name=edit_{{ $id_input }}]').val(0);
                            $('#path_file_{{ $id_input }}').val(file_name);
                        @endif
                    }
                }).catch(e => {
                    const { message = null } = e?.responseJSON || {};
                    onUploadImage(false);
                    $.toast({
                        heading: 'Error!',
                        text: message || e?.message || e,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'error'
                    });
                });
            });

            $('#{{ $id_input }}_remove').click(function() {
                deleteImage()
                .then(() => {
                    $('#{{ $id_input }}').val(null);
                    $('#{{ $id_input }}_preview')
                    .addClass('d-none')
                    .removeClass('d-flex');

                    $('#{{ $id_input }}_no_image')
                    .removeClass('d-none')
                    .addClass('d-flex');

                    $('#{{ $id_input }}_container')
                    .css({
                        'height': '150px',
                        'width': '100%'
                    });

                    $('#file_name_{{ $id_input }}').val('');

                    @if($edit)
                        $('#path_file_{{ $id_input }}').val('');
                    @endif

                }).catch(e => {
                    const { message = null } = e?.responseJSON || {};
                    $.toast({
                        heading: 'Error!',
                        text: message || e?.message || e,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'error'
                    });
                })
            });
        });

        function onUploadImage(status = false) {
            if(status) {
                $('#{{ $id_input }}_action')
                .hide();

                $('#{{ $id_input }}_container')
                .css({
                    'height': '150px',
                    'width': '250px'
                });

                $('#{{ $id_input }}_overlay_progress')
                .show();
                
                $('#{{ $id_input }}_no_image')
                .removeClass('d-flex')
                .addClass('d-none');
                
                $('#{{ $id_input }}_preview')
                .removeClass('d-none')
                .addClass('d-flex');

                return status;
            }

            $('#{{ $id_input }}').val(null);
            $('#{{ $id_input }}_preview')
            .addClass('d-none')
            .removeClass('d-flex');

            $('#{{ $id_input }}_no_image')
            .removeClass('d-none')
            .addClass('d-flex');

            $('#{{ $id_input }}_container')
            .css({
                'height': '150px',
                'width': '100%'
            });

            $('#file_name_{{ $id_input }}').val('');

            @if($edit)
                $('#path_file_{{ $id_input }}').val('');
            @endif

            return status;
        }

        function deleteImage() {
            return new Promise((resolve, reject) => {

                @if($edit)
                    const file_name = $('[name=path_file_{{ $id_input }}]').val();
                @else
                    const file_name = $('[name=file_name_{{ $id_input }}]').val();
                @endif

                $.post('{{ route('admin.delete_file') }}', {
                    _token: '{{ csrf_token() }}',
                    file_name: file_name,
                    edit: $('[name=edit_{{ $id_input }}]').val()
                })
                .done(resolve)
                .fail(reject)
            });
        }
    })(jQuery);
</script>