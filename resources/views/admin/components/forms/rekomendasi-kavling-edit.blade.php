<style>
    #input_images:hover {
        cursor: pointer;
    }
    #input_images input[type=file]:hover {
        cursor: pointer;
    }

    .btn__delete_image {
        cursor: pointer;
    }
</style>
<form action="{{ route('admin.rekomendasi_kavling.update', $rekomendasi->id) }}" method="POST" enctype="multipart/form-data" class="">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4>Edit Rekomendasi</h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input required name="title" value="{{ $rekomendasi->title }}" type="text" class="form-control @error('title') is-invalid @enderror" id="title" placeholder="Title">                            
                                @error('title')
                                    <div class="invalid-feedback">
                                        <span class="text-danger" style="font-size: 0.8em">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="title">Images</label>
                                <div class="row" id="wrapper_images">
                                    @if(count($rekomendasi->images()) > 0)
                                        @foreach ($rekomendasi->images() as $image)
                                            <div class="col-md-3 mb-3 position-relative">
                                                <div class="w-100 border rounded overflow-hidden" style="height: 150px;">
                                                    <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                                                        <img src="{{ image_url('rekomendasi-kavling', $image) }}" class="w-100">
                                                    </div>
                                                    <input type="hidden" name="fixed_images[]" value="{{ $image }}">
                                                </div>
                                                <span class="position-absolute bg-danger btn__delete_image text-white rounded-circle" style="z-index: 100;top: 5px;right: 20px;width: 30px;height: 30px;display: flex;justify-content: center;align-items: center">
                                                    <i class="ri-delete-bin-line"></i>
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="col-md-3 mb-3 position-relative" id="input_images">
                                        <input type="file" name="images[]" multiple class="position-absolute" style="opacity: 0;top:0;right:0;bottom:0;left:0" accept=".jpg,.jpeg,.png,.svg,.webp">
                                        <div class="w-100 border rounded" style="height: 150px;background-color: rgb(232, 232, 232)">
                                            <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                                                <i class="ri-add-line" style="font-size: 2em"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @error('images')
                                <span class="text-danger" style="font-size: 0.8em">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="content" class="form-label">Content</label>
                                <textarea
                                    class="form-control" 
                                    name="content" 
                                    id="content" 
                                    style="height: 250px" 
                                    placeholder="Tulis deskripsi disini..."
                                >{{  $rekomendasi->content }}</textarea>
                            </div>

                            @error('content')
                                <div class="invalid-feedback">
                                    <span class="text-danger" style="font-size: 0.8em">{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('admin.rekomendasi_kavling.index') }}" class="btn btn-danger me-2">Kembali</a>
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
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

        $('.btn__delete_image').each(function() {
            $(this).on('click', function() {
                $(this).parent().remove();
            });
        });

        $('#input_images input[type=file]').on('change', function(e) {
            const images = e.target.files;

            for (let i = 0; i < images.length; i++) {
                const image = images[i];
                $('#wrapper_images').prepend(`
                    <div class="col-md-3 mb-3 position-relative">
                        <div class="w-100 border rounded overflow-hidden" style="height: 150px;">
                            <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                                <img src="${URL.createObjectURL(image)}" class="w-100">
                            </div>
                            <input type="hidden" name="fixed_images[]" value="${image.name}">
                        </div>
                        <span class="position-absolute bg-danger btn__delete_image text-white rounded-circle" style="z-index: 100;top: 5px;right: 20px;width: 30px;height: 30px;display: flex;justify-content: center;align-items: center">
                            <i class="ri-delete-bin-line"></i>
                        </span>
                    </div>
                `);
            }
        });
    })
</script>