@extends('admin.layouts.main')

@section('content')
<style>
    .ed-hero { background: linear-gradient(135deg,#1f4d78,#14324f); color:#fff; border-radius:16px; }
    .ed-card { border:1px solid #eef1f0; border-radius:14px; background:#fff; overflow:hidden; transition:box-shadow .15s, border-color .15s; }
    .ed-card:hover { box-shadow:0 12px 30px -16px rgba(16,24,40,.22); border-color:#dbe4ee; }
    .ed-thumb { height:150px; background:#f1f5f9; border-bottom:1px solid #eef1f0; overflow:hidden; position:relative; }
    .ed-thumb iframe { border:0; width:200%; height:300px; transform:scale(.5); transform-origin:top left; pointer-events:none; }
    .ed-group-title { font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#64748b; font-weight:700; }
    .ed-badge { font-size:10px; padding:1px 7px; border-radius:6px; font-weight:700; }
    .ed-badge.on { background:#dcfce7; color:#166534; } .ed-badge.off { background:#f1f5f9; color:#64748b; }
    .ed-badge.def { background:#e0e7ff; color:#3730a3; }
</style>

<div class="row"><div class="col-12">
    <div class="ed-hero p-4 mb-4 d-flex flex-wrap justify-content-between align-items-center" style="gap:16px;">
        <div>
            <h4 class="mb-1 text-white"><i class="ri-mail-star-line me-1"></i> Email Builder</h4>
            <p class="mb-0" style="color:rgba(255,255,255,.85); max-width:640px;">
                Rancang desain email secara visual (seret &amp; lepas). Desain di sini bisa dipilih oleh
                <b>Template Notifikasi</b> pada channel <b>email</b>. Gunakan blok <code style="color:#ffe0a3;">@{{ body }}</code>
                sebagai tempat isi pesan disisipkan otomatis.
            </p>
        </div>
        <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createDesignModal">
            <i class="ri-add-line me-1"></i> Buat Desain
        </button>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @forelse ($grouped as $category => $designs)
        <div class="mb-2 ed-group-title">{{ $category }}</div>
        <div class="row g-3 mb-3">
            @foreach ($designs as $design)
                <div class="col-md-4 col-sm-6">
                    <div class="ed-card h-100 d-flex flex-column">
                        <div class="ed-thumb">
                            <iframe src="{{ route('admin.mobile.email_designs.preview', $design->id) }}" scrolling="no" loading="lazy"></iframe>
                        </div>
                        <div class="p-3 d-flex flex-column" style="gap:8px; flex:1;">
                            <div class="d-flex justify-content-between align-items-start" style="gap:8px;">
                                <div class="fw-bold text-dark" style="line-height:1.2;">{{ $design->name }}</div>
                                <div class="d-flex flex-column align-items-end" style="gap:3px;">
                                    <span class="ed-badge {{ $design->is_active ? 'on' : 'off' }}">{{ $design->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    @if ($design->is_default)<span class="ed-badge def">Bawaan</span>@endif
                                    @if (($design->notification_templates_count ?? 0) > 0)<span class="ed-badge" style="background:#e0f2fe;color:#0369a1;">Dipakai {{ $design->notification_templates_count }}</span>@endif
                                    @unless ($design->hasBodySlot())<span class="ed-badge" style="background:#fef3c7;color:#b45309;" title="Tanpa blok Isi Pesan — isi notifikasi tak muncul">⚠ Tanpa Isi Pesan</span>@endunless
                                </div>
                            </div>
                            @if ($design->description)<div class="text-muted" style="font-size:11.5px;">{{ $design->description }}</div>@endif
                            <div class="d-flex flex-wrap gap-1 mt-auto pt-1">
                                <a href="{{ route('admin.mobile.email_designs.builder', $design->id) }}" class="btn btn-primary btn-sm"><i class="ri-edit-2-line me-1"></i> Builder</a>
                                <a href="{{ route('admin.mobile.email_designs.preview', $design->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="ri-eye-line"></i></a>
                                <form action="{{ route('admin.mobile.email_designs.duplicate', $design->id) }}" method="POST" class="d-inline">@csrf
                                    <button class="btn btn-outline-info btn-sm" title="Duplikat"><i class="ri-file-copy-line"></i></button>
                                </form>
                                <form action="{{ route('admin.mobile.email_designs.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm(@js(($design->notification_templates_count ?? 0) > 0 ? 'Desain ini dipakai '.$design->notification_templates_count.' template notifikasi. Menghapusnya membuat template itu kembali ke layout bawaan. Lanjut hapus?' : 'Hapus desain ini?'))">@csrf
                                    <input type="hidden" name="id" value="{{ $design->id }}">
                                    <button class="btn btn-outline-danger btn-sm" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-center text-muted py-5">Belum ada desain email. Klik <b>Buat Desain</b> untuk memulai.</div>
    @endforelse
</div></div>

{{-- Modal buat desain --}}
<div class="modal fade" id="createDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.mobile.email_designs.store') }}" method="POST" class="modal-content border-0">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-add-line me-1"></i> Buat Desain Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Desain</label>
                    <input type="text" name="name" class="form-control" required placeholder="mis. Selamat Datang Pengguna">
                </div>
                <div class="mb-1">
                    <label class="form-label">Kategori <span class="text-muted">(opsional)</span></label>
                    <input type="text" name="category" class="form-control" placeholder="mis. Transaksi / Marketing" list="edCategories">
                    <datalist id="edCategories">
                        @foreach ($grouped->keys() as $cat)<option value="{{ $cat }}">@endforeach
                    </datalist>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ri-magic-line me-1"></i> Buat &amp; Buka Builder</button>
            </div>
        </form>
    </div>
</div>
@endsection
