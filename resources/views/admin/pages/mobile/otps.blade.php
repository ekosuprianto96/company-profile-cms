@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">OTP Logs</h4>
                    <p class="text-muted mb-0">Lacak pengiriman dan verifikasi OTP email/SMS untuk login maupun registrasi mobile app.</p>
                </div>
                <div class="d-flex" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.users') }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri-user-settings-line me-1"></i> Lihat Users
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
                    <table class="table w-100" id="tableOtpLogs">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>OTP Code</th>
                                <th>Purpose</th>
                                <th>Channel</th>
                                <th>Recipient</th>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>Timing</th>
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
        $('#tableOtpLogs').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.otp_logs.data") }}'
            },
            columns: [
                {data: 'user', name: 'user', orderable: false, searchable: false},
                {data: 'otp_code', name: 'code_encrypted', orderable: false, searchable: false},
                {data: 'purpose', name: 'purpose'},
                {data: 'channel_badge', name: 'channel', orderable: false, searchable: false},
                {data: 'recipient', name: 'recipient'},
                {data: 'provider', name: 'provider'},
                {data: 'status_badge', name: 'status', orderable: false, searchable: false},
                {data: 'attempts', name: 'attempts'},
                {data: 'timing', name: 'timing', orderable: false, searchable: false}
            ]
        });
    });
</script>
@endsection
