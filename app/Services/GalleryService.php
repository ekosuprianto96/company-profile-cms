<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Repositories\GalleryRepository;
use App\Http\Requests\GalleryStoreRequest;
use App\Http\Requests\GalleryUpdateRequest;

class GalleryService
{

    public function __construct(
        protected GalleryRepository $gallery
    ) {}

    public function all()
    {
        return $this->gallery->getAll();
    }

    public function findBySlug(string $slug)
    {
        return $this->gallery->findBySlug($slug);
    }

    public function getActiveGalleries()
    {
        return $this->gallery->where('an', 1)->limit(5)->latest()->get();
    }

    public function findGallery($id)
    {
        return $this->gallery->find($id);
    }

    public function create(GalleryStoreRequest $request)
    {
        if (isset($request->file_name)) {
            $pathTemps = 'assets/images/temps/' . $request->file_name;
            $pathBanners = 'assets/images/galleries/' . $request->file_name;

            if (!is_dir(public_path('assets/images/galleries/'))) {
                mkdir(public_path('assets/images/galleries/'), 0777, true);
            }

            if (file_exists(public_path($pathTemps))) {
                rename(public_path($pathTemps), public_path($pathBanners));
            }
        }

        return $this->gallery->create([
            'image' => $request->file_name,
            'slug' => Str::slug($request->title),
            'title' => Str::title($request->title),
            'keywords' => implode(',', $request->keywords),
            ...$request->only('content', 'an')
        ]);
    }

    public function update(GalleryUpdateRequest $request, $id)
    {
        if (isset($request->file_name) && !empty($request->file_name)) {
            $pathTemps = 'assets/images/temps/' . $request->file_name;
            $pathBanners = 'assets/images/galleries/' . $request->file_name;

            if (file_exists(public_path($pathTemps))) {
                rename(public_path($pathTemps), public_path($pathBanners));
            }
        } else {
            $request->merge([
                'file_name' => $this->findGallery($id)->image
            ]);
        }

        return $this->gallery->update($id, [
            'image' => $request->file_name,
            'slug' => Str::slug($request->title),
            'title' => Str::title($request->title),
            'keywords' => implode(',', $request->keywords),
            ...$request->only('content', 'an')
        ]);
    }

    public function delete(int $id)
    {
        $gallery = $this->findGallery($id);

        if (!$gallery) throw new \Exception("Data tidak ditemukan", 404);

        if (file_exists(public_path('assets/images/galleries/' . $gallery->image))) {
            unlink(public_path('assets/images/galleries/' . $gallery->image));
        }

        return $this->gallery->delete($id);
    }
}
