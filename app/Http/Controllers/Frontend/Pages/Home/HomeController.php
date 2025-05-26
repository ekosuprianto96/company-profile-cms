<?php

namespace App\Http\Controllers\Frontend\Pages\Home;

use App\Facades\PageFacade;
use App\Http\Controllers\Controller;
use App\Services\BannerService;
use App\Services\GalleryService;
use App\Services\LayananService;
use App\Traits\FrontView;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use FrontView;

    public function __construct() {}

    public function index(): \Illuminate\Contracts\View\View
    {
        $data['page'] = PageFacade::page('home');
        return $this->view('home', $data);
    }
}
