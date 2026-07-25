@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                <div>
                    <h4 class="card-title mb-1">Tambah Layanan Mobile</h4>
                    <p class="text-muted mb-0">Lengkapi data layanan untuk aplikasi mobile.</p>
                </div>
                <a href="{{ route('admin.mobile.services') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar Layanan</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @include('admin.components.forms.mobile-service-create')
            </div>
        </div>
    </div>
</div>
@endsection
