@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Mobile Users</h4>
                    <p class="text-muted mb-0">Monitor akun mobile, status verifikasi, dan session token yang sedang aktif.</p>
                </div>
                <div class="d-flex" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.otp_logs') }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri-shield-keyhole-line me-1"></i> Lihat OTP Logs
                    </a>
                    <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table w-100" id="tableMobileUsers">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kontak</th>
                                <th>Verifikasi</th>
                                <th>Status</th>
                                <th>Aktivitas</th>
                                <th>Terdaftar</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        window.$mobileUsersTable = $('#tableMobileUsers').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.users.data") }}'
            },
            columns: [
                {data: 'name', name: 'name'},
                {data: 'contacts', name: 'contacts', orderable: false, searchable: false},
                {data: 'verification', name: 'verification', orderable: false, searchable: false},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'activity', name: 'activity', orderable: false, searchable: false},
                {data: 'registered_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });

    function toggleStatus(id) {
        postMobileAction(
            '{{ url("admin/mobile/users") }}/' + id + '/toggle-status',
            'Status akun ini akan diubah. Lanjutkan?'
        );
    }

    function revokeTokens(id) {
        postMobileAction(
            '{{ url("admin/mobile/users") }}/' + id + '/revoke-tokens',
            'Semua token login user ini akan dicabut. Lanjutkan?'
        );
    }

    function unbanUser(id) {
        postMobileAction(
            '{{ url("admin/mobile/users") }}/' + id + '/unban',
            'Blokir user ini akan dibuka sehingga dapat login kembali. Lanjutkan?'
        );
    }

    function banUser(id) {
        Swal.fire({
            title: 'Blokir User',
            input: 'textarea',
            inputLabel: 'Alasan blokir (opsional, ditampilkan ke user)',
            inputPlaceholder: 'Mis. melanggar ketentuan layanan...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Blokir'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('{{ url("admin/mobile/users") }}/' + id + '/ban', {_token: '{{ csrf_token() }}', reason: result.value || ''})
                .done(function(response) {
                    if (window.$mobileUsersTable) window.$mobileUsersTable.ajax.reload();
                    $.toast({heading: 'Sukses', text: response.message, showHideTransition: 'plain', position: 'top-right', icon: 'success'});
                    if (!window.$mobileUsersTable) setTimeout(() => window.location.reload(), 800);
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};
                    $.toast({heading: 'Warning', text: response.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning'});
                });
        });
    }

    function postMobileAction(url, text) {
        Swal.fire({
            title: 'Konfirmasi',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, lanjutkan'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post(url, {_token: '{{ csrf_token() }}'})
                .done(function(response) {
                    window.$mobileUsersTable.ajax.reload();

                    $.toast({
                        heading: 'Sukses',
                        text: response.message,
                        showHideTransition: 'plain',
                        position: 'top-right',
                        icon: 'success'
                    });
                })
                .fail(function(error) {
                    const response = error.responseJSON || {};

                    $.toast({
                        heading: 'Warning',
                        text: response.message || 'Terjadi kesalahan.',
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                });
        });
    }
</script>
@endsection
