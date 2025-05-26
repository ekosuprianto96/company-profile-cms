<?php

namespace App\Http\Controllers\Admin\Sitemap;

use App\Http\Controllers\Controller;
use App\Services\SitemapService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SitemapController extends Controller
{
    use AdminView;

    public function __construct(
        protected SitemapService $sitemapService
    ) {
        $this->setView('admin.pages.sitemap');
    }

    public function index()
    {
        try {
            $data['info'] = $this->sitemapService->getSitemapInfo();
            $data['lastMod'] = $this->sitemapService->lastModified();
        } catch (\Exception) {
            $data['info'] = null;
            $data['lastMod'] = null;
        }

        return $this->view('index', $data);
    }

    public function generate(Request $request)
    {
        try {

            $this->sitemapService
                ->addModel(function ($value) {
                    return route('blog.show', $value->slug);
                }, 0.8, \App\Models\Blog::class)
                ->addModel(function ($value) {
                    return route('galeri.show', $value->slug);
                }, 0.6, \App\Models\Gallery::class)
                ->addModel(function ($value) {
                    return route('layanan.show', $value->slug);
                }, 0.6, \App\Models\Layanan::class)
                ->addSite('/', 1.0)
                ->addSite('/layanan', 0.8)
                ->addSite('/tentang-kami', 0.6)
                ->addSite('/galeri', 0.6)
                ->addSite('/blog', 0.8)
                ->addSite('/kontak')
                ->generate();
            // ->appendStyleSheet();

            Alert::success('Sukses!', 'Sitemap berhasil di generate.');

            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }
}
