@extends('admin.layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.75rem">
    <div>
        <h4 class="mb-1" style="font-weight:700">Monitoring Job & Antrean</h4>
        <p class="text-muted mb-0">Pantau antrean job, jalankan ulang atau hapus job yang gagal.</p>
    </div>
    <div class="d-flex align-items-center" style="gap:.5rem">
        <a href="{{ route('admin.system.schedule') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-time-line me-1"></i> Cron Schedule
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
            <i class="ri-refresh-line me-1"></i> Segarkan
        </button>
    </div>
</div>

@include('admin.pages.system.partials.server-metrics', ['metrics' => $metrics, 'live' => true])

{{-- Antrean menunggu --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:.5rem">
            <h6 class="mb-0" style="font-weight:600">
                <i class="ri-stack-line me-1"></i> Antrean Menunggu
                <span class="badge badge-light ms-1">{{ $pending->count() }}</span>
            </h6>
            @if($pending->count() > 0)
            <form method="POST" action="{{ route('admin.system.pending.purge') }}" class="js-confirm"
                  data-title="Kosongkan antrean?" data-text="Semua job menunggu akan dihentikan & dihapus.">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="ri-delete-bin-line me-1"></i> Kosongkan Antrean
                </button>
            </form>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:70px">ID</th>
                        <th>Job</th>
                        <th style="width:120px">Queue</th>
                        <th style="width:90px">Attempts</th>
                        <th style="width:120px">Status</th>
                        <th style="width:150px">Tersedia</th>
                        <th style="width:90px" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pending as $job)
                    <tr>
                        <td>{{ $job['id'] }}</td>
                        <td><span style="font-weight:600">{{ $job['name'] }}</span></td>
                        <td><span class="badge badge-light">{{ $job['queue'] }}</span></td>
                        <td>{{ $job['attempts'] }}</td>
                        <td>
                            @if($job['reserved'])
                                <span class="badge badge-info">Diproses</span>
                            @else
                                <span class="badge badge-warning">Menunggu</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $job['available_at'] }}</small></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.system.pending.stop', $job['id']) }}" class="js-confirm d-inline"
                                  data-title="Hentikan job?" data-text="Job akan dihapus dari antrean.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Stop / hapus">
                                    <i class="ri-stop-circle-line"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada job menunggu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Job gagal --}}
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:.5rem">
            <h6 class="mb-0" style="font-weight:600">
                <i class="ri-error-warning-line me-1"></i> Job Gagal
                <span class="badge {{ $failed->count() > 0 ? 'badge-danger' : 'badge-light' }} ms-1">{{ $failed->count() }}</span>
            </h6>
            @if($failed->count() > 0)
            <div class="d-flex" style="gap:.5rem">
                <form method="POST" action="{{ route('admin.system.jobs.retry_all') }}" class="js-confirm"
                      data-title="Coba ulang semua?" data-text="Semua job gagal akan dimasukkan kembali ke antrean." data-icon="question">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="ri-refresh-line me-1"></i> Coba Ulang Semua
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.system.jobs.flush') }}" class="js-confirm"
                      data-title="Bersihkan riwayat?" data-text="Semua riwayat job gagal akan dihapus permanen.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="ri-delete-bin-line me-1"></i> Bersihkan Semua
                    </button>
                </form>
            </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:70px">ID</th>
                        <th>Job</th>
                        <th>Error</th>
                        <th style="width:120px">Queue</th>
                        <th style="width:150px">Gagal</th>
                        <th style="width:130px" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failed as $job)
                    <tr>
                        <td>{{ $job['id'] }}</td>
                        <td>
                            <span style="font-weight:600">{{ $job['name'] }}</span>
                            <small class="d-block text-muted">{{ $job['connection'] }} · {{ $job['queue'] }}</small>
                        </td>
                        <td>
                            <small class="text-danger">{{ $job['exception_short'] }}</small>
                            <a href="#" class="d-block small js-show-exception"
                               data-title="{{ $job['name'] }}"
                               data-exception="{{ e($job['exception_full']) }}">Lihat detail</a>
                        </td>
                        <td><span class="badge badge-light">{{ $job['queue'] }}</span></td>
                        <td><small class="text-muted" title="{{ $job['failed_at'] }}">{{ $job['failed_ago'] }}</small></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.system.jobs.retry', $job['uuid']) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Coba ulang">
                                    <i class="ri-refresh-line"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.system.jobs.forget', $job['uuid']) }}" class="js-confirm d-inline"
                                  data-title="Hapus job gagal?" data-text="Riwayat job ini akan dihapus.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada job gagal. 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('admin-scripts')
<script>
document.querySelectorAll('.js-confirm').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
            title: form.dataset.title || 'Yakin?',
            text: form.dataset.text || '',
            icon: form.dataset.icon || 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjut',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
        }).then(function (res) { if (res.isConfirmed) form.submit(); });
    });
});
document.querySelectorAll('.js-show-exception').forEach(function (link) {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: link.dataset.title || 'Detail Error',
            html: '<pre style="text-align:left;white-space:pre-wrap;word-break:break-word;max-height:50vh;overflow:auto;font-size:12px">'
                + link.dataset.exception + '</pre>',
            width: '48rem',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#275a56',
        });
    });
});
</script>
@endpush
