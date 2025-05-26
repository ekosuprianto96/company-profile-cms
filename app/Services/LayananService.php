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

        return $this->layanan->create([
            'image' => $request->file_name,
            'title' => Str::title($request->title),
            'slug' => Str::slug($request->title),
            'keywords' => implode(',', $request->keywords),
            ...$request->only('content', 'an', 'icon')
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
        } else {
            $request->merge([
                'file_name' => $this->findLayanan($id)->image ?? null
            ]);
        }

        $update = $this->layanan->find($id);
        return $this->layanan->update($id, [
            'image' => $request->file_name,
            'title' => Str::title($request->title),
            'slug' => Str::slug($request->title),
            'keywords' => implode(',', $request->keywords),
            ...$request->only('content', 'an', 'icon')
        ]);
    }

    public function delete(int $id)
    {
        $delete = $this->layanan->find($id);
        return $this->layanan->delete($id);
    }
}
