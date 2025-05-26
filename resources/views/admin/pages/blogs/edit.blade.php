@extends('admin.layouts.main')
@section('content')
    <style>
        .ck-editor__editable {min-height: 400px;}
        #button_delete_image:hover {
            opacity: 0.6;
        }

        #button_delete_image:active {
            opacity: 0.3;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form class="m-0 p-0" method="POST" action="{{ route('admin.blogs.update', $blog->slug ?? '-') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="w-100 d-flex justify-content-end align-items-center gap-3">
                                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-danger btn-sm"><i class="ri-arrow-left-line"></i> Kembali</a>
                                    <a href="javascript:void(0)" id="destroy_post" data-slug="{{ $blog->slug ?? '' }}" class="btn btn-sm btn-warning"><i class="ri-delete-bin-line"></i> Hapus</a>
                                    <button class="btn btn-success btn-sm"><i class="ri-save-line"></i> Simpan</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input required name="title" value="{{ $blog->title ?? old('title') }}" type="text" class="form-control @error('title') is-invalid @enderror" id="title" placeholder="Title">
                                    @error('title')
                                        <div class="invalid-fedback">
                                            <span class="text-danger" style="font-size: 0.8em">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kategori_id" class="mb-0 d-flex gap-2">
                                        <span>Kategori</span>
                                        <span class="d-block text-danger mb-2" style="font-size: 0.8em">*Silahkan tambah kategori <a style="text-decoration: underline;color: blue" href="{{ route('admin.blogs.kategori.index') }}">disini</a>, jika belum tersedia.</span>
                                    </label>
                                    <select required name="kategori_id" id="kategori_id" class="form-control @error('kategori_id') is-invalid @enderror">
                                      <option value="">-- Pilih Kategori --</option>
                                      @foreach (App\Models\KategoriBlog::where('an', 1)->get() as $kategori)
                                            <option
                                                @selected($blog->kategori_id == $kategori->id)
                                                value="{{ trim($kategori->id) }}"
                                            >
                                                {{ trim($kategori->name) }}
                                            </option>
                                      @endforeach
                                    </select>
                                    @error('kategori_id')
                                        <div class="invalid-fedback">
                                            <span class="text-danger" style="font-size: 0.8em">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content" class="form-label">Content</label>
                            <textarea 
                                class="form-control @error('content') is-invalid @enderror" 
                                name="content" 
                                id="content" 
                                style="height: 250px" 
                                placeholder="Tulis content blog disini..."
                            >{{ $blog->content ?? old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-fedback">
                                    <span class="text-danger" style="font-size: 0.8em">{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="meta_descriptions" class="form-label">Keywords</label>
                                    <select required name="keywords[]" id="keywords" class="form-control" multiple="multiple">
                                        @foreach(explode(',', ($blog->keywords ?? '')) as $key => $value)
                                            <option selected value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="an" id="status" class="form-control">
                                      <option @selected($blog->an == 1) value="1">Publish</option>
                                      <option @selected($blog->an == 0) value="0">Unpublish</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Author</label>
                                    <input value="{{ $blog->createdBy->account->nama_lengkap ?? '-' }}" readonly type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div id="wrapper_input_image" class="mt-2 d-flex justify-content-center align-items-center rounded position-relative overflow-hidden" style="height: 250px;width: 100%;background-color: rgb(247, 247, 247)">
                                    <input type="file" id="input_image" accept=".jpg,.jpeg,.png,.svg,.webp" name="thumbnail" style="position: absolute;top:0;bottom:0;right:0;left:0;opacity: 0;z-index:80">
                                    <div id="empty_image" style="display: {{ ($blog->thumbnail ?? null) ? 'none' : 'flex'  }}" class="{{ ($blog->thumbnail ?? null) ? '' : 'd-flex'  }} justify-content-center align-items-center flex-column">
                                        <i class="ri-image-add-line" style="font-size: 2em"></i>
                                        <span class="d-block mt-3 mb-2" style="font-size: 0.8em;">Click atau drag gambar disini.</span>
                                        <span class="d-block" style="font-size: 0.6em;">Gambar yang diizinkan hanya (.jpg, .JPEG, .png, .svg, .webp)</span>
                                    </div>
                                    <div class="{{ ($blog->thumbnail ?? null) ? 'd-flex' : ''  }} justify-content-center w-100 align-items-center" style="position: absolute;top:0;bottom:0;right:0;left:0;z-index:100;display: {{ ($blog->thumbnail ?? null) ? 'flex' : 'none'  }}" id="image_preview">
                                        <img src="{{ image_url('blogs', $blog->thumbnail ?? '') }}" style="width: 100%">
                                    </div>
                                    <button
                                        id="button_delete_image"
                                        title="Hapus Gambar"
                                        class="position-absolute shadow"
                                        style="width: 30px;height:30px;z-index: 140;border-radius: 50%;outline: none;border: none;background-color: rgb(226, 89, 89);top:15px;right:12px;display: {{ ($blog->thumbnail ?? null) ? 'block' : 'none'  }}"
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
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/ckeditor5-classic-free-full-feature@35.4.1/build/ckeditor.min.js"></script> --}}
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
            $('#kategori_id').select2({
                width: '100%'
            });

            $('#keywords').select2({
                width: '100%',
                placeholder: 'Keywords',
                tags: true
            });

            $('#button_delete_image').click(function(e) {
                $('#wrapper_input_image')
                .css({'height': '250px'});

                $('#empty_image')
                .addClass('d-flex')
                .fadeIn();

                $('#image_preview')
                .fadeOut()
                .removeClass('d-flex')
                .find('img')
                .removeAttr('src');

                $('#button_delete_image')
                .fadeOut();

                setTimeout(() => {
                    $('#input_image').val(null);
                }, 500);
            });

            $('#input_image').change(function(e) {
                const file = e.target.files[0];
                if(file) {
                    const urlPreview = URL.createObjectURL(file);
                    $('#wrapper_input_image')
                    .css({'height': '100%'});

                    $('#empty_image')
                    .removeClass('d-flex')
                    .fadeOut();

                    $('#image_preview')
                    .fadeIn()
                    .addClass('d-flex')
                    .find('img')
                    .attr('src', urlPreview);

                    $('#button_delete_image')
                    .fadeIn();
                }
            });

            $('#destroy_post').click(function() {
                const slug = $(this).data('slug');
                Swal.fire({
                    title: 'Kamu Yakin?',
                    text: 'Apakah kamu yakin ingin menghapus postingan ini?',
                    icon: 'warning',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Tidak',
                    showConfirmButton: true,
                    showCancelButton: true,
                    customClass: {
                        cancelButton: 'bg-danger',
                        confirmButton: 'bg-primary'
                    }
                }).then(result => {
                    if(result.isConfirmed) {
                        destoryPost(slug)
                        .then(response => {
                            const { message, redirect_url = null } = response;
                            $.toast({
                                heading: 'Sukses',
                                text: message,
                                showHideTransition: 'plain',
                                position: 'top-right',
                                icon: 'success'
                            });

                            if(redirect_url) {
                                window.location.href = redirect_url;
                            }

                        }).catch(err => {
                            const { status, message = null } = err?.responseJSON || {};
                            if(message) {
                                $.toast({
                                    heading: 'Warning',
                                    text: message || err?.message || err,
                                    showHideTransition: 'slide',
                                    position: 'top-right',
                                    icon: 'warning'
                                });
                            }
                        })
                        return;
                    }
                })
            })
        });

        function destoryPost(slug) {
            return new Promise((resolve, reject) => {
                $.post('{{ route("admin.blogs.destroy") }}', {
                    slug,
                    _token: '{{ csrf_token() }}'
                })
                .done(resolve)
                .fail(reject)
            })
        }
    </script>
@endsection