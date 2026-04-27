<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileBlogCatalogService;
use Illuminate\Support\Facades\Log;

class BlogController extends ApiController
{
    public function __construct(
        protected MobileBlogCatalogService $mobileBlogCatalogService
    ) {}

    public function index()
    {
        try {
            return $this->success([
                'blogs' => $this->mobileBlogCatalogService->all(),
                'featured' => $this->mobileBlogCatalogService->featured(),
                'categories' => $this->mobileBlogCatalogService->categories(),
            ], 'Daftar blog berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile blog error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat blog.', 500);
        }
    }

    public function show(string $slug)
    {
        try {
            $blog = $this->mobileBlogCatalogService->findBySlug($slug);

            if (! $blog) {
                return $this->error('Blog tidak ditemukan.', 404);
            }

            return $this->success([
                'blog' => $blog,
            ], 'Detail blog berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile blog detail error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat detail blog.', 500);
        }
    }
}
