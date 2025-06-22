<?php

namespace App\Http\Controllers\Frontend\Pages\Layanan;

use App\Traits\FrontView;
use App\Facades\PageFacade;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\LayananService;

class LayananController extends Controller
{
    use FrontView;

    public function __construct(
        private LayananService $layanan
    ) {}

    public function index()
    {
        $data['page'] = PageFacade::page('layanan');
        return $this->view('layanan', $data);
    }

    public function show($slug)
    {
        $layanan = $this->layanan->findBySlug($slug);

        $data['page'] = PageFacade::createPage([
            'id' => 'detail-layanan',
            'meta' => [
                'title' => $layanan->title,
                'type' => 'Article',
                'keywords' => $layanan->keywords,
                'description' => $layanan->description ?? Str::limit(strip_tags($layanan->content), 150),
                'url_image' => image_url('services', $layanan->image),
                'image' => image_url('services', $layanan->image)
            ],
            'title' => $layanan->title,
            'custom_meta' => [],
            'styles' => [],
            'scripts' => [],
            'sections' => []
        ])
            ->registerSections('detail-layanan', [
                'detail-layanan',
                'contact-us-sidebar'
            ])
            ->page('detail-layanan')
            ->setCollectionSection('detail-layanan', $layanan);

        return $this->view('detail-layanan', $data);
    }
}
