@extends('admin.layouts.main')

@php
    $isEmail = $template->channel === 'email';
    $channelInfo = [
        'email'  => ['label' => 'Email', 'icon' => 'ri-mail-line', 'desc' => 'Dikirim ke kotak masuk email penerima. Mendukung format kaya (judul, list, tebal, dsb).'],
        'push'   => ['label' => 'Push', 'icon' => 'ri-notification-badge-line', 'desc' => 'Notifikasi yang muncul di layar HP (via FCM) walau aplikasi tertutup. Sebaiknya singkat; format otomatis jadi teks polos saat dikirim.'],
        'in_app' => ['label' => 'In-app', 'icon' => 'ri-smartphone-line', 'desc' => 'Muncul di daftar notifikasi (lonceng) di dalam aplikasi. Sebaiknya singkat; format otomatis jadi teks polos.'],
        'sms'    => ['label' => 'SMS', 'icon' => 'ri-message-2-line', 'desc' => 'Pesan teks singkat ke nomor HP penerima (via Zenziva). Hindari terlalu panjang; format otomatis jadi teks polos.'],
    ];
    $ci = $channelInfo[$template->channel] ?? ['label' => $template->channel, 'icon' => 'ri-information-line', 'desc' => ''];
@endphp

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
                <div>
                    <h4 class="card-title mb-1">{{ $eventLabel }}</h4>
                    <div>
                        <span class="badge bg-primary text-uppercase">{{ $template->channel }}</span>
                        <span class="badge bg-light text-dark">{{ $template->audience }}</span>
                        @if ($template->is_default)<span class="badge bg-secondary">Default</span>@else<span class="badge bg-info">Custom</span>@endif
                    </div>
                </div>
                <a href="{{ route('admin.mobile.notification_templates') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar Template</a>
            </div>
        </div>
    </div>

    <div class="col-12 mb-3">
        <div class="alert d-flex align-items-start mb-0" style="gap:10px; background:#f1f6f5; border:1px solid #e2ecea; color:#334155;">
            <i class="{{ $ci['icon'] }}" style="font-size:18px; color:#275a56;"></i>
            <div style="font-size:12.5px;"><b>Channel {{ $ci['label'] }}.</b> {{ $ci['desc'] }}</div>
        </div>
    </div>

    @if (session('success'))
        <div class="col-12"><div class="alert alert-success">{{ session('success') }}</div></div>
    @endif

    <div class="col-lg-8">
        <form action="{{ route('admin.mobile.notification_templates.update', $template->id) }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm mb-3"><div class="card-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Template</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">{{ $isEmail ? 'Subjek Email' : 'Judul Notifikasi' }}</label>
                    <input type="text" name="subject" id="field-subject" class="form-control js-var-target" value="{{ old('subject', $template->subject) }}" placeholder="mis. Pengajuan @{{ transaction_code }} disetujui">
                    @error('subject')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Isi Pesan</label>
                    <textarea name="body" id="field-body" class="form-control js-var-target" rows="{{ $isEmail ? 10 : 5 }}">{{ old('body', $template->body) }}</textarea>
                    @error('body')<div class="text-danger small">{{ $message }}</div>@enderror
                    @unless ($isEmail)
                        <small class="text-muted">Push &amp; in-app: format teks otomatis diubah jadi teks polos saat dikirim.</small>
                    @endunless
                </div>

                <div class="mb-3 d-flex align-items-center" style="gap:10px;">
                    <div class="form-check form-switch m-0" style="padding-left:2.75em; min-height:1.5rem;">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1"
                               style="width:2.75em; height:1.4em; margin-left:-2.75em; flex:0 0 auto;"
                               {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                    </div>
                    <label class="form-check-label mb-0" for="is_active" style="cursor:pointer;">Aktif (template ini dipakai untuk mengirim notifikasi)</label>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Simpan</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnPreview"><i class="ri-eye-line me-1"></i> Preview</button>
                </div>
            </div></div>
        </form>

        <div class="d-flex flex-wrap gap-2">
            @if ($template->is_default && $hasDefault)
                <form action="{{ route('admin.mobile.notification_templates.reset', $template->id) }}" method="POST" onsubmit="return confirm('Kembalikan teks ke default bawaan?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm"><i class="ri-refresh-line me-1"></i> Reset ke Default</button>
                </form>
            @endif
            <form action="{{ route('admin.mobile.notification_templates.duplicate', $template->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-info btn-sm"><i class="ri-file-copy-line me-1"></i> Duplikat jadi Custom</button>
            </form>
            @unless ($template->is_default)
                <form action="{{ route('admin.mobile.notification_templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Hapus template custom ini?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ri-delete-bin-line me-1"></i> Hapus</button>
                </form>
            @endunless
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <h6 class="mb-2">Variabel Tersedia</h6>
            <p class="text-muted small mb-2">Klik untuk menyisipkan ke field terakhir yang aktif.</p>
            <div class="d-flex flex-wrap gap-1">
                @foreach ($variables as $name => $meta)
                    <button type="button" class="btn btn-sm btn-light border js-var-chip" data-var="{{ $name }}" title="{{ $meta['label'] ?? $name }} · contoh: {{ $meta['sample'] ?? '' }}">
                        <code>@php echo e('{{ ' . $name . ' }}'); @endphp</code>
                    </button>
                @endforeach
            </div>
        </div></div>

        <div class="card border-0 shadow-sm"><div class="card-body">
            <h6 class="mb-2">Preview</h6>
            <div class="mb-2">
                <div class="text-muted small">Subjek / Judul</div>
                <div id="preview-subject" class="fw-bold">—</div>
            </div>
            <div>
                <div class="text-muted small">Isi</div>
                <div id="preview-body" class="border rounded p-2 bg-light" style="min-height:80px; white-space:pre-wrap;">Klik "Preview" untuk melihat hasil dengan data contoh.</div>
            </div>
        </div></div>
    </div>
</div>
@endsection

@push('ckeditor')
<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script>
(function () {
    const CHANNEL = @json($template->channel);
    const EVENT_KEY = @json($template->event_key);
    const PREVIEW_URL = @json(route('admin.mobile.notification_templates.preview'));
    const CSRF = @json(csrf_token());

    let editor = null;
    let bodyFocused = false; // apakah CKEditor (body) yang terakhir difokus
    const subjectEl = document.getElementById('field-subject');

    subjectEl.addEventListener('focus', () => { bodyFocused = false; });

    function insertToInput(el, text) {
        const start = el.selectionStart ?? el.value.length;
        const end = el.selectionEnd ?? el.value.length;
        el.value = el.value.slice(0, start) + text + el.value.slice(end);
        el.focus();
        el.selectionStart = el.selectionEnd = start + text.length;
    }

    // Token variabel via char code agar tidak diparse Blade.
    const OPEN = String.fromCharCode(123, 123);
    const CLOSE = String.fromCharCode(125, 125);

    document.querySelectorAll('.js-var-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const token = OPEN + ' ' + chip.dataset.var + ' ' + CLOSE;
            if (bodyFocused && editor) {
                editor.model.change((writer) => editor.model.insertContent(writer.createText(token)));
                editor.editing.view.focus();
            } else {
                insertToInput(subjectEl, token);
            }
        });
    });

    // CKEditor untuk SEMUA channel (email/push/in-app). Push & in-app otomatis
    // dijadikan teks polos saat dikirim (server strip HTML).
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor.create(document.getElementById('field-body'))
            .then((ed) => {
                editor = ed;
                ed.editing.view.document.on('focus', () => { bodyFocused = true; });
            })
            .catch(() => {});
    }

    function currentBody() {
        return editor ? editor.getData() : document.getElementById('field-body').value;
    }

    document.getElementById('btnPreview').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        fetch(PREVIEW_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ event_key: EVENT_KEY, channel: CHANNEL, subject: subjectEl.value, body: currentBody() }),
        })
        .then((r) => r.json())
        .then((data) => {
            document.getElementById('preview-subject').textContent = data.subject || '—';
            const bodyEl = document.getElementById('preview-body');
            if (data.plain) { bodyEl.textContent = data.body || '—'; bodyEl.style.whiteSpace = 'pre-wrap'; }
            else { bodyEl.innerHTML = data.body || '—'; bodyEl.style.whiteSpace = 'normal'; }
        })
        .catch(() => { document.getElementById('preview-body').textContent = 'Gagal memuat preview.'; })
        .finally(() => { btn.disabled = false; });
    });
})();
</script>
@endpush
