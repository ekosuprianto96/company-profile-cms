@extends('admin.layouts.main')

@section('content')
@php
    $avatar = !empty($pengguna->account->image ?? '')
        ? image_url('avatars', $pengguna->account->image)
        : asset('assets/admin/assets/images/faces/face8.jpg');
    $hasAccess = $me->mobile_admin_access ?? false;
    $isSuper = method_exists($me, 'isSuperAdmin') ? $me->isSuperAdmin() : false;
@endphp

<style>
    .profile-hero { background: linear-gradient(135deg, #1a403d 0%, #275a56 100%); border-radius: 20px; color: #fff; }
    .profile-card { border: 1px solid #eef1f4; border-radius: 18px; }
    .cred-code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 16px; letter-spacing: 1px; color: #275a56; background: #eef5f4; border-radius: 10px; padding: 10px 14px; display: inline-block; }
    .section-label { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; font-weight: 700; }
    .profile-avatar { width: 120px; height: 120px; object-fit: cover; border-radius: 20px; border: 4px solid rgba(255,255,255,.35); }
</style>

<div class="row">
    {{-- Hero --}}
    <div class="col-12 mb-4">
        <div class="profile-hero p-4 d-flex flex-wrap align-items-center" style="gap: 20px;">
            <img src="{{ $avatar }}" alt="Avatar" class="profile-avatar" id="avatarHero">
            <div class="flex-grow-1">
                <h3 class="mb-1 text-white">{{ $pengguna->account->nama_lengkap ?? $me->name }}</h3>
                <p class="mb-2" style="opacity:.85;">{{ $me->email }}</p>
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <span class="badge px-3 py-2" style="background:rgba(255,255,255,.18);">{{ ucfirst($me->role->nama ?? 'Admin') }}</span>
                    <span class="badge px-3 py-2" style="background:{{ $hasAccess ? 'rgba(46,125,50,.35)' : 'rgba(255,255,255,.15)' }};">
                        <i class="ri-smartphone-line me-1"></i>{{ $hasAccess ? 'Akses App Admin Aktif' : 'Belum ada akses app admin' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Left: avatar upload + credential --}}
    <div class="col-lg-4 mb-4">
        <div class="card profile-card shadow-sm border-0 mb-4">
            <div class="card-body text-center">
                <div class="border overflow-hidden rounded mx-auto d-inline-block" style="max-width: 220px;">
                    <img class="w-100" src="{{ $avatar }}" alt="Avatar" id="avatar">
                </div>
                <div class="position-relative mt-3">
                    <input type="file" id="input_image" name="image" accept=".jpg,.jpeg,.png,.svg,.webp" class="position-absolute" style="opacity:0;top:0;right:0;bottom:0;left:0;cursor:pointer;">
                    <button class="btn btn-outline-primary w-100"><i class="ri-file-image-line me-1"></i> Ganti Foto</button>
                </div>
            </div>
        </div>

        {{-- Credential aplikasi admin --}}
        <div class="card profile-card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3" style="gap:10px;">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:#eef5f4;">
                        <i class="ri-key-2-line" style="color:#275a56;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Credential Aplikasi Admin</div>
                        <small class="text-muted">Untuk login di aplikasi admin (mobile).</small>
                    </div>
                </div>

                @if ($hasAccess && $me->credential_key)
                    <span class="section-label">Credential Key</span>
                    <div class="d-flex align-items-center mt-1" style="gap:8px;">
                        <span class="cred-code" id="credText">{{ $me->credential_key }}</span>
                        <button type="button" class="btn btn-light btn-sm" onclick="copyCredential('{{ $me->credential_key }}')" title="Salin"><i class="ri-file-copy-line"></i></button>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size:.8em;">Login: email + password + credential key, lalu OTP ke email.</p>
                @elseif ($hasAccess)
                    <div class="alert alert-warning mb-0 py-2">Akses aktif tapi credential belum ada. {{ $isSuper ? 'Klik Generate di bawah.' : 'Hubungi superadmin.' }}</div>
                @else
                    <div class="alert alert-secondary mb-0 py-2">Akun ini belum diberi akses aplikasi admin. {{ $isSuper ? 'Sebagai superadmin, Anda bisa mengaktifkan sendiri di bawah.' : 'Hubungi superadmin untuk diberi akses.' }}</div>
                @endif

                @if ($isSuper)
                    <button type="button" class="btn btn-primary w-100 mt-3" onclick="generateOwnCredential()">
                        <i class="ri-refresh-line me-1"></i> {{ ($hasAccess && $me->credential_key) ? 'Generate Ulang Credential' : 'Aktifkan & Generate Credential' }}
                    </button>
                    <small class="text-muted d-block mt-2">Generate ulang membuat credential lama tidak berlaku &amp; mencabut sesi login app admin.</small>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: form --}}
    <div class="col-lg-8 mb-4">
        <div class="card profile-card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('admin.profile.update', ['id' => $pengguna->id]) }}" method="POST">
                    @csrf
                    <span class="section-label">Identitas</span>
                    <div class="row mt-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ $pengguna->account->nama_lengkap }}">
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $pengguna->email }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No Telpon</label>
                            <input type="text" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror" value="{{ $pengguna->account->no_telpon }}">
                            @error('no_telp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No KTP</label>
                            <input type="text" name="no_ktp" class="form-control @error('no_ktp') is-invalid @enderror" value="{{ $pengguna->account->no_ktp }}">
                            @error('no_ktp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No NIP <span class="text-muted">(No Induk Pegawai)</span></label>
                            <input type="text" name="no_nip" class="form-control @error('no_nip') is-invalid @enderror" value="{{ $pengguna->account->no_nip }}">
                            @error('no_nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tgl Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control @error('tgl_lahir') is-invalid @enderror" value="{{ $pengguna->account->tanggal_lahir }}">
                            @error('tgl_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" style="height: 90px" class="form-control @error('alamat') is-invalid @enderror">{{ $pengguna->account->alamat }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-3">
                    <span class="section-label">Keamanan</span>
                    <div class="mt-2 mb-3" style="max-width: 480px;">
                        <label class="form-label">Password</label>
                        <input type="password" autocomplete="new-password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                        <small class="text-danger d-block mt-1">Kosongkan jika tidak ingin ubah password.</small>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="text-end">
                        <button class="btn btn-success"><i class="ri-save-line me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        'use strict';
        $(document).ready(function() {
            $('#input_image').change(function(e) {
                const file = e.target.files[0];
                if (!file) return;
                const urlPreview = URL.createObjectURL(file);
                $('#avatar').attr('src', urlPreview);
                $('#avatarHero').attr('src', urlPreview);

                const data = new FormData();
                data.append('_token', '{{ csrf_token() }}');
                data.append('avatar', file);
                data.append('id', '{{ $pengguna->id }}');

                $.ajax({ url: '{{ route("admin.profile.upload-avatar") }}', type: 'POST', data, contentType: false, processData: false })
                    .done(() => $.toast({ heading: 'Berhasil', text: 'Avatar berhasil diupload.', showHideTransition: 'slide', position: 'top-right', icon: 'success' }))
                    .fail(() => $.toast({ heading: 'Gagal', text: 'Avatar gagal diupload.', showHideTransition: 'slide', position: 'top-right', icon: 'error' }));
            });
        });
    })(jQuery);

    function copyCredential(text) {
        navigator.clipboard.writeText(text).then(() =>
            $.toast({ heading: 'Disalin', text: 'Credential key disalin.', showHideTransition: 'plain', position: 'top-right', icon: 'success' }));
    }

    function generateOwnCredential() {
        Swal.fire({
            title: 'Generate credential?',
            text: 'Credential lama tidak berlaku lagi & sesi login app admin dicabut.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#275a56', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, generate'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.profile.generate-credential") }}', { _token: '{{ csrf_token() }}' })
                .done(function(response) {
                    Swal.fire({
                        title: 'Credential Baru',
                        html: '<code style="font-size:18px;letter-spacing:1px;background:#eef5f4;color:#275a56;padding:10px 16px;border-radius:10px;display:inline-block;">' + response.credential_key + '</code>',
                        icon: 'success', confirmButtonText: 'Salin & Muat Ulang', confirmButtonColor: '#275a56'
                    }).then(() => { copyCredential(response.credential_key); location.reload(); });
                })
                .fail(function(error) {
                    $.toast({ heading: 'Gagal', text: (error.responseJSON || {}).message || 'Gagal generate.', showHideTransition: 'slide', position: 'top-right', icon: 'error' });
                });
        });
    }
</script>
@endsection
