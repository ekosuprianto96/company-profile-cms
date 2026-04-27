@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                    <div>
                        <div class="text-uppercase text-muted mb-2" style="letter-spacing: 1px; font-size: 12px;">Mobile App Module</div>
                        <h3 class="mb-2">{{ $title }}</h3>
                        <p class="text-muted mb-0" style="max-width: 760px;">{{ $description }}</p>
                    </div>
                    <a href="{{ route('admin.mobile.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Kembali ke Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-3">Rekomendasi Tahap Berikutnya</h4>
                <div class="d-flex flex-column" style="gap: 12px;">
                    @foreach($recommendations as $index => $item)
                        <div class="border rounded p-3">
                            <div class="fw-bold mb-1">Langkah {{ $index + 1 }}</div>
                            <div class="text-muted">{{ $item }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-3">Navigasi Modul</h4>
                <div class="list-group">
                    @foreach($sections as $section)
                        <a href="{{ $section['route'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="{{ $section['icon'] }} me-2"></i>{{ $section['title'] }}</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
