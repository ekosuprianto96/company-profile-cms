<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Support\Str;
use App\Repositories\BannerRepository;
use App\Http\Requests\BannerStoreRequest;
use App\Http\Requests\BannerUpdateRequest;

class BannerService
{
    public function __construct(
        protected BannerRepository $banner
    ) {}

    public function all()
    {
        return $this->banner->getAll();
    }

    public function getActiveBanners()
    {
        return $this->banner->where('an', 1)->latest()->get();
    }

    public function create(BannerStoreRequest $request)
    {
        if (isset($request->file_name)) {
            $pathTemps = 'assets/images/temps/' . $request->file_name;
            $pathBanners = 'assets/images/banners/' . $request->file_name;

            if (file_exists(public_path($pathTemps))) {
                rename(public_path($pathTemps), public_path($pathBanners));
            }
        }

        return $this->banner->create([
            'image_url' => $request->file_name,
            'title' => Str::title($request->title),
            ...$request->only('sub_title', 'an')
        ]);
    }

    public function update(BannerUpdateRequest $request, $id)
    {
        if (isset($request->file_name)) {
            $pathTemps = 'assets/images/temps/' . $request->file_name;
            $pathBanners = 'assets/images/banners/' . $request->file_name;

            if (file_exists(public_path($pathTemps))) {
                rename(public_path($pathTemps), public_path($pathBanners));
            }
        } else {
            $request->merge([
                'file_name' => $this->findBanner($id)->image_url
            ]);
        }

        return $this->banner->update($id, [
            'image_url' => $request->file_name,
            'title' => Str::title($request->title),
            ...$request->only('sub_title', 'an')
        ]);
    }

    public function delete(int $id)
    {
        $banner = $this->findBanner($id);

        if (!$banner) throw new \Exception("Data tidak ditemukan", 404);

        if (file_exists(public_path('assets/images/banners/' . $banner->image_url))) {
            unlink(public_path('assets/images/banners/' . $banner->image_url));
        }

        return $this->banner->delete($id);
    }

    public function findBanner($id)
    {
        return $this->banner->find($id);
    }
}
