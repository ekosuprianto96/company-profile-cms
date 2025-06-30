<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repositories\RekomendasiKavlingRepository;

class RekomendasiKavlingService
{
    public function __construct(
        private ?Request $request = null,
        private RekomendasiKavlingRepository $rekomendasiKavlingRepository
    ) {}

    public function setRequest(Request $request) {
        $this->request = $request;

        return $this;
    }

    public function delete(int $id) {
        $rekomendasi = $this->rekomendasiKavlingRepository->find($id);
        if(!$rekomendasi) throw new \Exception('Data tidak ditemukan');

        $imagesRemoved = $rekomendasi->images();
        if(count($imagesRemoved) > 0) {
            foreach($imagesRemoved as $image) {
                $path = public_path('assets/images/rekomendasi-kavling/' . $image);
                if(file_exists($path)) {
                    unlink($path);
                }
            }
        }

        return $rekomendasi->delete();
    }

    public function update(int $id) {

        $arrayImages = [];
        $rekomendasi = $this->rekomendasiKavlingRepository->find($id);

        if(!$rekomendasi) throw new \Exception('Data tidak ditemukan');

        $fixedImages = $this->request->fixed_images;
        $currentImages = $rekomendasi->images();
        $imagesRemoved = array_values(array_diff($currentImages, $fixedImages));
        $filteredImages = array_filter($currentImages, function ($image) use ($imagesRemoved) {
            return !in_array($image, $imagesRemoved);
        });
       
        if(count($imagesRemoved) > 0) {
            foreach($imagesRemoved as $image) {
                $path = public_path('assets/images/rekomendasi-kavling/' . $image);
                if(file_exists($path)) {
                    unlink($path);
                }
            }
        }
        
        if($this->request->hasFile('images')) {

            $images = collect($this->request->images)
            ->filter(function ($image) use ($fixedImages) {
                return in_array($image->getClientOriginalName(), $fixedImages);
            })->values();

            if(count($images) > 0) {
                foreach($images as $image) {
                    $extension = $image->getClientOriginalExtension();
                    $newName = now()->format('ymd') . '-' . Str::uuid() . '.' . $extension;

                    $image->move(public_path('assets/images/rekomendasi-kavling/'), $newName);
                    $arrayImages[] = $newName;
                }
            }
        }

        $paramsStore = [
            'title' => Str::title($this->request->title),
            'slug' => Str::slug($this->request->title),
            'content' => $this->request->content,
            'images' => json_encode([...$filteredImages, ...$arrayImages])
        ];
        
        return $rekomendasi->update($paramsStore);
    }

    public function store() {

        $arrayImages = [];
        if($this->request->hasFile('images')) {
            $fixedImages = $this->request->fixed_images;

            $images = collect($this->request->images)
            ->filter(function ($image) use ($fixedImages) {
                return in_array($image->getClientOriginalName(), $fixedImages);
            })->values();

            if(count($images) > 0) {
                foreach($images as $image) {
                    $extension = $image->getClientOriginalExtension();
                    $newName = now()->format('ymd') . '-' . Str::uuid() . '.' . $extension;

                    $image->move(public_path('assets/images/rekomendasi-kavling/'), $newName);
                    $arrayImages[] = $newName;
                }
            }
        }

        $paramsStore = [
            'title' => Str::title($this->request->title),
            'slug' => Str::slug($this->request->title),
            'content' => $this->request->content,
            'images' => json_encode($arrayImages)
        ];
        
        return $this->rekomendasiKavlingRepository->create($paramsStore);
    }

    public function getAll() {
        return $this->rekomendasiKavlingRepository->getAll();
    }

    public function findKavling($id) {
        return $this->rekomendasiKavlingRepository->find($id);
    }

    public function findKavlingBySlug(string $slug) {
        return $this->rekomendasiKavlingRepository->where('slug', $slug)->first();
    }
}
