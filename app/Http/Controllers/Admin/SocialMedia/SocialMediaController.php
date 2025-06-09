<?php

namespace App\Http\Controllers\Admin\SocialMedia;

use App\Http\Controllers\Controller;
use App\Services\SocialMediaService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SocialMediaController extends Controller
{
    use AdminView;

    public function __construct(
        public SocialMediaService $service
    ) {
        $this->setView('admin.pages.social-media');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function create()
    {
        return $this->view('create');
    }

    public function data(Request $request)
    {
        try {

            return $this->service
                ->setRequest($request)
                ->dataTable();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {

            $socialMedia = $this->service->findSocialMedia($id);
            return $this->view('edit', compact('socialMedia'));
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->route('admin.social_media.index');
        }
    }

    public function update(Request $request, string $id)
    {
        $service = $this->service->setRequest($request);

        $service->validate(true);

        try {

            $service->update($id);

            cache()->forget('social_media');

            Alert::success('Success!', 'Social media berhasil diupdate.');

            return redirect()->route('admin.social_media.index');
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $service = $this->service->setRequest($request);

        $service->validate();

        try {

            $service->create();

            cache()->forget('social_media');

            Alert::success('Success!', 'Social media berhasil disimpan.');

            return redirect()->route('admin.social_media.index');
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }
}
