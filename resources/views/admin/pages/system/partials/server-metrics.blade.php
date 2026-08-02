@php
    /** @var array $metrics */
    $live = $live ?? false;
    $cpu = $metrics['cpu'] ?? [];
    $mem = $metrics['memory'] ?? [];
    $disk = $metrics['disk'] ?? [];
    $sys = $metrics['system'] ?? [];
    $db = $metrics['database'] ?? [];
    $queue = $metrics['queue'] ?? [];

    $fmtBytes = function ($b) {
        if ($b === null) return '—';
        $u = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0; $b = (float) $b;
        while ($b >= 1024 && $i < 4) { $b /= 1024; $i++; }
        return number_format($b, $b >= 100 ? 0 : 1) . ' ' . $u[$i];
    };
    $fmtUptime = function ($s) {
        if ($s === null) return '—';
        $d = intdiv($s, 86400); $h = intdiv($s % 86400, 3600); $m = intdiv($s % 3600, 60);
        return ($d ? $d . ' hari ' : '') . ($h ? $h . ' jam ' : '') . $m . ' mnt';
    };
    $barClass = function ($p) {
        if ($p === null) return 'bg-secondary';
        return $p >= 85 ? 'bg-danger' : ($p >= 65 ? 'bg-warning' : 'bg-success');
    };
@endphp

<div class="row" id="server-metrics"
     @if($live) data-live-url="{{ route('admin.system.metrics') }}" @endif>
    {{-- CPU --}}
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="d-flex align-items-center" style="gap:.5rem">
                        <i class="ri-cpu-line" style="font-size:22px;color:#6571ff"></i>
                        <span style="font-weight:600">CPU Load</span>
                    </span>
                    <span class="badge badge-light" id="sm-cpu-cores">{{ $cpu['cores'] ?? '—' }} core</span>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <h3 class="mb-0" id="sm-cpu-percent">{{ $cpu['percent'] !== null ? $cpu['percent'] . '%' : '—' }}</h3>
                    <small class="text-muted" id="sm-cpu-load">load {{ $cpu['load_1'] ?? '—' }} / {{ $cpu['load_5'] ?? '—' }} / {{ $cpu['load_15'] ?? '—' }}</small>
                </div>
                <div class="progress mt-2" style="height:8px">
                    <div class="progress-bar {{ $barClass($cpu['percent'] ?? null) }}" id="sm-cpu-bar"
                         style="width: {{ $cpu['percent'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Memory --}}
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="d-flex align-items-center" style="gap:.5rem">
                        <i class="ri-ram-line" style="font-size:22px;color:#05c3fb"></i>
                        <span style="font-weight:600">Memori (RAM)</span>
                    </span>
                    <span class="badge badge-light" id="sm-mem-total">{{ $fmtBytes($mem['total'] ?? null) }}</span>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <h3 class="mb-0" id="sm-mem-percent">{{ $mem['percent'] !== null ? $mem['percent'] . '%' : '—' }}</h3>
                    <small class="text-muted" id="sm-mem-detail">{{ $fmtBytes($mem['used'] ?? null) }} terpakai</small>
                </div>
                <div class="progress mt-2" style="height:8px">
                    <div class="progress-bar {{ $barClass($mem['percent'] ?? null) }}" id="sm-mem-bar"
                         style="width: {{ $mem['percent'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Disk --}}
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="d-flex align-items-center" style="gap:.5rem">
                        <i class="ri-hard-drive-2-line" style="font-size:22px;color:#0fb78d"></i>
                        <span style="font-weight:600">Disk</span>
                    </span>
                    <span class="badge badge-light" id="sm-disk-total">{{ $fmtBytes($disk['total'] ?? null) }}</span>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <h3 class="mb-0" id="sm-disk-percent">{{ $disk['percent'] !== null ? $disk['percent'] . '%' : '—' }}</h3>
                    <small class="text-muted" id="sm-disk-detail">{{ $fmtBytes($disk['free'] ?? null) }} sisa</small>
                </div>
                <div class="progress mt-2" style="height:8px">
                    <div class="progress-bar {{ $barClass($disk['percent'] ?? null) }}" id="sm-disk-bar"
                         style="width: {{ $disk['percent'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info sistem --}}
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="mb-3" style="font-weight:600"><i class="ri-server-line me-1"></i> Informasi Server</h6>
                <div class="row">
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Sistem Operasi</small><span>{{ $sys['os'] ?? '—' }}</span></div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Hostname</small><span>{{ $sys['hostname'] ?? '—' }}</span></div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Uptime</small><span id="sm-uptime">{{ $fmtUptime($sys['uptime_seconds'] ?? null) }}</span></div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">PHP</small><span>{{ $sys['php_version'] ?? '—' }}</span></div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Laravel</small><span>{{ $sys['laravel_version'] ?? '—' }}</span></div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Environment</small><span class="badge {{ ($sys['environment'] ?? '') === 'production' ? 'badge-success' : 'badge-light' }}">{{ $sys['environment'] ?? '—' }}</span></div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Database</small>
                        <span id="sm-db-status">
                            @if(($db['connected'] ?? false))
                                <span class="badge badge-success">Terhubung</span> <small class="text-muted">{{ $db['latency_ms'] ?? '—' }} ms</small>
                            @else
                                <span class="badge badge-danger">Terputus</span>
                            @endif
                        </span>
                    </div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Driver DB</small><span>{{ $db['driver'] ?? '—' }}</span></div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Waktu Server</small><span id="sm-server-time">{{ $sys['server_time'] ?? '—' }}</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan antrean --}}
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="mb-3" style="font-weight:600"><i class="ri-stack-line me-1"></i> Antrean Job</h6>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Menunggu</span>
                    <span class="badge badge-light" id="sm-q-pending" style="font-size:14px">{{ $queue['pending'] ?? 0 }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Diproses</span>
                    <span class="badge badge-info" id="sm-q-reserved" style="font-size:14px">{{ $queue['reserved'] ?? 0 }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted">Gagal</span>
                    <span class="badge {{ ($queue['failed'] ?? 0) > 0 ? 'badge-danger' : 'badge-light' }}" id="sm-q-failed" style="font-size:14px">{{ $queue['failed'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if($live)
@push('admin-scripts')
<script>
(function () {
    var root = document.getElementById('server-metrics');
    if (!root) return;
    var url = root.getAttribute('data-live-url');
    if (!url) return;

    function fmtBytes(b) {
        if (b === null || b === undefined) return '—';
        var u = ['B', 'KB', 'MB', 'GB', 'TB'], i = 0; b = parseFloat(b);
        while (b >= 1024 && i < 4) { b /= 1024; i++; }
        return (b >= 100 ? b.toFixed(0) : b.toFixed(1)) + ' ' + u[i];
    }
    function barClass(p) {
        if (p === null || p === undefined) return 'bg-secondary';
        return p >= 85 ? 'bg-danger' : (p >= 65 ? 'bg-warning' : 'bg-success');
    }
    function set(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
    function setBar(id, p) {
        var el = document.getElementById(id); if (!el) return;
        el.style.width = (p || 0) + '%';
        el.className = 'progress-bar ' + barClass(p);
    }

    function refresh() {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (m) {
                if (!m) return;
                set('sm-cpu-percent', m.cpu.percent !== null ? m.cpu.percent + '%' : '—');
                set('sm-cpu-load', 'load ' + m.cpu.load_1 + ' / ' + m.cpu.load_5 + ' / ' + m.cpu.load_15);
                setBar('sm-cpu-bar', m.cpu.percent);
                set('sm-mem-percent', m.memory.percent !== null ? m.memory.percent + '%' : '—');
                set('sm-mem-detail', fmtBytes(m.memory.used) + ' terpakai');
                setBar('sm-mem-bar', m.memory.percent);
                set('sm-disk-percent', m.disk.percent !== null ? m.disk.percent + '%' : '—');
                set('sm-disk-detail', fmtBytes(m.disk.free) + ' sisa');
                setBar('sm-disk-bar', m.disk.percent);
                set('sm-server-time', m.system.server_time);
                set('sm-q-pending', m.queue.pending);
                set('sm-q-reserved', m.queue.reserved);
                set('sm-q-failed', m.queue.failed);
            })
            .catch(function () {});
    }
    setInterval(refresh, 5000);
})();
</script>
@endpush
@endif
