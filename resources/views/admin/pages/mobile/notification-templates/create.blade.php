@extends('admin.layouts.main')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Buat Template Baru</h4>
                <a href="{{ route('admin.mobile.notification_templates') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="card border-0 shadow-sm"><div class="card-body">
            <form action="{{ route('admin.mobile.notification_templates.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">Jenis Notifikasi (Event)</label>
                    <select name="event_key" class="form-select" required>
                        <option value="">— Pilih event —</option>
                        @foreach ($events as $key => $event)
                            <option value="{{ $key }}" {{ old('event_key') === $key ? 'selected' : '' }}>{{ $event['group'] ?? 'Lainnya' }} — {{ $event['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="form-label">Channel</label>
                        <select name="channel" class="form-select" required>
                            <option value="email" {{ old('channel') === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="push" {{ old('channel') === 'push' ? 'selected' : '' }}>Push (FCM)</option>
                            <option value="in_app" {{ old('channel') === 'in_app' ? 'selected' : '' }}>In-app</option>
                            <option value="sms" {{ old('channel') === 'sms' ? 'selected' : '' }}>SMS (Zenziva)</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="form-label">Penerima</label>
                        <select name="audience" class="form-select" required>
                            <option value="user" {{ old('audience') === 'user' ? 'selected' : '' }}>Pengguna</option>
                            <option value="admin" {{ old('audience') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Nama Template</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="mis. Pengajuan disetujui — versi singkat" required>
                </div>

                <p class="text-muted small">Template dibuat sebagai <b>custom</b> (nonaktif). Isi diawali dari default event bila tersedia, lalu bisa disunting & diaktifkan di halaman edit.</p>

                <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Buat &amp; Sunting</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
