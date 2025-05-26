<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RobotsService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class RobotsController extends Controller
{
    use AdminView;
    public function __construct(
        protected RobotsService $robotsService
    ) {
        $this->setView('admin.pages.robots');
    }

    public function index()
    {
        $data['robots'] = $this->robotsService->read();
        return $this->view('index', $data);
    }

    public function update(Request $request)
    {
        try {

            $robots = $request->robots;
            $this->robotsService->update($robots);

            Alert::success('Sukses!', 'Robots berhasil di update.');
            return redirect()->back();
        } catch (\Throwable $th) {

            Alert::error('Error!', $th->getMessage());
            return redirect()->back();
        }
    }
}
