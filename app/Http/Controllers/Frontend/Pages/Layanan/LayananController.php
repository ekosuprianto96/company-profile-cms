<?php

namespace App\Http\Controllers\Frontend\Pages\Layanan;

use App\Traits\FrontView;
use App\Facades\PageFacade;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\LayananService;
use App\Services\RekomendasiKavlingService;

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

    public function showWidget($slug, $widget)
    {
        $widget = app(RekomendasiKavlingService::class)
            ->findKavlingBySlug($widget);

        if(!$widget) {
            abort(404);
        }

        $data['page'] = PageFacade::createPage([
            'id' => 'detail-widget',
            'meta' => [
                'title' => $widget->title,
                'type' => 'Article',
                'keywords' => 'kavling,rumah,pembangunan,bangunan,maninjau,studio,konstruksi,besi,baja',
                'description' => $layanan->description ?? Str::limit(strip_tags($widget->content), 150),
                'url_image' => image_url('rekomenasi-kavling', $widget->images()[0] ?? ''),
                'image' => image_url('rekomenasi-kavling', $widget->images()[0] ?? ''),
            ],
            'title' => $widget->title,
            'custom_meta' => [],
            'styles' => [],
            'scripts' => [],
            'sections' => []
        ])
            ->registerSections('detail-widget', [
                'detail-widget'
            ])
            ->page('detail-widget')
            ->setCollectionSection('detail-widget', $widget);
        
        return $this->view('detail-widget', $data);
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
                'list-rekomedasi-kavling',
                'detail-layanan',
                'contact-us-sidebar',
            ])
            ->page('detail-layanan')
            ->setCollectionSection('detail-layanan', $layanan);
        
        return $this->view('detail-layanan', $data);
    }
}
