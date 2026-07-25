@extends('admin.layouts.main')

@section('content')
@php
    // Warna tile dibuat berulang agar tiap menu mudah dibedakan sekilas.
    $tilePalette = ['#1f6f8b', '#2e7d32', '#c8915c', '#6a4c93', '#0277bd', '#d84315', '#00897b', '#455a64'];
@endphp

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Mobile App</h4>
                    <p class="text-muted mb-0">Pusat kendali aplikasi mobile: pengguna, layanan, produk, pesanan, dan konten.</p>
                </div>
                <a href="{{ route('admin.mobile.users') }}" class="btn btn-primary btn-sm">
                    <i class="ri-user-settings-line me-1"></i> Kelola Pengguna
                </a>
            </div>
        </div>
    </div>

    @foreach ($stats as $stat)
        <div class="col-6 col-md-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted mb-1">{{ $stat['label'] }}</div>
                        <div style="font-size: 26px; font-weight: 700;">{{ $stat['value'] }}</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-{{ $stat['tone'] }} text-white"
                         style="width: 48px; height: 48px; font-size: 22px;">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3" style="gap:12px;">
                    <div>
                        <h4 class="mb-1">Menu</h4>
                        <p class="text-muted mb-0">Pilih area yang ingin dikelola.</p>
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                        {{-- Cari menu --}}
                        <div class="position-relative" style="min-width:220px;">
                            <i class="ri-search-line position-absolute text-muted" style="left:12px; top:50%; transform:translateY(-50%);"></i>
                            <input type="text" class="form-control form-control-sm" id="menuSearch"
                                   style="padding-left:32px;" placeholder="Cari menu…" autocomplete="off">
                        </div>
                        {{-- Toggle tampilan grid / list --}}
                        <div class="btn-group btn-group-sm" role="group" id="menuViewToggle">
                            <button type="button" class="btn btn-outline-secondary active" data-view="grid" title="Tampilan grid">
                                <i class="ri-grid-fill"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-view="list" title="Tampilan daftar">
                                <i class="ri-list-check"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Grid --}}
                <div class="row g-3" id="menuGrid">
                    @foreach ($sections as $section)
                        @php $tileColor = $tilePalette[$loop->index % count($tilePalette)]; @endphp
                        <div class="col-4 col-sm-3 col-md-2 menu-item" data-menu-name="{{ \Illuminate\Support\Str::lower($section['title'] . ' ' . ($section['description'] ?? '')) }}">
                            <a href="{{ $section['route'] }}"
                               class="mobile-tile"
                               title="{{ $section['description'] ?? $section['title'] }}">
                                <span class="mobile-tile__icon" style="background: {{ $tileColor }};">
                                    <i class="{{ $section['icon'] }}"></i>
                                </span>
                                <span class="mobile-tile__label">{{ $section['title'] }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>

                {{-- List --}}
                <div class="mobile-menu-list d-none" id="menuList">
                    @foreach ($sections as $section)
                        @php $tileColor = $tilePalette[$loop->index % count($tilePalette)]; @endphp
                        <a href="{{ $section['route'] }}" class="mobile-row menu-item" data-menu-name="{{ \Illuminate\Support\Str::lower($section['title'] . ' ' . ($section['description'] ?? '')) }}">
                            <span class="mobile-row__icon" style="background: {{ $tileColor }};">
                                <i class="{{ $section['icon'] }}"></i>
                            </span>
                            <span class="mobile-row__text">
                                <span class="mobile-row__title">{{ $section['title'] }}</span>
                                @if (!empty($section['description']))
                                    <span class="mobile-row__desc">{{ $section['description'] }}</span>
                                @endif
                            </span>
                            <i class="ri-arrow-right-s-line mobile-row__chev"></i>
                        </a>
                    @endforeach
                </div>

                <div id="menuEmpty" class="text-center text-muted py-4 d-none">
                    <i class="ri-search-line" style="font-size:22px;"></i>
                    <div class="mt-1" style="font-size:.9rem;">Menu tidak ditemukan.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .mobile-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 14px 6px;
        border-radius: 14px;
        text-decoration: none;
        transition: background-color .15s ease, transform .15s ease;
    }
    .mobile-tile:hover {
        background-color: rgba(31, 111, 139, .07);
        transform: translateY(-2px);
    }
    .mobile-tile__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 62px;
        height: 62px;
        border-radius: 16px;
        color: #fff;
        font-size: 28px;
        box-shadow: 0 6px 16px rgba(16, 24, 40, .14);
    }
    .mobile-tile__label {
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.25;
        text-align: center;
        color: #3f4254;
        /* Judul panjang dipotong agar tinggi tiap tile tetap rata. */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ---- Tampilan daftar ---- */
    .mobile-menu-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 10px;
    }
    .mobile-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid #eef0f3;
        border-radius: 12px;
        text-decoration: none;
        transition: background-color .15s ease, border-color .15s ease;
    }
    .mobile-row:hover { background-color: rgba(31, 111, 139, .06); border-color: #dbe3e2; }
    .mobile-row__icon {
        flex: none;
        display: flex; align-items: center; justify-content: center;
        width: 42px; height: 42px; border-radius: 11px;
        color: #fff; font-size: 20px;
    }
    .mobile-row__text { min-width: 0; flex: 1; display: flex; flex-direction: column; }
    .mobile-row__title { font-size: 13.5px; font-weight: 600; color: #3f4254; }
    .mobile-row__desc {
        font-size: 11.5px; color: #8a94a6; margin-top: 1px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .mobile-row__chev { flex: none; color: #c2c9d3; font-size: 18px; }
</style>

@push('admin-scripts')
<script>
    (function () {
        const KEY = 'maninjau-admin-menu-view';
        const grid = document.getElementById('menuGrid');
        const list = document.getElementById('menuList');
        const toggle = document.getElementById('menuViewToggle');
        if (!grid || !list || !toggle) return;

        function apply(view) {
            const isList = view === 'list';
            grid.classList.toggle('d-none', isList);
            list.classList.toggle('d-none', !isList);
            toggle.querySelectorAll('button').forEach((btn) => {
                btn.classList.toggle('active', btn.dataset.view === view);
            });
        }

        apply(localStorage.getItem(KEY) === 'list' ? 'list' : 'grid');

        toggle.addEventListener('click', function (event) {
            const btn = event.target.closest('button[data-view]');
            if (!btn) return;
            localStorage.setItem(KEY, btn.dataset.view);
            apply(btn.dataset.view);
        });

        // Cari menu (filter judul + deskripsi di grid & list sekaligus)
        const search = document.getElementById('menuSearch');
        const empty = document.getElementById('menuEmpty');
        if (search) {
            search.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                let visible = 0;
                document.querySelectorAll('.menu-item').forEach((item) => {
                    const match = !q || (item.dataset.menuName || '').includes(q);
                    item.classList.toggle('d-none', !match);
                    if (match) visible++;
                });
                // .menu-item ada 2x (grid+list) → bagi 2 untuk hitung real
                if (empty) empty.classList.toggle('d-none', visible > 0);
            });
        }
    })();
</script>
@endpush
@endsection
