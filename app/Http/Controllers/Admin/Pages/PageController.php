<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Traits\AdminView;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\PageAdminService;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class PageController extends Controller
{
    use AdminView;

    public function __construct(
        protected PageAdminService $pageAdminService
    ) {
        $this->setView('admin.pages.setting-page');
    }

    public function index($page)
    {
        try {
            $page = $this->pageAdminService->page($page);
            $dataTabs = $page->getTabs();
            $data['sections'] = $page->getSections();
            $data['tabs'] = $dataTabs['tabs'];
            $data['targets'] = $dataTabs['targets'];

            return $this->view('index', $data);
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }
    }

    public function storePage(Request $request, $id, $type)
    {
        try {

            $page = $this->pageAdminService->page($id);
            $page->initialRequest($request, $type)
                ->save();

            Alert::success('Sukses', 'Data berhasil disimpan!');

            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }
    }
}
