<?php

namespace App\Http\Controllers\Frontend\Pages;

use App\Traits\FrontView;
use App\Facades\PageFacade;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\GalleryService;
use App\Http\Controllers\Controller;

class GalleryController extends Controller
{
    use FrontView;

    public function __construct(
        private GalleryService $gallery
    ) {}

    public function index()
    {
        $data['page'] = PageFacade::page('galeri');
        return $this->view('galeri', $data);
    }

    public function show($slug)
    {
        $gallery = $this->gallery->findBySlug($slug);

        $data['page'] = PageFacade::createPage([
            'id' => 'detail-gallery',
            'meta' => [
                'title' => $gallery->title,
                'type' => 'Article',
                'keywords' => $gallery->keywords,
                'description' => $gallery->description ?? Str::limit(strip_tags($gallery->content), 150),
                'url_image' => image_url('galleries', $gallery->image),
                'image' => image_url('galleries', $gallery->image)
            ],
            'title' => $gallery->title,
            'custom_meta' => [],
            'styles' => [],
            'scripts' => [],
            'sections' => []
        ])
            ->registerSections('detail-gallery', [
                'detail-gallery'
            ])
            ->page('detail-gallery')
            ->setCollectionSection('detail-gallery', $gallery);

        return $this->view('detail-gallery', $data);
    }
}
