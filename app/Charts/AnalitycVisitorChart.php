<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class AnalitycVisitorChart
{

    public function __construct(
        public LarapexChart $chart
    ) {}

    public function build()
    {
        return $this->chart->barChart()
            ->setTitle('Total Pengunjung Perbulan')
            ->setSubtitle('Tahun : ' . date('Y'))
            ->addData('Pengunjung', [700, 300, 800, 200, 600, 400])
            ->setXAxis($this->getMonths());
    }

    public function getMonths()
    {
        $months = collect(range(0, 5))->map(function ($i) {
            return now('Asia/Jakarta')->subMonths($i)->translatedFormat('F');
        })->reverse()->values()->toArray();

        return $months;
    }
}
