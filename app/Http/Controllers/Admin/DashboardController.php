<?php

namespace App\Http\Controllers\Admin;

use App\Charts\AnalitycVisitorChart;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    use AdminView;

    public function __construct()
    {
        $this->setView('admin.pages.dashboard.');
    }

    public function index(AnalitycVisitorChart $chart)
    {
        dd($chart->build());
        return $this->view('index', ['chart' => $chart->build()]);
    }
}
