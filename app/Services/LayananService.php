<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Repositories\LayananRepository;
use App\Http\Requests\LayananStoreRequest;
use App\Http\Requests\LayananUpdateRequest;

class LayananService
{
    public function __construct(
        public LayananRepository $layanan
    ) {}

    public function all()
    {
        return $this->layanan->getAll();
    }

    public function findLayanan(int $id)
    {
        return $this->layanan->find($id);
    }

    public function findBySlug(string $slug)
    {
        return $this->layanan->findBySlug($slug);
    }

    public function getActiveLayanan()
    {
        // return $this->layanan->where('an', 1)->get();
        return collect([
            [
                'title' => 'Layanan 1',
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos, voluptatum.'
            ],
            [
                'title' => 'Layanan 2',
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsam at sunt error tempora rem asperiores amet odit cum nemo. Laboriosam!'
            ],
            [
                'title' => 'Layanan 3',
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates fugiat facere sed.'
            ],
            [
                'title' => 'Layanan 4',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae fuga voluptate, quam minima a iusto.'
            ],
            [
                'title' => 'Layanan 5',
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos, voluptatum.'
            ],
            [
                'title' => 'Layanan 6',
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos, voluptatum.'
            ],
        ]);
    }

    public function create(LayananStoreRequest $request)
    {
        if (isset($request->file_name)) {
            $pathTemps = 'assets/images/temps/' . $request->file_name;
            $pathBanners = 'assets/images/services/' . $request->file_name;

            if (!is_dir(public_path('assets/images/services/'))) {
                mkdir(public_path('assets/images/services/'), 0777, true);
            }

            if (file_exists(public_path($pathTemps))) {
                rename(public_path($pathTemps), public_path($pathBanners));
            }
        }

        if (isset($request->url_icon) && !empty($request->url_icon)) {
            if ($request->type_icon == 'image') {
                $pathTemps = 'assets/images/temps/' . $request->url_icon;
                $pathIcon = 'assets/images/services/' . $request->url_icon;

                if (!is_dir(public_path('assets/images/services/'))) {
                    mkdir(public_path('assets/images/services/'), 0777, true);
                }

                if (file_exists(public_path($pathTemps))) {
                    rename(public_path($pathTemps), public_path($pathIcon));
                }
            }
        }

        return $this->layanan->create([
            'image' => $request->file_name,
            'type' => $request->type_icon,
            'url_image' => ($request->type_icon == 'image' && isset($request->url_icon)) ? $request->url_icon : null,
            'title' => Str::title($request->title),
            'slug' => Str::slug($request->title),
            'keywords' => implode(',', $request->keywords),
            'icon' => $request->icon ?? '',
            ...$request->only('content', 'an')
        ]);
    }

    public function update(LayananUpdateRequest $request, int $id)
    {

        if (isset($request->file_name)) {
            $pathTemps = 'assets/images/temps/' . $request->file_name;
            $pathBanners = 'assets/images/services/' . $request->file_name;

            if (file_exists(public_path($pathTemps))) {
                rename(public_path($pathTemps), public_path($pathBanners));
            }

            $this->removeImage($this->findLayanan($id)->image ?? 'xxxxx.jpg');
        } else {
            $request->merge([
                'file_name' => $this->findLayanan($id)->image ?? null
            ]);
        }

        if (isset($request->url_icon) && !empty($request->url_icon)) {
            if ($request->type_icon == 'image') {
                $pathTemps = 'assets/images/temps/' . $request->url_icon;
                $pathIcon = 'assets/images/services/' . $request->url_icon;

                if (!is_dir(public_path('assets/images/services/'))) {
                    mkdir(public_path('assets/images/services/'), 0777, true);
                }

                if (file_exists(public_path($pathTemps))) {
                    rename(public_path($pathTemps), public_path($pathIcon));
                }
            }
        } else {
            $request->merge([
                'url_icon' => $this->findLayanan($id)->url_image ?? null
            ]);
        }


        $update = $this->layanan->find($id);

        if ($request->type == 'icon') {
            $this->removeImage($update->url_image);
        } else {
            if (isset($request->url_icon) && !empty($request->url_icon) && !empty($update->url_image)) {
                $this->removeImage($update->url_image);
            }
        }

        return $this->layanan->update($id, [
            'image' => $request->file_name,
            'type' => $request->type_icon,
            'url_image' => ($request->type_icon == 'image' && isset($request->url_icon)) ? $request->url_icon : null,
            'title' => Str::title($request->title),
            'slug' => Str::slug($request->title),
            'keywords' => implode(',', $request->keywords),
            'icon' => $request->icon ?? '',
            ...$request->only('content', 'an')
        ]);
    }

    public function removeImage(string $filename)
    {
        $existsFile = file_exists(public_path('assets/images/services/' . $filename));
        if ($existsFile) {
            unlink(public_path('assets/images/services/' . $filename));
        }

        return true;
    }

    public function delete(int $id)
    {
        $delete = $this->layanan->find($id);
        return $this->layanan->delete($id);
    }
}
