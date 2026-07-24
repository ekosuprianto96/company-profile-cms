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
                <h4 class="mb-1">Menu</h4>
                <p class="text-muted mb-4">Pilih area yang ingin dikelola.</p>

                <div class="row g-3">
                    @foreach ($sections as $section)
                        @php $tileColor = $tilePalette[$loop->index % count($tilePalette)]; @endphp
                        <div class="col-4 col-sm-3 col-md-2">
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
</style>
@endsection
