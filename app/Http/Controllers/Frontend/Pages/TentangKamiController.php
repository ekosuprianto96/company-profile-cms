<?php

namespace App\Http\Controllers\Frontend\Pages;

use App\Traits\FrontView;
use App\Facades\PageFacade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TentangKamiController extends Controller
{
    use FrontView;

    public function index()
    {
        $data['page'] = PageFacade::page('tentang-kami');
        return $this->view('tentang-kami', $data);
    }
}
