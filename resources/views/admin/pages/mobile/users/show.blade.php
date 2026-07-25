@extends('admin.layouts.main')

@section('content')
@php
    $initials = collect(explode(' ', trim($user->name ?? '')))
        ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $isBanned = ! is_null($user->banned_at);
    $rupiah = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');

    $srStatusColor = fn ($s) => match ($s) {
        'approved', 'completed', 'paid' => 'success',
        'rejected', 'failed', 'cancelled' => 'danger',
        'waiting_payment', 'waiting_transfer', 'payment_challenge', 'pending' => 'warning',
        default => 'secondary',
    };
@endphp

<div class="row">
    {{-- Header --}}
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Detail Mobile User</h4>
                    <p class="text-muted mb-0">Profil, aktivitas, dan kontrol akun user mobile.</p>
                </div>
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    <button type="button" onclick="toggleStatus({{ $user->id }})" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'secondary' }} btn-sm">
                        <i class="ri-{{ $user->is_active ? 'pause-circle-line' : 'play-circle-line' }} me-1"></i> {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                    <button type="button" onclick="revokeTokens({{ $user->id }})" class="btn btn-outline-dark btn-sm">
                        <i class="ri-key-line me-1"></i> Cabut Sesi
                    </button>
                    @if ($isBanned)
                        <button type="button" onclick="unbanUser({{ $user->id }})" class="btn btn-success btn-sm">
                            <i class="ri-shield-check-line me-1"></i> Buka Blokir
                        </button>
                    @else
                        <button type="button" onclick="banUser({{ $user->id }})" class="btn btn-danger btn-sm">
                            <i class="ri-forbid-2-line me-1"></i> Blokir User
                        </button>
                    @endif
                    <a href="{{ route('admin.mobile.users') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Banned banner --}}
    @if ($isBanned)
        <div class="col-md-12 mb-4">
            <div class="alert alert-danger d-flex align-items-start mb-0" style="gap: 12px;">
                <i class="ri-forbid-2-line" style="font-size: 22px;"></i>
                <div>
                    <strong>Akun diblokir</strong> pada {{ $user->banned_at?->format('d M Y H:i') }}
                    @if ($user->bannedBy) oleh {{ $user->bannedBy->name }} @endif.
                    <div class="mt-1">Alasan: {{ $user->ban_reason ?: '—' }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Profile card --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                     style="width: 84px; height: 84px; font-size: 28px; font-weight: 700; overflow: hidden;">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="avatar" style="width:84px;height:84px;object-fit:cover;">
                    @else
                        {{ $initials ?: '?' }}
                    @endif
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <div class="mb-2">
                    @if ($isBanned)
                        <span class="badge badge-danger">Banned</span>
                    @else
                        <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    @endif
                </div>
                <ul class="list-group list-group-flush text-start mt-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="ri-mail-line me-1"></i> Email</span>
                        <span>{{ $user->email ?: '-' }}
                            <span class="badge badge-sm badge-{{ $user->email_verified_at ? 'success' : 'secondary' }}">{{ $user->email_verified_at ? 'Verified' : 'Pending' }}</span>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="ri-phone-line me-1"></i> Telepon</span>
                        <span>{{ $user->phone ?: '-' }}
                            <span class="badge badge-sm badge-{{ $user->phone_verified_at ? 'success' : 'secondary' }}">{{ $user->phone_verified_at ? 'Verified' : 'Pending' }}</span>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted"><i class="ri-calendar-line me-1"></i> Terdaftar</span>
                        <span>{{ $user->created_at?->format('d M Y H:i') ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted"><i class="ri-login-circle-line me-1"></i> Login Terakhir</span>
                        <span>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Stats + related --}}
    <div class="col-md-8 mb-4">
        <div class="row">
            @foreach ($stats as $stat)
                <div class="col-md-4 col-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center" style="gap: 12px;">
                            <div class="d-flex align-items-center justify-content-center rounded bg-light" style="width:44px;height:44px;">
                                <i class="{{ $stat['icon'] }}" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <div class="h4 mb-0">{{ $stat['value'] }}</div>
                                <div class="text-muted small">{{ $stat['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Alamat --}}
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ri-map-pin-line me-1"></i> Alamat ({{ $user->addresses_count }})</h5>
                @forelse ($user->addresses as $address)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $address->label ?: 'Alamat' }} @if ($address->is_primary)<span class="badge badge-sm badge-primary ms-1">Utama</span>@endif</strong>
                            <span class="text-muted small">{{ $address->recipient_name }} · {{ $address->recipient_phone }}</span>
                        </div>
                        <div class="text-muted small mt-1">{{ $address->address }}</div>
                        <div class="text-muted small">{{ $address->region_label }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada alamat.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Order Layanan --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ri-file-list-3-line me-1"></i> Order Layanan Terakhir</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($user->serviceRequests as $sr)
                            <tr>
                                <td>
                                    <div><strong>{{ $sr->transaction_code_label }}</strong></div>
                                    <div class="text-muted small">{{ $sr->service->title ?? '-' }} · {{ $sr->created_at?->format('d M Y') }}</div>
                                </td>
                                <td class="text-end">
                                    <div><span class="badge badge-sm badge-{{ $srStatusColor($sr->status) }}">{{ ucfirst(str_replace('_', ' ', $sr->status)) }}</span></div>
                                    <div class="text-muted small">{{ $rupiah($sr->total_amount) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Belum ada order layanan.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Produk --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ri-shopping-bag-3-line me-1"></i> Order Produk Terakhir</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($user->productOrders as $order)
                            <tr>
                                <td>
                                    <div><strong>{{ $order->order_number }}</strong></div>
                                    <div class="text-muted small">{{ $order->created_at?->format('d M Y') }}</div>
                                </td>
                                <td class="text-end">
                                    <div><span class="badge badge-sm badge-{{ $srStatusColor($order->payment_status ?? $order->status) }}">{{ $order->status_label ?: ucfirst((string) $order->status) }}</span></div>
                                    <div class="text-muted small">{{ $rupiah($order->total_amount) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Belum ada order produk.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Proposal --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ri-draft-line me-1"></i> Proposal Terakhir</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($user->proposals as $proposal)
                            <tr>
                                <td>
                                    <div><strong>{{ $proposal->proposal_number }}</strong></div>
                                    <div class="text-muted small">{{ $proposal->service->title ?? '-' }} · {{ $proposal->created_at?->format('d M Y') }}</div>
                                </td>
                                <td class="text-end">
                                    <div><span class="badge badge-sm badge-{{ $srStatusColor($proposal->status) }}">{{ ucfirst((string) $proposal->status) }}</span></div>
                                    <div class="text-muted small">{{ $rupiah($proposal->total_amount) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Belum ada proposal.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Voucher diklaim --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ri-coupon-3-line me-1"></i> Voucher Diklaim</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($user->voucherClaims as $claim)
                            <tr>
                                <td>
                                    <div><strong>{{ $claim->voucher->name ?? '-' }}</strong></div>
                                    <div class="text-muted small">{{ $claim->voucher->code ?? '' }}</div>
                                </td>
                                <td class="text-end text-muted small">{{ $claim->claimed_at?->format('d M Y H:i') ?? $claim->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Belum ada voucher diklaim.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sesi aktif --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ri-device-line me-1"></i> Sesi / Perangkat Aktif ({{ $user->tokens_count }})</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($user->tokens as $token)
                            <tr>
                                <td><strong>{{ $token->name ?: 'Perangkat' }}</strong></td>
                                <td class="text-end text-muted small">
                                    <div>Terakhir: {{ $token->last_used_at?->format('d M Y H:i') ?? '-' }}</div>
                                    <div>Dibuat: {{ $token->created_at?->format('d M Y') ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Tidak ada sesi aktif.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- OTP terakhir --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ri-timer-flash-line me-1"></i> OTP Terakhir</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($recentOtps as $otp)
                            <tr>
                                <td>
                                    <span class="badge badge-sm badge-{{ $otp->channel === 'sms' ? 'info' : 'primary' }}">{{ strtoupper($otp->channel) }}</span>
                                    <span class="text-muted small">{{ $otp->purpose }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge badge-sm badge-secondary">{{ ucfirst((string) $otp->status) }}</span>
                                    <div class="text-muted small">{{ $otp->created_at?->format('d M Y H:i') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Belum ada OTP.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function postMobileUserAction(url, text) {
        Swal.fire({
            title: 'Konfirmasi', text: text, icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Ya, lanjutkan'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(url, {_token: '{{ csrf_token() }}'})
                .done(function(response) {
                    $.toast({heading: 'Sukses', text: response.message, showHideTransition: 'plain', position: 'top-right', icon: 'success'});
                    setTimeout(() => window.location.reload(), 800);
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};
                    $.toast({heading: 'Warning', text: response.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning'});
                });
        });
    }

    function toggleStatus(id) {
        postMobileUserAction('{{ url("admin/mobile/users") }}/' + id + '/toggle-status', 'Status akun ini akan diubah. Lanjutkan?');
    }

    function revokeTokens(id) {
        postMobileUserAction('{{ url("admin/mobile/users") }}/' + id + '/revoke-tokens', 'Semua sesi login user ini akan dicabut. Lanjutkan?');
    }

    function unbanUser(id) {
        postMobileUserAction('{{ url("admin/mobile/users") }}/' + id + '/unban', 'Blokir user ini akan dibuka. Lanjutkan?');
    }

    function banUser(id) {
        Swal.fire({
            title: 'Blokir User', input: 'textarea',
            inputLabel: 'Alasan blokir (opsional, ditampilkan ke user)',
            inputPlaceholder: 'Mis. melanggar ketentuan layanan...',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Blokir'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ url("admin/mobile/users") }}/' + id + '/ban', {_token: '{{ csrf_token() }}', reason: result.value || ''})
                .done(function(response) {
                    $.toast({heading: 'Sukses', text: response.message, showHideTransition: 'plain', position: 'top-right', icon: 'success'});
                    setTimeout(() => window.location.reload(), 800);
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};
                    $.toast({heading: 'Warning', text: response.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning'});
                });
        });
    }
</script>
@endsection
