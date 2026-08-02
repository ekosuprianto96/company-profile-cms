<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\System\ServerMetricsService;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class SystemMonitorController extends Controller
{
    public function __construct(protected ServerMetricsService $metrics)
    {
    }

    /** Halaman monitoring job: antrean menunggu + job gagal. */
    public function jobs(Request $request)
    {
        $pending = DB::table('jobs')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn ($row) => $this->presentPendingJob($row));

        $failed = DB::table('failed_jobs')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn ($row) => $this->presentFailedJob($row));

        return view('admin.pages.system.jobs', [
            'metrics' => $this->metrics->all(),
            'pending' => $pending,
            'failed' => $failed,
        ]);
    }

    /** Halaman daftar cron / scheduled tasks. */
    public function schedule(Schedule $schedule)
    {
        $tasks = collect($schedule->events())->map(function ($event) {
            $isCallback = $event instanceof CallbackEvent;
            $command = $this->cleanCommand($event->command ?? null);
            $nextRun = null;

            try {
                $nextRun = (new CronExpression($event->expression))
                    ->getNextRunDate(Carbon::now($event->timezone ?: config('app.timezone')))
                    ->format('Y-m-d H:i:s');
            } catch (Throwable $e) {
                $nextRun = null;
            }

            return [
                'summary' => method_exists($event, 'getSummaryForDisplay')
                    ? $event->getSummaryForDisplay()
                    : ($command ?? 'Closure'),
                'command' => $command,
                'artisan' => $this->artisanSignature($command),
                'expression' => $event->expression,
                'human' => $this->humanExpression($event->expression),
                'description' => $event->description,
                'timezone' => $event->timezone ?: config('app.timezone'),
                'without_overlapping' => (bool) ($event->withoutOverlapping ?? false),
                'runnable' => ! $isCallback && $this->artisanSignature($command) !== null,
                'next_run' => $nextRun,
            ];
        })->values();

        return view('admin.pages.system.schedule', [
            'tasks' => $tasks,
            'metrics' => $this->metrics->all(),
        ]);
    }

    /** Endpoint JSON metrik server (untuk polling live di dashboard & halaman sistem). */
    public function serverMetrics()
    {
        return response()->json($this->metrics->all());
    }

    /** Coba jalankan ulang satu job gagal. */
    public function retry(string $uuid)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$uuid]]);
            Alert::success('Berhasil', 'Job dimasukkan ulang ke antrean.');
        } catch (Throwable $e) {
            Alert::error('Gagal', $e->getMessage());
        }

        return back();
    }

    /** Jalankan ulang seluruh job gagal. */
    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            Alert::success('Berhasil', 'Semua job gagal dimasukkan ulang ke antrean.');
        } catch (Throwable $e) {
            Alert::error('Gagal', $e->getMessage());
        }

        return back();
    }

    /** Hapus satu job gagal. */
    public function forget(string $uuid)
    {
        try {
            Artisan::call('queue:forget', ['id' => $uuid]);
            Alert::success('Berhasil', 'Job gagal dihapus.');
        } catch (Throwable $e) {
            Alert::error('Gagal', $e->getMessage());
        }

        return back();
    }

    /** Hapus seluruh job gagal. */
    public function flush()
    {
        try {
            Artisan::call('queue:flush');
            Alert::success('Berhasil', 'Seluruh riwayat job gagal dibersihkan.');
        } catch (Throwable $e) {
            Alert::error('Gagal', $e->getMessage());
        }

        return back();
    }

    /** Stop (hapus) satu job yang masih menunggu di antrean. */
    public function stopPending(int $id)
    {
        try {
            $deleted = DB::table('jobs')->where('id', $id)->delete();
            $deleted
                ? Alert::success('Berhasil', 'Job antrean dihentikan & dihapus.')
                : Alert::info('Info', 'Job tidak ditemukan (mungkin sudah diproses).');
        } catch (Throwable $e) {
            Alert::error('Gagal', $e->getMessage());
        }

        return back();
    }

    /** Kosongkan seluruh antrean job yang menunggu. */
    public function purgePending()
    {
        try {
            $count = DB::table('jobs')->count();
            DB::table('jobs')->delete();
            Alert::success('Berhasil', "{$count} job antrean dihentikan.");
        } catch (Throwable $e) {
            Alert::error('Gagal', $e->getMessage());
        }

        return back();
    }

    /** Jalankan sekarang sebuah scheduled command (whitelist dari daftar schedule). */
    public function runTask(Request $request, Schedule $schedule)
    {
        $signature = (string) $request->input('command');

        $allowed = collect($schedule->events())
            ->map(fn ($event) => $this->artisanSignature($this->cleanCommand($event->command ?? null)))
            ->filter()
            ->values()
            ->all();

        if (! in_array($signature, $allowed, true)) {
            Alert::error('Ditolak', 'Perintah tidak terdaftar pada schedule.');

            return back();
        }

        try {
            Artisan::call($signature);
            Alert::success('Berhasil', "Perintah '{$signature}' dijalankan.");
        } catch (Throwable $e) {
            Alert::error('Gagal', $e->getMessage());
        }

        return back();
    }

    /** ------- Helper presentasi ------- */

    protected function presentPendingJob(object $row): array
    {
        $payload = json_decode($row->payload, true) ?: [];

        return [
            'id' => $row->id,
            'name' => $payload['displayName'] ?? ($payload['job'] ?? 'Job'),
            'queue' => $row->queue,
            'attempts' => (int) $row->attempts,
            'reserved' => $row->reserved_at !== null,
            'available_at' => $row->available_at ? Carbon::createFromTimestamp($row->available_at)->toDateTimeString() : null,
            'created_at' => $row->created_at ? Carbon::createFromTimestamp($row->created_at)->diffForHumans() : null,
        ];
    }

    protected function presentFailedJob(object $row): array
    {
        $payload = json_decode($row->payload, true) ?: [];
        $exception = (string) $row->exception;
        $firstLine = trim(strtok($exception, "\n"));

        return [
            'id' => $row->id,
            'uuid' => $row->uuid,
            'name' => $payload['displayName'] ?? ($payload['job'] ?? 'Job'),
            'connection' => $row->connection,
            'queue' => $row->queue,
            'failed_at' => $row->failed_at ? Carbon::parse($row->failed_at)->toDateTimeString() : null,
            'failed_ago' => $row->failed_at ? Carbon::parse($row->failed_at)->diffForHumans() : null,
            'exception_short' => mb_strimwidth($firstLine, 0, 180, '…'),
            'exception_full' => mb_strimwidth($exception, 0, 4000, '…'),
        ];
    }

    protected function cleanCommand(?string $command): ?string
    {
        if (! $command) {
            return null;
        }

        // Buang path binary PHP & artisan: "'/usr/bin/php' 'artisan' foo:bar" -> "foo:bar"
        $command = preg_replace("/^.*?artisan'?\s*/", '', $command);

        return trim($command) ?: null;
    }

    protected function artisanSignature(?string $command): ?string
    {
        if (! $command) {
            return null;
        }

        // Ambil hanya bagian signature (buang argumen/opsi untuk validasi whitelist run-now).
        $parts = preg_split('/\s+/', trim($command));

        return $parts[0] ?? null;
    }

    protected function humanExpression(string $expression): string
    {
        return match ($expression) {
            '* * * * *' => 'Setiap menit',
            '0 * * * *' => 'Setiap jam',
            '0 0 * * *' => 'Setiap hari (00:00)',
            '0 0 * * 0' => 'Setiap minggu',
            '0 0 1 * *' => 'Setiap bulan',
            default => $expression,
        };
    }
}
