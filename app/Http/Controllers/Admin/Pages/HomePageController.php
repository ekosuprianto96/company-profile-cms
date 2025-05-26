<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Http\Controllers\Controller;
use App\Services\HomePageService;
use App\Traits\AdminView;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    use AdminView;

    public function __construct(
        protected HomePageService $homePageService
    ) {
        $this->setView('admin.pages.home');
    }

    public function index()
    {
        try {

            $blocks = $this->homePageService->getBlocks();
            return $this->view('index', $blocks);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
