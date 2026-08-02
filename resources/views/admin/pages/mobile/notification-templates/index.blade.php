@extends('admin.layouts.main')

@section('content')
<style>
    .nt-hero { background: linear-gradient(135deg,#275a56,#1c433f); color:#fff; border-radius:16px; }
    .nt-group-title { font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#64748b; font-weight:700; }
    .nt-event { border:1px solid #eef1f0; border-radius:14px; transition:box-shadow .15s, border-color .15s; background:#fff; }
    .nt-event:hover { box-shadow:0 10px 26px -14px rgba(16,24,40,.18); border-color:#dfeae7; }
    .nt-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:10px; font-size:12px; font-weight:600;
        border:1px solid #e7ecea; background:#fafbfb; color:#334155; text-decoration:none; transition:.12s; }
    .nt-chip:hover { transform:translateY(-1px); border-color:#c9d6d2; color:#0f172a; }
    .nt-chip .ico { width:18px; height:18px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:11px; }
    .nt-chip.email .ico { background:#2563eb; } .nt-chip.push .ico { background:#c8915c; } .nt-chip.in_app .ico { background:#0f766e; } .nt-chip.sms .ico { background:#7c3aed; }
    .nt-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
    .nt-dot.on { background:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.15); } .nt-dot.off { background:#cbd5e1; }
    .nt-badge-custom { font-size:10px; background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:6px; font-weight:700; }
    .nt-missing { opacity:.55; font-style:italic; }
    .nt-event.d-none-search { display:none !important; }
</style>

@php
    $channelMeta = [
        'email'  => ['label' => 'Email', 'icon' => 'ri-mail-line', 'desc' => 'Dikirim ke kotak masuk email penerima. Mendukung format kaya (judul, list, dsb).'],
        'push'   => ['label' => 'Push', 'icon' => 'ri-notification-badge-line', 'desc' => 'Notifikasi yang muncul di layar HP (lewat FCM), walau aplikasi sedang tertutup. Teks singkat.'],
        'in_app' => ['label' => 'In-app', 'icon' => 'ri-smartphone-line', 'desc' => 'Muncul di daftar notifikasi DI DALAM aplikasi (lonceng). Teks singkat.'],
        'sms'    => ['label' => 'SMS', 'icon' => 'ri-message-2-line', 'desc' => 'Pesan teks singkat ke nomor HP penerima (via Zenziva). Hindari terlalu panjang.'],
    ];
    $audLabel = ['user' => 'Pengguna', 'admin' => 'Admin'];
@endphp

<div class="row">
    <div class="col-12">
        {{-- Hero --}}
        <div class="nt-hero p-4 mb-4 d-flex flex-wrap justify-content-between align-items-center" style="gap:16px;">
            <div>
                <h4 class="mb-1 text-white">Template Notifikasi</h4>
                <p class="mb-0" style="color:rgba(255,255,255,.85); max-width:640px;">
                    Semua teks <b>email</b>, <b>push</b>, dan <b>in-app</b> diambil dari template di sini. Sisipkan variabel seperti
                    <code style="color:#ffd9b0;">@{{ app_name }}</code> atau <code style="color:#ffd9b0;">@{{ recipient_name }}</code> untuk data dinamis.
                </p>
            </div>
            <a href="{{ route('admin.mobile.notification_templates.create') }}" class="btn btn-light btn-sm fw-semibold"><i class="ri-add-line me-1"></i> Buat Template</a>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        {{-- Search + tombol info channel --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">
            <div class="position-relative" style="max-width:360px; flex:1;">
                <i class="ri-search-line position-absolute" style="left:12px; top:9px; color:#94a3b8;"></i>
                <input type="text" id="ntSearch" class="form-control ps-5" placeholder="Cari event notifikasi…">
            </div>
            <div class="d-flex align-items-center" style="gap:14px;">
                <span style="font-size:12px; color:#64748b;"><span class="nt-dot on"></span> Aktif &nbsp; <span class="nt-dot off"></span> Nonaktif</span>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#channelInfoModal">
                    <i class="ri-information-line me-1"></i> Info Channel
                </button>
            </div>
        </div>

        @foreach ($groups as $groupName => $events)
            <div class="mb-2 nt-group" data-group="{{ \Illuminate\Support\Str::lower($groupName) }}">
                <div class="nt-group-title mb-2">{{ $groupName }}</div>

                <div class="d-flex flex-column" style="gap:10px;">
                    @foreach ($events as $key => $event)
                        @php $rows = $templates[$key] ?? collect(); @endphp
                        <div class="nt-event p-3 nt-event-item" data-search="{{ \Illuminate\Support\Str::lower($event['label'] . ' ' . $key . ' ' . $groupName) }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:12px;">
                                <div style="min-width:200px;">
                                    <div class="fw-bold text-dark">{{ $event['label'] }}</div>
                                    <div class="text-muted" style="font-size:11px;">{{ $key }}</div>
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                                    @foreach (['email','push','in_app','sms'] as $ch)
                                        @foreach (['user','admin'] as $aud)
                                            @php $tpl = $rows->first(fn ($t) => $t->channel === $ch && $t->audience === $aud); @endphp
                                            @if ($tpl)
                                                <a href="{{ route('admin.mobile.notification_templates.edit', $tpl->id) }}" class="nt-chip {{ $ch }}" title="{{ $tpl->subject }}">
                                                    <span class="ico"><i class="{{ $channelMeta[$ch]['icon'] }}"></i></span>
                                                    <span>{{ $channelMeta[$ch]['label'] }}</span>
                                                    <span class="text-muted">· {{ $audLabel[$aud] }}</span>
                                                    <span class="nt-dot {{ $tpl->is_active ? 'on' : 'off' }}"></span>
                                                    @unless ($tpl->is_default)<span class="nt-badge-custom">custom</span>@endunless
                                                </a>
                                            @endif
                                        @endforeach
                                    @endforeach
                                    @if ($rows->isEmpty())
                                        <span class="text-muted nt-missing" style="font-size:12px;">Belum ada template</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div id="ntEmpty" class="text-center text-muted py-5 d-none">Tidak ada event yang cocok.</div>
    </div>
</div>

{{-- Modal: penjelasan jenis channel --}}
<div class="modal fade" id="channelInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-information-line me-1"></i> Jenis Channel Notifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column" style="gap:14px;">
                    @foreach ($channelMeta as $ch => $meta)
                        <div class="d-flex align-items-start" style="gap:12px;">
                            <span class="nt-chip {{ $ch }}" style="pointer-events:none; min-width:96px;"><span class="ico"><i class="{{ $meta['icon'] }}"></i></span> {{ $meta['label'] }}</span>
                            <div style="font-size:13px; color:#475569; line-height:1.5;">{{ $meta['desc'] }}</div>
                        </div>
                    @endforeach
                </div>
                <hr>
                <div style="font-size:12px; color:#64748b;">
                    <span class="nt-dot on"></span> <b>Aktif</b> = template dipakai untuk mengirim.
                    &nbsp; <span class="nt-dot off"></span> <b>Nonaktif</b> = template disimpan tapi tidak dipakai.
                    &nbsp; <span class="nt-badge-custom">custom</span> = template buatan admin (bukan bawaan).
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin-scripts')
<script>
(function () {
    const input = document.getElementById('ntSearch');
    const items = Array.from(document.querySelectorAll('.nt-event-item'));
    const groups = Array.from(document.querySelectorAll('.nt-group'));
    const empty = document.getElementById('ntEmpty');

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let shown = 0;
        items.forEach((el) => {
            const match = !q || (el.dataset.search || '').includes(q);
            el.classList.toggle('d-none-search', !match);
            if (match) shown++;
        });
        // Sembunyikan grup yang semua itemnya tersembunyi.
        groups.forEach((g) => {
            const visible = g.querySelectorAll('.nt-event-item:not(.d-none-search)').length;
            g.style.display = visible ? '' : 'none';
        });
        empty.classList.toggle('d-none', shown !== 0);
    });
})();
</script>
@endpush
