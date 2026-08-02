@php
    $hasAccess = $pengguna->mobile_admin_access ?? false;
@endphp

<style>
    .pg-hero { background: linear-gradient(135deg, #1a403d 0%, #275a56 100%); border-radius: 20px; color:#fff; }
    .pg-card { border: 1px solid #eef1f4; border-radius: 18px; }
    .pg-avatar { width: 84px; height: 84px; border-radius: 20px; background: rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:800; }
    .cred-code { font-family: ui-monospace, Menlo, monospace; font-size: 15px; letter-spacing: 1px; color:#275a56; background:#eef5f4; border-radius:10px; padding:9px 12px; display:inline-block; }
    .section-label { font-size: 11px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; font-weight:700; }
</style>

<div class="row">
    {{-- Hero --}}
    <div class="col-12 mb-4">
        <div class="pg-hero p-4 d-flex flex-wrap align-items-center justify-content-between" style="gap:16px;">
            <div class="d-flex align-items-center" style="gap:16px;">
                <div class="pg-avatar">{{ strtoupper(mb_substr($pengguna->account->nama_lengkap ?? $pengguna->name ?? 'U', 0, 2)) }}</div>
                <div>
                    <h3 class="mb-1 text-white">{{ $pengguna->account->nama_lengkap ?? $pengguna->name }}</h3>
                    <p class="mb-2" style="opacity:.85;">{{ $pengguna->email }}</p>
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        <span class="badge px-3 py-2" style="background:rgba(255,255,255,.18);">{{ ucfirst($pengguna->role->nama ?? 'Admin') }}</span>
                        <span class="badge px-3 py-2" style="background:{{ $hasAccess ? 'rgba(46,125,50,.35)' : 'rgba(255,255,255,.15)' }};">
                            <i class="ri-smartphone-line me-1"></i>{{ $hasAccess ? 'Akses App Admin Aktif' : 'Belum ada akses app admin' }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.pengguna.index') }}" class="btn btn-light text-dark"><i class="ri-arrow-left-line me-1"></i> Kembali</a>
        </div>
    </div>

    {{-- Form --}}
    <div class="col-lg-8 mb-4">
        <div class="card pg-card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('admin.pengguna.update', $pengguna->id ?? '-') }}" method="POST">
                    @csrf
                    <span class="section-label">Identitas</span>
                    <div class="row mt-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ $pengguna->account->nama_lengkap }}" placeholder="Nama lengkap">
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" value="{{ $pengguna->account->tanggal_lahir }}" max="{{ \Carbon\Carbon::now()->addYear(-17)->format('Y-m-d') }}" name="tgl_lahir" class="form-control @error('tgl_lahir') is-invalid @enderror">
                            @error('tgl_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No Telp</label>
                            <input type="text" class="form-control @error('no_telp') is-invalid @enderror" value="{{ $pengguna->account->no_telpon }}" name="no_telp" placeholder="No Telpon">
                            @error('no_telp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No KTP</label>
                            <input type="text" name="no_ktp" class="form-control @error('no_ktp') is-invalid @enderror" value="{{ $pengguna->account->no_ktp }}" placeholder="No KTP">
                            @error('no_ktp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No NIP <span class="text-muted">(nomor induk pegawai)</span></label>
                            <input type="text" class="form-control @error('no_nip') is-invalid @enderror" value="{{ $pengguna->account->no_nip }}" name="no_nip" placeholder="No NIP">
                            @error('no_nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea style="height: 90px" class="form-control @error('alamat') is-invalid @enderror" name="alamat" placeholder="Alamat">{{ $pengguna->account->alamat }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-3">
                    <span class="section-label">Akun &amp; Akses</span>
                    <div class="row mt-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ $pengguna->email }}" name="email" placeholder="Email">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role Pengguna</label>
                            <select name="id_role" class="form-control @error('id_role') is-invalid @enderror">
                                <option value="">-- Pilih Role Akses --</option>
                                @foreach (App\Models\Role::where('an', 1)->get() as $role)
                                    <option {{ $role->id_role == $pengguna->id_role ? 'selected' : '' }} value="{{ $role->id_role }}">{{ $role->nama }}</option>
                                @endforeach
                            </select>
                            @error('id_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-3">
                    <span class="section-label">Keamanan</span>
                    <div class="mt-2 mb-3" style="max-width: 480px;">
                        <label class="form-label">Password</label>
                        <input autocomplete="new-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="••••••••">
                        <small class="text-danger d-block mt-1">*Kosongkan jika tidak ingin merubah password</small>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success"><i class="ri-save-line me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Akses aplikasi admin --}}
    <div class="col-lg-4 mb-4">
        <div class="card pg-card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3" style="gap:10px;">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:#eef5f4;">
                        <i class="ri-smartphone-line" style="color:#275a56;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Akses Aplikasi Admin</div>
                        <small class="text-muted">Login app admin (mobile).</small>
                    </div>
                </div>

                @if ($hasAccess)
                    <span class="badge badge-sm badge-success mb-2">Aktif</span>
                    @if ($pengguna->credential_key)
                        <div class="mb-1"><span class="section-label">Credential Key</span></div>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <span class="cred-code">{{ $pengguna->credential_key }}</span>
                            <button type="button" class="btn btn-light btn-sm" onclick="copyCredential('{{ $pengguna->credential_key }}')" title="Salin"><i class="ri-file-copy-line"></i></button>
                        </div>
                    @endif
                    <div class="d-grid gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="regenerateCredential({{ $pengguna->id }})"><i class="ri-refresh-line me-1"></i> Generate Ulang Credential</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleMobileAccess({{ $pengguna->id }}, false)"><i class="ri-forbid-line me-1"></i> Cabut Akses</button>
                    </div>
                @else
                    <div class="alert alert-secondary py-2">Belum diberi akses aplikasi admin.</div>
                    <button type="button" class="btn btn-primary w-100" onclick="toggleMobileAccess({{ $pengguna->id }}, true)"><i class="ri-smartphone-line me-1"></i> Beri Akses &amp; Generate</button>
                @endif
                <small class="text-muted d-block mt-2" style="font-size:.78em;">Generate ulang / cabut akses akan mencabut sesi login app admin user ini.</small>
            </div>
        </div>
    </div>
</div>

<script>
    function copyCredential(text) {
        navigator.clipboard.writeText(text).then(() =>
            $.toast({ heading: 'Disalin', text: 'Credential key disalin.', showHideTransition: 'plain', position: 'top-right', icon: 'success' }));
    }
    function showCredential(key) {
        Swal.fire({
            title: 'Credential Key',
            html: '<p class="mb-2 text-muted">Berikan credential ini ke admin terkait.</p><code style="font-size:18px;letter-spacing:1px;background:#eef5f4;color:#275a56;padding:10px 16px;border-radius:10px;display:inline-block;">' + key + '</code>',
            icon: 'success', confirmButtonText: 'Salin & Muat Ulang', confirmButtonColor: '#275a56'
        }).then(() => { copyCredential(key); location.reload(); });
    }
    function toggleMobileAccess(id, grant) {
        Swal.fire({
            title: grant ? 'Beri akses aplikasi admin?' : 'Cabut akses aplikasi admin?',
            text: grant ? 'Credential akan dibuat otomatis bila belum ada.' : 'Sesi login app admin akan dicabut.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: grant ? '#275a56' : '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: grant ? 'Ya, beri akses' : 'Ya, cabut'
        }).then((r) => {
            if (!r.isConfirmed) return;
            $.post('{{ url("admin/pengguna") }}/' + id + '/mobile-access', { _token: '{{ csrf_token() }}' })
                .done((res) => { if (res.mobile_admin_access && res.credential_key) showCredential(res.credential_key); else location.reload(); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON || {}).message || 'Gagal.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' }));
        });
    }
    function regenerateCredential(id) {
        Swal.fire({
            title: 'Generate ulang credential?', text: 'Credential lama tidak berlaku lagi & sesi login dicabut.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#275a56', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, generate ulang'
        }).then((r) => {
            if (!r.isConfirmed) return;
            $.post('{{ url("admin/pengguna") }}/' + id + '/regenerate-credential', { _token: '{{ csrf_token() }}' })
                .done((res) => showCredential(res.credential_key))
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON || {}).message || 'Gagal.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
