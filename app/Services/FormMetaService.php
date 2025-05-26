<?php

namespace App\Services;

use App\Facades\PageFacade;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class FormMetaService
{
    public function __construct(
        protected Request $request,
        protected string $page,
        protected string $type,
        protected $currentPage = null
    ) {
        $this->setPage($this->page);
    }

    public function setPage(string $page = ''): self
    {
        $this->currentPage = PageFacade::page($page);
        return $this;
    }

    public function save()
    {
        $validated = $this->validatedInput();

        if ($validated) {
            if ($validated->hasFile('image')) {

                $this->deleteOldImage();

                $file = $validated->file('image');
                $ext = $file->getClientOriginalExtension();
                $newName = now()->format('Ymd') . '-' . Str::uuid() . '.' . $ext;

                $file->move(public_path('assets/images/pages'), $newName);

                $validated->merge([
                    'url_image' => asset('assets/images/pages/' . $newName)
                ]);
            } else {
                $validated->merge([
                    'url_image' => $this->currentPage->meta['image']
                ]);
            }

            $this->currentPage->setMeta(
                $validated->only('title', 'description', 'keywords', 'url_image')
            );

            $pages = $this->readConfig();
            if (count($pages) > 0) {
                foreach ($pages as $key => $page) {
                    if ($page['id'] != $this->currentPage->id) {
                        continue;
                    }

                    $pages[$key] = $this->currentPage->getConfig();
                    file_put_contents(base_path('config/page.json'), json_encode($pages, JSON_PRETTY_PRINT));
                }
            }
        }
    }

    public function validatedInput()
    {
        if (count($this->request->all()) > 0) {
            $this->request->validate([
                'title' => 'required|string|max:150',
                'description' => 'required|string|max:255',
                'keywords' => 'required|array',
                'image' => 'image|mimes:jpg,jpeg,svg,png,webp|max:2000'
            ], [
                'title.required' => 'Meta title harus diisi.',
                'title.string' => 'Meta title harus berupa string yang valid.',
                'title.max' => 'Meta title maximal 150 karakter.',

                'description.required' => 'Meta deskripsi tidak boleh kosong.',
                'description.string' => 'Meta deskripsi harus berupa string yang valid.',
                'description.max' => 'Meta deskripsi maximal 255 karakter.',

                'keywords.required' => 'Meta Keywords harus diisi.',
                'keywords.array' => 'Meta Keywords harus lebih atau sama dengan 1 item.',

                'image.image' => 'Meta image harus berupa gambar.',
                'image.mimes' => 'Gambar yang diperbolehkan hanya (.jpg, .jpeg, .png, .svg, .webp)',
                'image.max' => 'Gambar maximal ukuran 2MB'
            ]);

            return $this->request;
        }

        return null;
    }

    public function deleteOldImage()
    {
        if (!empty($this->currentPage->meta()->image ?? '')) {
            $relativePath = str_replace(asset('/'), '', $this->currentPage->meta()->image);
            if (file_exists(public_path($relativePath))) {
                unlink($relativePath);
            }
        }
    }

    public function readConfig()
    {
        $pageConfig = file_get_contents(base_path('config/page.json'));
        $toArray = json_decode($pageConfig, true);
        return $toArray;
    }
}
