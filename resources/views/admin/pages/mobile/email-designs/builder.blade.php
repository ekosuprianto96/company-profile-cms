@extends('admin.layouts.main')

@php
    // Palette blok EMAIL-SAFE dari sumber tunggal App\Support\EmailBlocks (dipakai juga
    // oleh blank template & seeder default → konsisten). Token {{ }} literal, aman via @json.
    $emailBlocks = \App\Support\EmailBlocks::palette();

    // Variabel dinamis dari katalog (disisipkan sebagai teks inline).
    foreach ($variables as $key => $meta) {
        $emailBlocks[] = [
            'id' => 'ebv-' . $key,
            'label' => $meta['label'] ?? $key,
            'category' => 'Variabel',
            'icon' => 'ri-price-tag-3-line',
            'content' => ['type' => 'text', 'content' => '{{ ' . $key . ' }}', 'style' => ['display' => 'inline-block']],
        ];
    }
@endphp

@section('content')
<link href="{{ asset('assets/vendor/grapesjs/grapes.min.css') }}" rel="stylesheet">
<style>
    .eb-bar { background:#14324f; color:#fff; border-radius:12px 12px 0 0; }
    .eb-bar .form-control { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); color:#fff; }
    .eb-bar .form-control::placeholder { color:rgba(255,255,255,.6); }
    #gjs { border:1px solid #d9e0e7; border-top:0; border-radius:0 0 12px 12px; overflow:hidden; }
    .gjs-block { width:auto; min-height:auto; padding:9px 6px; font-size:11px; }
    .gjs-block__media i { font-size:20px; }
    .gjs-block-category .gjs-title { font-weight:700; }
    .eb-help code { background:#eef5f4; color:#14324f; padding:1px 5px; border-radius:4px; font-size:11px; }
</style>

<div class="eb-bar p-3" style="border-radius:12px 12px 0 0;">
    <div class="d-flex flex-wrap align-items-center" style="gap:10px;">
        <a href="{{ route('admin.mobile.email_designs') }}" class="btn btn-sm btn-outline-light"><i class="ri-arrow-left-line"></i></a>
        <input type="text" id="ebName" class="form-control form-control-sm" style="max-width:220px;" value="{{ $design->name }}" placeholder="Nama desain">
        <input type="text" id="ebSubject" class="form-control form-control-sm" style="max-width:280px; flex:1;" value="{{ $design->subject }}" placeholder="Subjek email default (opsional)">
        <span class="text-white-50 small ms-auto" id="ebStatus"></span>
        <a href="{{ route('admin.mobile.email_designs.preview', $design->id) }}" target="_blank" id="ebPreview" class="btn btn-sm btn-outline-light"><i class="ri-eye-line me-1"></i> Pratinjau</a>
        <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#ebTestModal"><i class="ri-send-plane-line me-1"></i> Kirim Uji</button>
        <button id="ebSave" class="btn btn-sm btn-light fw-semibold text-dark"><i class="ri-save-line me-1"></i> Simpan</button>
    </div>
    <div class="d-flex flex-wrap align-items-center mt-2" style="gap:10px;">
        <input type="text" id="ebPreheader" class="form-control form-control-sm" style="max-width:340px;" value="{{ $design->preheader }}" placeholder="Preheader — teks pratinjau di daftar inbox (opsional)">
        @if (!empty($events))
        <div class="d-flex align-items-center" style="gap:6px;">
            <span class="text-white-50 small">Pratinjau sebagai:</span>
            <select id="ebPreviewEvent" class="form-select form-select-sm" style="max-width:230px;">
                <option value="">— Contoh umum —</option>
                @foreach ($events as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
            </select>
        </div>
        @endif
        <button type="button" class="btn btn-sm btn-outline-light ms-auto" id="ebSaveBlock" title="Simpan elemen terpilih jadi blok"><i class="ri-add-box-line me-1"></i> Simpan sbg Blok</button>
    </div>
</div>

{{-- Skema warna: pilih swatch → warnai elemen terpilih --}}
<div class="d-flex flex-wrap align-items-center px-3 py-2" style="gap:10px; background:#f1f5f9; border:1px solid #d9e0e7; border-top:0;">
    <span class="fw-semibold" style="font-size:12px; color:#475569;"><i class="ri-palette-line me-1"></i> Skema Warna</span>
    <select id="ebScheme" class="form-select form-select-sm" style="max-width:190px;"></select>
    <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-secondary active" id="ebTargetBg" data-mode="bg">Latar</button>
        <button type="button" class="btn btn-outline-secondary" id="ebTargetText" data-mode="text">Teks</button>
    </div>
    <div id="ebSwatches" class="d-flex align-items-center" style="gap:6px;"></div>
    <button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-bs-toggle="modal" data-bs-target="#ebSchemeModal"><i class="ri-add-line"></i> Buat Skema</button>
</div>

<div id="gjs" style="height:calc(100vh - 250px); min-height:460px;"></div>

{{-- Modal buat skema warna custom --}}
<div class="modal fade" id="ebSchemeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-palette-line me-1"></i> Buat Skema Warna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Skema</label>
                    <input type="text" id="ebSchemeName" class="form-control" placeholder="mis. Brand Perusahaan Saya">
                </div>
                <label class="form-label">Warna</label>
                <div class="d-flex flex-wrap gap-2" id="ebSchemeColors">
                    <input type="color" class="form-control form-control-color" value="#275a56">
                    <input type="color" class="form-control form-control-color" value="#1c433f">
                    <input type="color" class="form-control form-control-color" value="#c8915c">
                    <input type="color" class="form-control form-control-color" value="#334155">
                    <input type="color" class="form-control form-control-color" value="#f8fafc">
                    <input type="color" class="form-control form-control-color" value="#ffffff">
                </div>
                <div class="text-danger small mt-2 d-none" id="ebSchemeErr"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="ebSchemeSave"><i class="ri-save-line me-1"></i> Simpan Skema</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal kirim email uji --}}
<div class="modal fade" id="ebTestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-send-plane-line me-1"></i> Kirim Email Uji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Kirim email ini ke alamat Anda untuk melihat tampilan asli di Gmail/Outlook. <b>Simpan desain dulu</b> agar perubahan terbaru ikut terkirim.</p>
                <div class="mb-3">
                    <label class="form-label">Email tujuan</label>
                    <input type="email" id="ebTestEmail" class="form-control" value="{{ auth()->user()->email ?? '' }}" placeholder="nama@email.com">
                </div>
                @if (!empty($events))
                <div class="mb-1">
                    <label class="form-label">Sebagai notifikasi (opsional)</label>
                    <select id="ebTestEvent" class="form-select">
                        <option value="">— Contoh umum —</option>
                        @foreach ($events as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                @endif
                <div class="small mt-2 d-none" id="ebTestMsg"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="ebTestSend"><i class="ri-send-plane-line me-1"></i> Kirim</button>
            </div>
        </div>
    </div>
</div>

<div class="eb-help text-muted small mt-2">
    <i class="ri-information-line"></i> Seret blok dari panel kanan ke kanvas untuk menyusun email. Kategori <b>Struktur</b> = kerangka,
    <b>Konten</b> = isi, <b>Variabel</b> = data dinamis. Blok <code>Isi Pesan</code> (<code>@{{ body }}</code>) adalah tempat teks dari template notifikasi disisipkan otomatis.
    Klik dua kali teks untuk menyuntingnya.
</div>

<script src="{{ asset('assets/vendor/grapesjs/grapes.min.js') }}"></script>
<script src="{{ asset('assets/vendor/grapesjs/grapesjs-preset-newsletter.min.js') }}"></script>
<script>
(function () {
    const SAVE_URL = @json(route('admin.mobile.email_designs.save', $design->id));
    const UPLOAD_URL = @json(route('admin.mobile.email_designs.upload'));
    const SCHEME_URL = @json(route('admin.mobile.email_designs.schemes.store'));
    const TEST_URL = @json(route('admin.mobile.email_designs.test', $design->id));
    const BLOCK_URL = @json(route('admin.mobile.email_designs.blocks.store'));
    const BLOCK_DESTROY_URL = @json(route('admin.mobile.email_designs.blocks.destroy'));
    const PREVIEW_BASE = @json(route('admin.mobile.email_designs.preview', $design->id));
    const CSRF = @json(csrf_token());
    const HTML = @json($design->html ?? '');
    const PROJECT = @json($design->design_json ? json_decode($design->design_json) : null);
    const BLOCKS = @json($emailBlocks);
    const SCHEMES = @json($colorSchemes);
    const CUSTOM_BLOCKS = @json($customBlocks);

    const editor = grapesjs.init({
        container: '#gjs',
        height: '100%',
        fromElement: false,
        storageManager: false,
        plugins: ['grapesjs-preset-newsletter'],
        pluginsOpts: {
            'grapesjs-preset-newsletter': {
                modalTitleImport: 'Impor HTML email',
                inlineCss: true,
            },
        },
        assetManager: { uploadName: 'files', upload: UPLOAD_URL, headers: { 'X-CSRF-TOKEN': CSRF }, autoAdd: true },
    });

    // Bersihkan blok bawaan preset (berbahasa Inggris) → ganti dengan palette ramah admin.
    const bm = editor.BlockManager;
    bm.getAll().reset();
    BLOCKS.forEach((b) => bm.add(b.id, {
        label: b.label,
        category: b.category,
        media: '<i class="' + b.icon + '"></i>',
        content: b.content,
    }));

    // Blok custom buatan admin ("Blok Saya").
    function addCustomBlock(cb) {
        bm.add('ebc-' + cb.id, {
            label: cb.name,
            category: 'Blok Saya',
            media: '<i class="ri-bookmark-line"></i>',
            content: cb.html,
        });
    }
    (Array.isArray(CUSTOM_BLOCKS) ? CUSTOM_BLOCKS : []).forEach(addCustomBlock);

    // Muat konten awal.
    editor.on('load', () => {
        try {
            if (PROJECT) editor.loadProjectData(PROJECT);
            else if (HTML) editor.setComponents(HTML);
        } catch (e) { if (HTML) editor.setComponents(HTML); }
    });

    const statusEl = document.getElementById('ebStatus');

    // ---- Skema warna ----
    const schemes = Array.isArray(SCHEMES) ? SCHEMES.slice() : [];
    let targetMode = 'bg'; // 'bg' | 'text'
    const schemeSel = document.getElementById('ebScheme');
    const swatchWrap = document.getElementById('ebSwatches');

    function fillSchemeSelect() {
        schemeSel.innerHTML = '';
        schemes.forEach((s) => {
            const opt = document.createElement('option');
            opt.value = String(s.id);
            opt.textContent = s.name + (s.is_default ? '' : ' (custom)');
            schemeSel.appendChild(opt);
        });
    }
    function renderSwatches() {
        const s = schemes.find((x) => String(x.id) === schemeSel.value) || schemes[0];
        swatchWrap.innerHTML = '';
        ((s && s.colors) || []).forEach((hex) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.title = hex + ' → klik untuk terapkan';
            b.style.cssText = 'width:26px;height:26px;border-radius:6px;border:1px solid rgba(15,23,42,.2);cursor:pointer;padding:0;background:' + hex;
            b.addEventListener('click', () => applyColor(hex));
            swatchWrap.appendChild(b);
        });
    }
    function applyColor(hex) {
        const sel = editor.getSelected();
        if (!sel) { statusEl.textContent = 'Pilih elemen di kanvas dulu'; setTimeout(() => statusEl.textContent = '', 2500); return; }
        sel.addStyle({ [targetMode === 'text' ? 'color' : 'background-color']: hex });
        statusEl.textContent = 'Warna diterapkan'; setTimeout(() => statusEl.textContent = '', 1500);
    }
    schemeSel.addEventListener('change', renderSwatches);
    document.getElementById('ebTargetBg').addEventListener('click', function () {
        targetMode = 'bg'; this.classList.add('active'); document.getElementById('ebTargetText').classList.remove('active');
    });
    document.getElementById('ebTargetText').addEventListener('click', function () {
        targetMode = 'text'; this.classList.add('active'); document.getElementById('ebTargetBg').classList.remove('active');
    });
    fillSchemeSelect();
    renderSwatches();

    // Buat skema custom.
    document.getElementById('ebSchemeSave').addEventListener('click', function () {
        const name = document.getElementById('ebSchemeName').value.trim();
        const colors = Array.from(document.querySelectorAll('#ebSchemeColors input')).map((i) => i.value);
        const err = document.getElementById('ebSchemeErr');
        if (!name) { err.textContent = 'Nama skema wajib diisi.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none');
        fetch(SCHEME_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name: name, colors: colors }),
        })
        .then((r) => r.json())
        .then((d) => {
            if (!d.ok) throw new Error();
            schemes.push(d.scheme);
            fillSchemeSelect();
            schemeSel.value = String(d.scheme.id);
            renderSwatches();
            bootstrap.Modal.getInstance(document.getElementById('ebSchemeModal'))?.hide();
        })
        .catch(() => { err.textContent = 'Gagal menyimpan skema.'; err.classList.remove('d-none'); });
    });

    let dirty = false;
    function currentHtml() {
        try { return editor.runCommand('gjs-get-inlined-html') || editor.getHtml(); }
        catch (e) { return '<style>' + editor.getCss() + '</style>' + editor.getHtml(); }
    }
    function save(silent) {
        const html = currentHtml();
        if (!silent) statusEl.textContent = 'Menyimpan…';
        return fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                html: html,
                design_json: JSON.stringify(editor.getProjectData()),
                name: document.getElementById('ebName').value,
                subject: document.getElementById('ebSubject').value,
                preheader: document.getElementById('ebPreheader').value,
            }),
        })
        .then((r) => r.json())
        .then((d) => {
            dirty = false;
            statusEl.textContent = 'Tersimpan ✓'; setTimeout(() => { if (!dirty) statusEl.textContent = ''; }, 2500);
            if (d && d.has_body === false) {
                statusEl.textContent = '⚠ Tanpa blok "Isi Pesan"';
                alert('Perhatian: desain ini belum punya blok "Isi Pesan" (@{{ body }}).\nIsi notifikasi (mis. kode OTP) TIDAK akan muncul di email. Seret blok "Isi Pesan" dari kategori Konten.');
            }
        })
        .catch(() => { statusEl.textContent = 'Gagal menyimpan'; });
    }

    document.getElementById('ebSave').addEventListener('click', () => save(false));
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); save(false); }
    });

    // Tandai perubahan + autosave + peringatan keluar tanpa simpan.
    editor.on('update', () => { dirty = true; });
    let autoT = null;
    editor.on('update', () => {
        if (autoT) clearTimeout(autoT);
        autoT = setTimeout(() => { if (dirty) save(true); }, 8000);
    });
    window.addEventListener('beforeunload', (e) => {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    // Pratinjau per-event: perbarui tautan Pratinjau saat pilih event.
    const prevEventSel = document.getElementById('ebPreviewEvent');
    if (prevEventSel) {
        prevEventSel.addEventListener('change', function () {
            const link = document.getElementById('ebPreview');
            link.href = this.value ? PREVIEW_BASE + '?event=' + encodeURIComponent(this.value) : PREVIEW_BASE;
        });
    }

    // Kirim email uji.
    document.getElementById('ebTestSend').addEventListener('click', function () {
        const email = document.getElementById('ebTestEmail').value.trim();
        const eventEl = document.getElementById('ebTestEvent');
        const msg = document.getElementById('ebTestMsg');
        const btn = this;
        msg.classList.add('d-none');
        if (!email) { msg.className = 'small mt-2 text-danger'; msg.textContent = 'Isi email tujuan.'; msg.classList.remove('d-none'); return; }
        btn.disabled = true; msg.className = 'small mt-2 text-muted'; msg.textContent = 'Mengirim…'; msg.classList.remove('d-none');
        // Simpan dulu agar versi terbaru terkirim, lalu kirim uji.
        save(true).then(() => fetch(TEST_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ email: email, event: eventEl ? eventEl.value : null }),
        }))
        .then((r) => r.json())
        .then((d) => { msg.className = 'small mt-2 ' + (d.ok ? 'text-success' : 'text-danger'); msg.textContent = d.message; })
        .catch(() => { msg.className = 'small mt-2 text-danger'; msg.textContent = 'Gagal mengirim email uji.'; })
        .finally(() => { btn.disabled = false; });
    });

    // Simpan elemen terpilih jadi blok custom ("Blok Saya").
    document.getElementById('ebSaveBlock').addEventListener('click', function () {
        const sel = editor.getSelected();
        if (!sel) { statusEl.textContent = 'Pilih elemen dulu'; setTimeout(() => statusEl.textContent = '', 2500); return; }
        const name = prompt('Nama blok:');
        if (!name) return;
        const html = sel.toHTML();
        fetch(BLOCK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name: name, html: html }),
        })
        .then((r) => r.json())
        .then((d) => { if (d.ok) { addCustomBlock(d.block); statusEl.textContent = 'Blok disimpan ✓'; setTimeout(() => statusEl.textContent = '', 2500); } })
        .catch(() => { statusEl.textContent = 'Gagal simpan blok'; });
    });
})();
</script>
@endsection
