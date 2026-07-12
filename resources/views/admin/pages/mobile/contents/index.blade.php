@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Konten Aplikasi Mobile</h4>
                    <p class="text-muted mb-0">Isi halaman <strong>Tentang Aplikasi</strong> dan <strong>Syarat &amp; Ketentuan</strong> yang tampil di aplikasi mobile.</p>
                </div>
                <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Overview
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="col-md-12">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif

    <div class="col-md-12">
        <form action="{{ route('admin.mobile.contents.update') }}" method="POST">
            @csrf
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3"><i class="ri-information-line me-1"></i> Tentang Aplikasi</h5>
                    <textarea name="about_body" id="about_body">{!! optional($about)->body !!}</textarea>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3"><i class="ri-file-list-3-line me-1"></i> Syarat &amp; Ketentuan</h5>
                    <textarea name="terms_body" id="terms_body">{!! optional($terms)->body !!}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Simpan Konten</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
<script>
    ['about_body', 'terms_body'].forEach(function(id) {
        ClassicEditor
            .create(document.querySelector('#' + id), {
                extraPlugins: [
                    function(editor) {
                        createCustomUploadAdapterPlugin({
                            url: '{{ route('admin.ckeditor.upload') }}',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })(editor);
                        new ImageRemovePlugin(editor);
                    }
                ],
                removePlugins: ['Markdown'],
            })
            .catch(function(error) { console.error(error); });
    });
</script>
@endsection
