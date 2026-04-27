@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #112d4e 0%, #1f6f8b 100%); color: #fff;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                    <div>
                        <div class="mb-2 text-uppercase" style="letter-spacing: 1px; font-size: 12px; opacity: .8;">Mobile App Backoffice</div>
                        <h3 class="mb-2 text-white">Module Mobile</h3>
                        <p class="mb-0" style="max-width: 720px;">
                            Area ini disiapkan sebagai pusat kendali aplikasi mobile order jasa kontraktor. Tujuannya supaya tim tidak perlu membangun dashboard baru dan semua operasional tetap terpusat di admin web yang sudah ada.
                        </p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('admin.mobile.users') }}" class="btn btn-light btn-sm">
                            <i class="ri-user-settings-line me-1"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($stats as $stat)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted mb-2">{{ $stat['label'] }}</div>
                            <div style="font-size: 30px; font-weight: 700;">{{ $stat['value'] }}</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-{{ $stat['tone'] }} text-white" style="width: 54px; height: 54px; font-size: 24px;">
                            <i class="{{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="mb-1">Cakupan Modul</h4>
                        <p class="text-muted mb-0">Menu awal untuk area mobile app management.</p>
                    </div>
                </div>

                <div class="row">
                    @foreach($sections as $section)
                        <div class="col-md-6 mb-3">
                            <a href="{{ $section['route'] }}" class="text-decoration-none">
                                <div class="border rounded p-3 h-100 hover-shadow" style="transition: .2s ease;">
                                    <div class="d-flex align-items-start" style="gap: 12px;">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="{{ $section['icon'] }}" style="font-size: 20px; color: #1f6f8b;"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 text-dark">{{ $section['title'] }}</h5>
                                            <p class="mb-0 text-muted">{{ $section['description'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h4 class="mb-3">Prioritas Implementasi</h4>
                <div class="d-flex flex-column" style="gap: 12px;">
                    <div class="p-3 rounded bg-light">
                        <div class="fw-bold mb-1">1. Struktur Data Mobile</div>
                        <div class="text-muted">Pisahkan entitas mobile dari konten company profile di bagian yang memang berbeda tujuan.</div>
                    </div>
                    <div class="p-3 rounded bg-light">
                        <div class="fw-bold mb-1">2. Monitoring User & OTP</div>
                        <div class="text-muted">Pantau verifikasi akun, token aktif, dan histori OTP sebelum flow order mobile diperluas.</div>
                    </div>
                    <div class="p-3 rounded bg-light">
                        <div class="fw-bold mb-1">3. Panel Konfigurasi Home</div>
                        <div class="text-muted">Supaya tim bisa mengatur banner, urutan section, layanan unggulan, dan tema tanpa deploy ulang aplikasi.</div>
                    </div>
                    <div class="p-3 rounded bg-light">
                        <div class="fw-bold mb-1">4. Notifikasi & Chat</div>
                        <div class="text-muted">Dikerjakan sesudah user, order, dan layanan sudah punya flow yang jelas.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
