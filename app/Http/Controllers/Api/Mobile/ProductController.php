<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileProductCatalogService;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function __construct(
        protected MobileProductCatalogService $catalog
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'service_id' => ['nullable', 'integer'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,popular,rating'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $paginator = $this->catalog->list($validated);

        return $this->success([
            'products' => collect($paginator->items())->map(fn ($p) => $this->catalog->listItem($p))->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
                'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
                'total' => $paginator->total(),
            ],
        ], 'OK');
    }

    public function show(Request $request, string $slug)
    {
        $product = $this->catalog->findBySlug($slug);

        if (! $product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        return $this->success(['product' => $this->catalog->detail($product)], 'OK');
    }

    public function categories()
    {
        return $this->success(['categories' => $this->catalog->categories()], 'OK');
    }
}
