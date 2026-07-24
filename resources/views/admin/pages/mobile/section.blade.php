@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">{{ $title }}</h4>
                    <p class="text-muted mb-0" style="max-width: 760px;">{{ $description }}</p>
                </div>
                <a href="{{ route('admin.mobile.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 64px; height: 64px;">
                    <i class="ri-tools-line text-muted" style="font-size: 28px;"></i>
                </div>
                <h5 class="mb-1">Belum tersedia</h5>
                <p class="text-muted mb-0">Area ini belum bisa dikelola dari dashboard. Hubungi tim teknis bila kamu membutuhkannya.</p>
            </div>
        </div>
    </div>
</div>
@endsection
