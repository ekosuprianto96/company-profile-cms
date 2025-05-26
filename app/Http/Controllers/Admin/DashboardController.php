<?php

namespace App\Http\Controllers\Admin;

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

    public function index() {
        return $this->view('index');
    }
}
