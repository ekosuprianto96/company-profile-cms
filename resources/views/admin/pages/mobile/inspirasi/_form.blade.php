@php
    $isEdit = isset($inspire) && $inspire;
    $action = $action ?? '';
    $titleValue = old('title', $inspire->title ?? '');
    $categoryValue = old('category', $inspire->category ?? '');
    $summaryValue = old('summary', $inspire->summary ?? '');
    $contentValue = old('content', $inspire->content ?? '');
    $accentColorValue = old('accent_color', $inspire->accent_color ?? '#275a56');
    $readingTimeValue = old('reading_time', $inspire->reading_time ?? 3);
    $sortOrderValue = old('sort_order', $inspire->sort_order ?? 0);
    $isFeaturedValue = (bool) old('is_featured', $inspire->is_featured ?? false);
    $isPublishedValue = (bool) old('is_published', $inspire->is_published ?? true);
    $thumbnailUrl = $isEdit ? $inspire->cover_image_url : null;
@endphp

<style>
    .ck-editor__editable { min-height: 420px; }
    #button_delete_image:hover { opacity: 0.75; }
</style>

<form class="m-0 p-0" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="w-100 d-flex justify-content-end align-items-center gap-3">
                <a href="{{ route('admin.mobile.inspirasi.index') }}" class="btn btn-sm btn-danger">
                    <i class="ri-arrow-left-line"></i> Kembali
                </a>
                @if ($isEdit)
                    <a href="javascript:void(0)" id="destroy_post" data-slug="{{ $inspire->slug ?? '' }}" class="btn btn-sm btn-warning">
                        <i class="ri-delete-bin-line"></i> Hapus
                    </a>
                @endif
                <button class="btn btn-success btn-sm"><i class="ri-save-line"></i> Simpan</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="title">Title</label>
                <input required name="title" value="{{ $titleValue }}" type="text" class="form-control @error('title') is-invalid @enderror" id="title" placeholder="Title">
                @error('title') <div class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em">{{ $message }}</span></div> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="category">Category</label>
                <input required name="category" value="{{ $categoryValue }}" type="text" class="form-control @error('category') is-invalid @enderror" id="category" placeholder="Tips, Interior, Renovasi...">
                @error('category') <div class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em">{{ $message }}</span></div> @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="reading_time">Reading Time</label>
                <input required name="reading_time" value="{{ $readingTimeValue }}" type="number" min="1" max="60" class="form-control @error('reading_time') is-invalid @enderror" id="reading_time">
                @error('reading_time') <div class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em">{{ $message }}</span></div> @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="summary">Summary</label>
        <textarea class="form-control @error('summary') is-invalid @enderror" name="summary" id="summary" rows="3" placeholder="Ringkasan singkat konten inspire...">{{ $summaryValue }}</textarea>
        @error('summary') <div class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em">{{ $message }}</span></div> @enderror
    </div>

    <div class="form-group">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control @error('content') is-invalid @enderror" name="content" id="content" style="height: 250px" placeholder="Tulis konten inspire di sini...">{{ $contentValue }}</textarea>
        @error('content') <div class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em">{{ $message }}</span></div> @enderror
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="accent_color">Accent Color</label>
                        <input required type="color" name="accent_color" id="accent_color" value="{{ $accentColorValue }}" class="form-control form-control-color @error('accent_color') is-invalid @enderror" style="height: 48px;">
                        @error('accent_color') <div class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em">{{ $message }}</span></div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input name="sort_order" value="{{ $sortOrderValue }}" type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order">
                        @error('sort_order') <div class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em">{{ $message }}</span></div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="is_featured">Featured</label>
                        <select name="is_featured" id="is_featured" class="form-control">
                            <option value="0" @selected(! $isFeaturedValue)>No</option>
                            <option value="1" @selected($isFeaturedValue)>Yes</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="is_published">Status</label>
                        <select name="is_published" id="is_published" class="form-control">
                            <option value="1" @selected($isPublishedValue)>Publish</option>
                            <option value="0" @selected(! $isPublishedValue)>Unpublish</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div id="wrapper_input_image" class="mt-2 d-flex justify-content-center align-items-center rounded position-relative overflow-hidden" style="height: 250px;width: 100%;background-color: rgb(247, 247, 247)">
                <input type="file" id="input_image" accept=".jpg,.jpeg,.png,.svg,.webp" name="thumbnail" style="position: absolute;top:0;bottom:0;right:0;left:0;opacity: 0;z-index:80">
                <div id="empty_image" class="d-flex justify-content-center align-items-center flex-column" style="display: {{ $thumbnailUrl ? 'none' : 'flex' }}">
                    <i class="ri-image-add-line" style="font-size: 2em"></i>
                    <span class="d-block mt-3 mb-2" style="font-size: 0.8em;">Click atau drag gambar disini.</span>
                    <span class="d-block" style="font-size: 0.6em;">(.jpg, .jpeg, .png, .svg, .webp)</span>
                </div>
                <div class="justify-content-center w-100 align-items-center" style="position: absolute;top:0;bottom:0;right:0;left:0;z-index:100;display: {{ $thumbnailUrl ? 'flex' : 'none' }}" id="image_preview">
                    <img src="{{ $thumbnailUrl ?: '' }}" style="width: 100%" alt="">
                </div>
                <button
                    id="button_delete_image"
                    title="Hapus Gambar"
                    class="position-absolute shadow"
                    style="width: 30px;height:30px;z-index: 140;border-radius: 50%;outline: none;border: none;background-color: rgb(226, 89, 89);top:15px;right:12px;display: {{ $thumbnailUrl ? 'block' : 'none' }}"
                    type="button"
                >
                    <i class="ri-delete-bin-line text-white"></i>
                </button>
            </div>
            @error('thumbnail')
                <div class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em">{{ $message }}</span>
                </div>
            @enderror
        </div>
    </div>
</form>

<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
<script>
    var editorInstance;
    ClassicEditor
        .create(document.querySelector('#content'), {
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
            removePlugins: ['Markdown'],
        })
        .then(editor => {
            editorInstance = editor;

            editor.on('image:removed', (event, { imageRemoved }) => {
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
        $('#button_delete_image').click(function() {
            $('#wrapper_input_image').css({ 'height': '250px' });
            $('#empty_image').addClass('d-flex').fadeIn();
            $('#image_preview').fadeOut().removeClass('d-flex').find('img').removeAttr('src');
            $('#button_delete_image').fadeOut();

            setTimeout(() => {
                $('#input_image').val(null);
            }, 500);
        });

        $('#input_image').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                const urlPreview = URL.createObjectURL(file);
                $('#wrapper_input_image').css({ 'height': '100%' });
                $('#empty_image').removeClass('d-flex').fadeOut();
                $('#image_preview').fadeIn().addClass('d-flex').find('img').attr('src', urlPreview);
                $('#button_delete_image').fadeIn();
            }
        });

        $('#destroy_post').click(function() {
            Swal.fire({
                title: 'Kamu yakin?',
                text: 'Konten inspire ini akan dihapus.',
                icon: 'warning',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                showConfirmButton: true,
                showCancelButton: true,
                customClass: {
                    cancelButton: 'bg-danger',
                    confirmButton: 'bg-primary'
                }
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.post('{{ route("admin.mobile.inspirasi.destroy") }}', {
                    slug: $(this).data('slug'),
                    _token: '{{ csrf_token() }}'
                }).done(function(response) {
                    window.location.href = response.redirect_url;
                });
            });
        });
    });
</script>
