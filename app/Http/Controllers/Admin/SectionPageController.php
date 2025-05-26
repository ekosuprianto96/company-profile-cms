<?php

namespace App\Http\Controllers\Admin;

use App\Facades\PageFacade;
use App\Http\Controllers\Controller;
use App\Services\FormService;
use App\Services\PageAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SectionPageController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct()
    {
        $this->setView('admin.components.forms');
    }

    public function forms(
        Request $request,
        FormService $forms
    ) {

        try {

            $page = PageFacade::page($request->id_page);
            $data['page'] = $page;
            $data['forms'] = new $forms(page: $page, section: $request->id_section);
            $data['id_section'] = $request->id_section;

            return $this->view('section', $data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function updateSection(
        Request $request,
        $page,
        $section,
        FormService $forms
    ) {
        try {

            $findPage = PageFacade::page($page);
            $form = new $forms(page: $findPage, section: $section);
            $form->setRequest($request)
                ->save();

            Alert::success('Sukses!', 'Data section berhasil di update.');

            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }
}
