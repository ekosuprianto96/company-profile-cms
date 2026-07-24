<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\HomeSectionResolver;
use Illuminate\Support\Facades\Log;

class HomeSectionController extends ApiController
{
    public function __construct(
        protected HomeSectionResolver $resolver
    ) {}

    public function index()
    {
        try {
            return $this->success([
                'sections' => $this->resolver->feed(),
            ], 'Section home berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load home sections error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat section home.', 500);
        }
    }
}
