<?php

namespace App\Charts;

use Illuminate\Support\Facades\DB;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class AnalitycVisitorChart
{

    public function __construct(
        public LarapexChart $chart
    ) {}

    public function build()
    {
        $data = $this->builderQuery();
        return $this->chart->barChart()
            ->setTitle('Total Pengunjung Perbulan')
            ->setSubtitle('Tahun : ' . date('Y'))
            ->addData('Pengunjung', $data)
            ->setXAxis($this->getMonths());
    }

    public function getMonths()
    {
        $months = collect(range(0, 5))->map(function ($i) {
            return now('Asia/Jakarta')->subMonths($i)->translatedFormat('F');
        })->reverse()->values()->toArray();

        return $months;
    }

    public function builderQuery()
    {
        $now = now('Asia/Jakarta');
        $startDate = $now->copy()->subMonths(5)->startOfMonth();
        $endDate = $now->copy()->endOfMonth();

        $sub = DB::table('visitors')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->whereBetween('created_at', [$startDate, $endDate]);

        $rawData = DB::query()
            ->fromSub($sub, 'v')
            ->selectRaw('ym, COUNT(*) as total')
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        // Susun ulang agar setiap bulan pasti ada (isi 0 kalau kosong)
        $data = collect(range(0, 5))->map(function ($i) use ($rawData, $now) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            return $rawData[$key] ?? 0;
        })->reverse()->values()->toArray();

        return $data;
    }
}
