<?php

namespace App\Services\System;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Mengumpulkan metrik kondisi server & antrean.
 *
 * Metrik berbasis Linux (/proc, sys_getloadavg) dijaga dengan pengecekan
 * ketersediaan sehingga aman dijalankan di lingkungan non-Linux / shared
 * hosting yang membatasi fungsi — nilai yang tak tersedia dikembalikan null.
 */
class ServerMetricsService
{
    /** Ambil seluruh metrik untuk ditampilkan di dashboard / halaman sistem. */
    public function all(): array
    {
        return [
            'cpu' => $this->cpu(),
            'memory' => $this->memory(),
            'disk' => $this->disk(),
            'system' => $this->system(),
            'database' => $this->database(),
            'queue' => $this->queue(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /** Beban CPU (load average) dan estimasi persentase terhadap jumlah core. */
    public function cpu(): array
    {
        $cores = $this->cpuCores();
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : null;

        $load1 = is_array($load) ? round((float) $load[0], 2) : null;
        $percent = ($load1 !== null && $cores > 0)
            ? min(100, (int) round(($load1 / $cores) * 100))
            : null;

        return [
            'cores' => $cores,
            'load_1' => $load1,
            'load_5' => is_array($load) ? round((float) $load[1], 2) : null,
            'load_15' => is_array($load) ? round((float) $load[2], 2) : null,
            'percent' => $percent,
            'available' => $load !== null,
        ];
    }

    /** Penggunaan memori (RAM) dari /proc/meminfo. */
    public function memory(): array
    {
        $total = null;
        $available = null;

        if (@is_readable('/proc/meminfo')) {
            $content = @file_get_contents('/proc/meminfo');
            if ($content !== false) {
                if (preg_match('/MemTotal:\s+(\d+)\s*kB/', $content, $m)) {
                    $total = (int) $m[1] * 1024;
                }
                if (preg_match('/MemAvailable:\s+(\d+)\s*kB/', $content, $m)) {
                    $available = (int) $m[1] * 1024;
                }
            }
        }

        $used = ($total !== null && $available !== null) ? $total - $available : null;
        $percent = ($total && $used !== null) ? (int) round(($used / $total) * 100) : null;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $available,
            'percent' => $percent,
            'available' => $total !== null,
        ];
    }

    /** Penggunaan disk pada root filesystem. */
    public function disk(): array
    {
        $total = @disk_total_space('/') ?: null;
        $free = @disk_free_space('/') ?: null;
        $used = ($total !== null && $free !== null) ? $total - $free : null;
        $percent = ($total && $used !== null) ? (int) round(($used / $total) * 100) : null;

        return [
            'total' => $total ? (int) $total : null,
            'used' => $used ? (int) $used : null,
            'free' => $free ? (int) $free : null,
            'percent' => $percent,
            'available' => $total !== null,
        ];
    }

    /** Informasi umum sistem: OS, versi PHP/Laravel, uptime. */
    public function system(): array
    {
        $uptime = null;
        if (@is_readable('/proc/uptime')) {
            $content = @file_get_contents('/proc/uptime');
            if ($content !== false) {
                $uptime = (int) round((float) explode(' ', trim($content))[0]);
            }
        }

        return [
            'os' => PHP_OS_FAMILY,
            'hostname' => gethostname() ?: null,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'uptime_seconds' => $uptime,
            'server_time' => now()->toDateTimeString(),
        ];
    }

    /** Status koneksi database + latensi query sederhana. */
    public function database(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('select 1');
            $latency = (int) round((microtime(true) - $start) * 1000);

            return [
                'connected' => true,
                'driver' => DB::connection()->getDriverName(),
                'database' => DB::connection()->getDatabaseName(),
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            return [
                'connected' => false,
                'driver' => config('database.default'),
                'database' => null,
                'latency_ms' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /** Ringkasan antrean job: menunggu, gagal, umur job tertua. */
    public function queue(): array
    {
        $pending = 0;
        $reserved = 0;
        $failed = 0;
        $oldestSeconds = null;

        try {
            $pending = (int) DB::table('jobs')->count();
            $reserved = (int) DB::table('jobs')->whereNotNull('reserved_at')->count();
            $failed = (int) DB::table('failed_jobs')->count();

            $oldest = DB::table('jobs')->min('available_at');
            if ($oldest) {
                $oldestSeconds = max(0, time() - (int) $oldest);
            }
        } catch (Throwable $e) {
            // Tabel belum ada / driver bukan database — biarkan nilai default.
        }

        return [
            'connection' => config('queue.default'),
            'pending' => $pending,
            'reserved' => $reserved,
            'failed' => $failed,
            'oldest_wait_seconds' => $oldestSeconds,
        ];
    }

    protected function cpuCores(): int
    {
        if (@is_readable('/proc/cpuinfo')) {
            $content = @file_get_contents('/proc/cpuinfo');
            if ($content !== false) {
                $count = substr_count($content, 'processor');
                if ($count > 0) {
                    return $count;
                }
            }
        }

        return 1;
    }
}
