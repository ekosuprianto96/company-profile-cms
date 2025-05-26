@php
    $thumbnailPath = public_path("assets/images/{$path}/" . ($informasi->value->thumbnail ?? ''));
    $existsFile = !empty($value) && file_exists($thumbnailPath);
@endphp
<div class="form-group">
    <label for="input_image_{{ $id }}" class="form-label">{{ $label }}</label>
    <div id="wrapper_input_image_{{ $id }}" class="mt-2 mb2 d-flex justify-content-center align-items-center rounded position-relative overflow-hidden" style="height: 200px;width: 200px;background-color: rgb(247, 247, 247)">
        <input type="file" id="input_image_{{ $id }}" accept=".jpg,.jpeg,.png,.svg,.webp" name="{{ $name }}" style="position: absolute;top:0;bottom:0;right:0;left:0;opacity: 0;z-index:80">
        <div id="empty_image_{{ $id }}" style="display: {{ $existsFile ? 'none' : 'flex'  }}" class="{{ $existsFile ? '' : 'd-flex'  }} text-center justify-content-center align-items-center flex-column">
            <i class="ri-image-add-line" style="font-size: 2em"></i>
            <span class="d-block mt-3 mb-2" style="font-size: 0.8em;">Click atau drag gambar disini.</span>
            <span class="d-block" style="font-size: 0.6em;">Gambar yang diizinkan hanya (.jpg, .JPEG, .png, .svg, .webp)</span>
        </div>
        <div class="{{ $existsFile ? 'd-flex' : ''  }} justify-content-center w-100 align-items-center" style="position: absolute;top:0;bottom:0;right:0;left:0;z-index:100;display: {{ $existsFile ? 'flex' : 'none'  }}" id="image_preview_{{ $id }}">
            <img src="{{ image_url($path, $value ?? '') }}" style="width: 100%">
        </div>
        <button
            id="button_delete_image_{{ $id }}"
            title="Hapus Gambar"
            class="position-absolute shadow"
            style="width: 30px;height:30px;z-index: 140;border-radius: 50%;outline: none;border: none;background-color: rgb(226, 89, 89);top:15px;right:12px;display: {{ $existsFile ? 'block' : 'none'  }}"
            type="button"
        >
            <i class="ri-delete-bin-line text-white"></i>
        </button>
    </div>
</div>

<script>
    (function($) {
        'use strict';

        $(document).ready(function() {
            $('#button_delete_image_{{ $id }}').click(function(e) {
                // $('#wrapper_input_image_{{ $id }}')
                // .css({'height': '250px'});

                $('#empty_image_{{ $id }}')
                .addClass('d-flex')
                .fadeIn();

                $('#image_preview_{{ $id }}')
                .fadeOut()
                .removeClass('d-flex')
                .find('img')
                .removeAttr('src');

                $('#button_delete_image_{{ $id }}')
                .fadeOut();

                setTimeout(() => {
                    $('#input_image_{{ $id }}').val(null);
                }, 500);
            });

            $('#input_image_{{ $id }}').change(function(e) {
                const file = e.target.files[0];
                if(file) {
                    const urlPreview = URL.createObjectURL(file);
                    // $('#wrapper_input_image_{{ $id }}')
                    // .css({'height': '200px'});

                    $('#empty_image_{{ $id }}')
                    .removeClass('d-flex')
                    .fadeOut();

                    $('#image_preview_{{ $id }}')
                    .fadeIn()
                    .addClass('d-flex')
                    .find('img')
                    .attr('src', urlPreview);

                    $('#button_delete_image_{{ $id }}')
                    .fadeIn();
                }
            });
        })
    })(jQuery);
</script>