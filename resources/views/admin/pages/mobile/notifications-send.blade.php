@extends('admin.layouts.main')

@section('content')
@php
    $typeLabels = [
        'promo' => ['label' => 'Promo', 'class' => 'badge-info'],
        'informasi' => ['label' => 'Informasi', 'class' => 'badge-primary'],
        'konfirmasi' => ['label' => 'Konfirmasi', 'class' => 'badge-success'],
    ];
@endphp

<style>
    .ck-editor__editable { min-height: 260px; }
</style>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Kirim Notifikasi</h4>
                    <p class="text-muted mb-0">Gunakan CKEditor untuk membuat pesan kaya teks dan menyisipkan gambar ke notifikasi mobile.</p>
                </div>
                <a href="{{ route('admin.mobile.notifications') }}" class="btn btn-light btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Kembali ke Inbox
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('admin.mobile.notifications.send') }}" method="POST" id="notificationForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Target Pengiriman</label>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="btn btn-outline-primary btn-sm">
                                <input type="radio" name="target" value="all" checked class="me-1"> Semua User
                            </label>
                            <label class="btn btn-outline-primary btn-sm">
                                <input type="radio" name="target" value="specific" class="me-1"> User Tertentu
                            </label>
                        </div>
                        @error('target')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Notifikasi</label>
                        <select name="type" class="form-control">
                            @foreach ($typeLabels as $key => $item)
                                <option value="{{ $key }}" @selected(old('type', 'informasi') === $key)>{{ $item['label'] }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Promo Spesial Akhir Pekan" value="{{ old('title') }}" required maxlength="120">
                        @error('title')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea
                            class="form-control"
                            name="message"
                            id="content"
                            style="height: 260px"
                            placeholder="Tulis isi notifikasi..."
                        >{{ old('message') }}</textarea>
                        <small class="text-muted d-block mt-2">Gambar dan format HTML akan tampil di detail notifikasi aplikasi mobile.</small>
                        @error('message')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Tujuan</label>
                        <input type="text" name="url" class="form-control" placeholder="/promo/hemat-akhir-pekan atau https://..." value="{{ old('url') }}">
                        <small class="text-muted">Opsional. Gunakan path relatif agar tidak terikat domain tertentu.</small>
                        @error('url')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 d-none" id="specificUsersWrap">
                        <label class="form-label">Pilih User</label>
                        <select name="user_ids[]" id="notification_user_ids" class="form-control" multiple="multiple" style="width:100%">
                            @foreach ($notificationUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}{{ $user->email ? ' - ' . $user->email : '' }}{{ $user->phone ? ' - ' . $user->phone : '' }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Cari dan pilih satu atau beberapa user.</small>
                        @error('user_ids')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-send-plane-2-line me-1"></i> Kirim Notifikasi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-2">Panduan</h5>
                <div class="small text-muted lh-lg">
                    Gunakan editor untuk menulis pesan yang lebih kaya, termasuk gambar, garis pemisah, dan link.
                    Konten akan ditampilkan di aplikasi mobile sebagai detail notifikasi yang bisa dibaca dengan nyaman.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        const $targetInputs = $('input[name="target"]');
        const $specificUsersWrap = $('#specificUsersWrap');
        const $specificUsers = $('#notification_user_ids');

        if ($specificUsers.length && $.fn.select2) {
            $specificUsers.select2({
                placeholder: 'Cari dan pilih user',
                width: '100%',
            });
        }

        function syncTargetVisibility() {
            const target = $('input[name="target"]:checked').val();
            if (target === 'specific') {
                $specificUsersWrap.removeClass('d-none');
            } else {
                $specificUsersWrap.addClass('d-none');
                if ($specificUsers.length && $.fn.select2) {
                    $specificUsers.val(null).trigger('change');
                }
            }
        }

        $targetInputs.on('change', syncTargetVisibility);
        syncTargetVisibility();
    });
</script>
<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
<script>
    let editorInstance = null;

    if (window.ClassicEditor && document.querySelector('#content')) {
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
            })
            .catch((error) => {
                console.error('CKEditor init failed:', error);
            });
    }

    $(function () {
        $('#notificationForm').on('submit', function () {
            if (editorInstance) {
                $('#content').val(editorInstance.getData());
            }

            const target = $('input[name="target"]:checked').val();
            const $specificUsers = $('#notification_user_ids');

            if (target !== 'specific' && $specificUsers.length && $.fn.select2) {
                $specificUsers.prop('disabled', true);
            }
        });
    });
</script>
@endsection
