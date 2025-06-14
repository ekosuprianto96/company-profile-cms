<?php

namespace App\Http\Controllers\Frontend\Pages\Blogs;

use App\Traits\FrontView;
use App\Facades\PageFacade;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BlogService;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class BlogController extends Controller
{
    use FrontView;

    public function __construct(
        protected BlogService $blogService
    ) {}

    public function index(Request $request, $kategori = null)
    {
        if ($request->kategori) {
            $data['kategori_slug'] = $request->kategori;
        }

        if (!empty($request->search ?? '')) {
            $data['search'] = $request->search;
        }

        $data['page'] = PageFacade::page('blog');
        return $this->view('blog', $data);
    }

    public function show($slug)
    {
        try {

            $blog = $this->blogService->findBySlug($slug);

            if ($blog) {
                $this->blogService->incerementViews($slug);
            }

            $data['page'] = PageFacade::createPage([
                'id' => 'single-post',
                'meta' => [
                    'title' => $blog->title,
                    'type' => 'Article',
                    'keywords' => $blog->keywords,
                    'description' => $blog->description ?? Str::limit(strip_tags($blog->content), 150),
                    'url_image' => image_url('blogs', $blog->thumbnail),
                    'image' => image_url('blogs', $blog->thumbnail)
                ],
                'title' => $blog->title,
                'custom_meta' => [],
                'styles' => [],
                'scripts' => [],
                'sections' => []
            ])
                ->registerSections('single-post', [
                    'detail-post',
                    'input-search',
                    'list-kategori',
                    'list-latest-post',
                ])
                ->page('single-post')
                ->setCollectionSection('detail-post', $blog);

            return $this->view('single-post', $data);
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }
    }
}
