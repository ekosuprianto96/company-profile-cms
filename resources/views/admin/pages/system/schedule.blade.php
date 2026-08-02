@extends('admin.layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.75rem">
    <div>
        <h4 class="mb-1" style="font-weight:700">Cron Schedule</h4>
        <p class="text-muted mb-0">Daftar tugas terjadwal (scheduler Laravel). Jalankan manual bila perlu.</p>
    </div>
    <a href="{{ route('admin.system.jobs') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ri-stack-line me-1"></i> Monitoring Job
    </a>
</div>

<div class="alert alert-info d-flex align-items-start" style="gap:.5rem">
    <i class="ri-information-line" style="font-size:20px"></i>
    <div>
        <strong>Scheduler berjalan melalui satu cron induk.</strong>
        Pastikan crontab server memuat:
        <code>* * * * * cd {{ base_path() }} &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tugas</th>
                        <th style="width:150px">Jadwal</th>
                        <th style="width:170px">Ekspresi Cron</th>
                        <th style="width:180px">Eksekusi Berikutnya</th>
                        <th style="width:110px" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr>
                        <td>
                            <span style="font-weight:600">{{ $task['command'] ?? $task['summary'] }}</span>
                            @if($task['description'])
                                <small class="d-block text-muted">{{ $task['description'] }}</small>
                            @endif
                            @if($task['without_overlapping'])
                                <span class="badge badge-light mt-1">withoutOverlapping</span>
                            @endif
                        </td>
                        <td>{{ $task['human'] }}</td>
                        <td><code>{{ $task['expression'] }}</code></td>
                        <td>
                            @if($task['next_run'])
                                <small>{{ $task['next_run'] }}</small>
                                <small class="d-block text-muted">{{ \Illuminate\Support\Carbon::parse($task['next_run'])->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($task['runnable'])
                            <form method="POST" action="{{ route('admin.system.schedule.run') }}" class="js-run d-inline"
                                  data-title="Jalankan sekarang?" data-text="{{ $task['artisan'] }}">
                                @csrf
                                <input type="hidden" name="command" value="{{ $task['artisan'] }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Jalankan sekarang">
                                    <i class="ri-play-circle-line"></i>
                                </button>
                            </form>
                            @else
                                <span class="badge badge-light" title="Job/Closure — jalankan lewat worker antrean">Job</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada tugas terjadwal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('admin-scripts')
<script>
document.querySelectorAll('.js-run').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
            title: form.dataset.title || 'Jalankan?',
            text: form.dataset.text || '',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, jalankan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#275a56',
            cancelButtonColor: '#6c757d',
        }).then(function (res) { if (res.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
