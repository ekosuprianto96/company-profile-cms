@extends('admin.layouts.main')

@php
    $statusOptions = [
        1 => 'Aktif',
        0 => 'Nonaktif',
    ];

    $formatRupiah = static fn ($amount) => $amount === null ? '-' : 'Rp' . number_format((int) $amount, 0, ',', '.');
@endphp

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Master Event Project</h4>
                    <p class="text-muted mb-0">Data ini dipakai mobile app untuk flow Wedding Organizer, Exhibition, dan Gathering.</p>
                </div>
                <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Overview
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="mb-3">Jenis Project</h5>
                <form method="POST" action="{{ route('admin.mobile.event_projects.types.store') }}" class="row g-2 mb-4">
                    @csrf
                    @include('admin.pages.mobile.event-projects.partials.basic-fields')
                    <div class="col-12"><button class="btn btn-primary btn-sm">Tambah Jenis Project</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Nama</th><th>Sort</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($types as $type)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.types.update', $type->id) }}" class="row g-2">
                                            @csrf
                                            <div class="col-12"><input name="name" class="form-control form-control-sm" value="{{ $type->name }}"></div>
                                            <div class="col-12"><input name="description" class="form-control form-control-sm" value="{{ $type->description }}" placeholder="Deskripsi"></div>
                                    </td>
                                    <td><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $type->sort_order }}"></td>
                                    <td>
                                        <select name="is_active" class="form-control form-control-sm">
                                            @foreach($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected((bool)$type->is_active === (bool)$value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-nowrap">
                                            <button class="btn btn-success btn-xs">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.types.destroy', $type->id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-danger btn-xs" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="mb-3">Kebutuhan Project</h5>
                <form method="POST" action="{{ route('admin.mobile.event_projects.needs.store') }}" class="row g-2 mb-4">
                    @csrf
                    <div class="col-12">
                        <select name="mobile_event_project_type_id" class="form-control form-control-sm" required>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('admin.pages.mobile.event-projects.partials.basic-fields')
                    <div class="col-12"><button class="btn btn-primary btn-sm">Tambah Kebutuhan</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Project</th><th>Nama</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($needs as $need)
                                <tr>
                                    <td>{{ $need->projectType?->name ?? '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.needs.update', $need->id) }}" class="row g-2">
                                            @csrf
                                            <input type="hidden" name="mobile_event_project_type_id" value="{{ $need->mobile_event_project_type_id }}">
                                            <input name="name" class="form-control form-control-sm" value="{{ $need->name }}">
                                            <input name="description" class="form-control form-control-sm" value="{{ $need->description }}" placeholder="Deskripsi">
                                            <input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $need->sort_order }}">
                                    </td>
                                    <td>
                                        <select name="is_active" class="form-control form-control-sm">
                                            @foreach($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected((bool)$need->is_active === (bool)$value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-nowrap">
                                            <button class="btn btn-success btn-xs">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.needs.destroy', $need->id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-danger btn-xs" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="mb-3">Paket Event</h5>
                <form method="POST" action="{{ route('admin.mobile.event_projects.packages.store') }}" class="row g-2 mb-4">
                    @csrf
                    <div class="col-md-5">
                        <select name="mobile_event_project_need_id" class="form-control form-control-sm" required>
                            @foreach($needs as $need)
                                <option value="{{ $need->id }}">{{ $need->projectType?->name }} - {{ $need->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7"><input name="name" class="form-control form-control-sm" placeholder="Nama paket" required></div>
                    <div class="col-md-7"><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Deskripsi"></textarea></div>
                    <div class="col-md-2"><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="0"></div>
                    <div class="col-md-3">
                        <select name="is_active" class="form-control form-control-sm">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-12"><button class="btn btn-primary btn-sm">Tambah Paket</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Kebutuhan</th><th>Paket</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($packages as $package)
                                <tr>
                                    <td>{{ $package->projectNeed?->projectType?->name }} - {{ $package->projectNeed?->name }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.packages.update', $package->id) }}" class="row g-2">
                                            @csrf
                                            <input type="hidden" name="mobile_event_project_need_id" value="{{ $package->mobile_event_project_need_id }}">
                                            <input name="name" class="form-control form-control-sm" value="{{ $package->name }}">
                                            <textarea name="description" class="form-control form-control-sm" rows="2">{{ $package->description }}</textarea>
                                            <input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $package->sort_order }}">
                                    </td>
                                    <td>
                                        <select name="is_active" class="form-control form-control-sm">
                                            @foreach($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected((bool)$package->is_active === (bool)$value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-nowrap">
                                            <button class="btn btn-success btn-xs">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.packages.destroy', $package->id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-danger btn-xs" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="mb-3">Anggaran Event</h5>
                <form method="POST" action="{{ route('admin.mobile.event_projects.budgets.store') }}" class="row g-2 mb-4">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control form-control-sm" placeholder="Contoh: 100 Juta - 300 Juta" required></div>
                    <div class="col-md-4"><input name="min_amount" type="number" min="0" class="form-control form-control-sm" placeholder="Min"></div>
                    <div class="col-md-4"><input name="max_amount" type="number" min="0" class="form-control form-control-sm" placeholder="Max"></div>
                    <div class="col-md-2"><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="0"></div>
                    <div class="col-md-2"><select name="is_active" class="form-control form-control-sm"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                    <div class="col-12"><button class="btn btn-primary btn-sm">Tambah Anggaran</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Nama</th><th>Range</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($budgets as $budget)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.budgets.update', $budget->id) }}" class="row g-2">
                                            @csrf
                                            <input name="name" class="form-control form-control-sm" value="{{ $budget->name }}">
                                            <input name="min_amount" type="number" min="0" class="form-control form-control-sm" value="{{ $budget->min_amount }}">
                                            <input name="max_amount" type="number" min="0" class="form-control form-control-sm" value="{{ $budget->max_amount }}">
                                            <input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $budget->sort_order }}">
                                    </td>
                                    <td>{{ $formatRupiah($budget->min_amount) }} - {{ $formatRupiah($budget->max_amount) }}</td>
                                    <td>
                                        <select name="is_active" class="form-control form-control-sm">
                                            @foreach($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected((bool)$budget->is_active === (bool)$value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-nowrap">
                                            <button class="btn btn-success btn-xs">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.mobile.event_projects.budgets.destroy', $budget->id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-danger btn-xs" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
