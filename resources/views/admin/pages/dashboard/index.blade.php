@extends('admin.layouts.main')

@section('content')
<style>
    .stat-card { border:none; border-radius:16px; overflow:hidden; transition:transform .15s ease, box-shadow .15s ease; }
    .stat-card:hover { transform:translateY(-3px); box-shadow:0 12px 26px rgba(31,45,61,.10); }
    .stat-ico { width:56px; height:56px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:26px; }
    .stat-num { font-size:28px; font-weight:800; line-height:1; }
    .stat-label { font-size:13px; color:#8a94a6; font-weight:600; }
    .dash-hero { border-radius:16px; background:linear-gradient(120deg,#275a56,#317f77); color:#fff; }
    .section-title { font-weight:700; font-size:17px; }
</style>

{{-- Hero --}}
<div class="dash-hero p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap" style="gap:1rem">
    <div>
        <h3 class="mb-1" style="font-weight:800;color:#fff">Halo, {{ auth()->user()->name ?? 'Admin' }} 👋</h3>
        <p class="mb-0" style="opacity:.85">Selamat datang kembali di panel {{ config('settings.value.app_name') }}.</p>
    </div>
    <div class="text-end d-none d-md-block">
        <small class="d-block" style="opacity:.8">{{ now()->translatedFormat('l, d F Y') }}</small>
        <span style="font-weight:700;font-size:20px" id="dash-clock">{{ now()->format('H:i') }}</span>
    </div>
</div>

{{-- Statistik ringkas --}}
<div class="row">
    <div class="col-xl-3 col-md-6 grid-margin stretch-card">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center" style="gap:1rem">
                <div class="stat-ico" style="background:#eef0ff;color:#6571ff"><i class="ri-file-text-line"></i></div>
                <div><div class="stat-num">{{ number_format($countPost) }}</div><div class="stat-label">Total Post</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 grid-margin stretch-card">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center" style="gap:1rem">
                <div class="stat-ico" style="background:#e6f9f1;color:#0fb78d"><i class="ri-group-line"></i></div>
                <div><div class="stat-num">{{ number_format($userCount) }}</div><div class="stat-label">Total User</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 grid-margin stretch-card">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center" style="gap:1rem">
                <div class="stat-ico" style="background:#e7f6ff;color:#05c3fb"><i class="ri-eye-line"></i></div>
                <div><div class="stat-num">{{ number_format($visitorCount) }}</div><div class="stat-label">Total Pengunjung</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 grid-margin stretch-card">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center" style="gap:1rem">
                <div class="stat-ico" style="background:#fff1e9;color:#ff7a45"><i class="ri-mail-unread-line"></i></div>
                <div><div class="stat-num">{{ number_format($unreadMessages->count()) }}</div><div class="stat-label">Pesan Belum Dibaca</div></div>
            </div>
        </div>
    </div>
</div>

@if(isset($serverMetrics) && optional(auth()->user())->isSuperAdmin())
{{-- Monitoring kondisi server --}}
<div class="d-flex justify-content-between align-items-center mb-2 mt-2">
    <span class="section-title"><i class="ri-server-line me-1"></i> Kondisi Server</span>
    <a href="{{ route('admin.system.jobs') }}" class="btn btn-sm btn-outline-secondary">Detail Sistem</a>
</div>
@include('admin.pages.system.partials.server-metrics', ['metrics' => $serverMetrics, 'live' => true])
@endif

{{-- Chart + pesan --}}
<div class="row mt-2">
    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">
                <h6 class="section-title mb-3">Analitik Pengunjung</h6>
                {!! $chart->container() !!}
            </div>
        </div>
    </div>
    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">
                <h6 class="section-title mb-3">Pesan Belum Dibaca</h6>
                @if($unreadMessages->count() > 0)
                    @foreach($unreadMessages as $value)
                        <a href="{{ route('admin.email.show', $value->id) }}" style="text-decoration:none"
                           class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div>
                                <p class="mb-1 fw-bold text-success"><i class="ri-mail-unread-line me-2"></i>{{ $value->name }}</p>
                                <small class="text-muted">{{ $value->email }}</small>
                            </div>
                            <small class="text-muted">{{ $value->created_at->diffForHumans() }}</small>
                        </a>
                    @endforeach
                @else
                    <div class="text-center text-muted py-5">
                        <i class="ri-mail-check-line" style="font-size:38px;opacity:.4"></i>
                        <p class="mb-0 mt-2">Tidak ada pesan baru.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Aktivitas pengunjung --}}
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">
                <h6 class="section-title mb-3">Aktifitas Pengunjung</h6>
                <div class="table-responsive">
                    <table class="table table-striped" id="tableVisitor">
                        <thead>
                            <tr>
                                <th>IP</th><th>Tanggal</th><th>Waktu</th><th>URL</th><th>Halaman</th><th>Browser</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ $chart->cdn() }}"></script>
{{ $chart->script() }}
<script>
    $(function () {
        'use strict';
        $('#tableVisitor').DataTable({
            processing: true,
            pageLength: 25,
            serverSide: true,
            paginate: true,
            ajax: { method: 'get', url: '{{ route("admin.visitor.data") }}' },
            columns: [
                { data: 'ip_address', name: 'ip_address', search: true },
                { data: 'tanggal', name: 'tanggal', search: true },
                { data: 'waktu', name: 'waktu', search: true },
                { data: 'url', name: 'url' },
                { data: 'page', name: 'page', search: true },
                { data: 'user_agent', name: 'user_agent', search: true }
            ]
        });

        // Jam realtime di hero.
        setInterval(function () {
            var d = new Date();
            var el = document.getElementById('dash-clock');
            if (el) el.textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
        }, 30000);
    });
</script>
@endsection
