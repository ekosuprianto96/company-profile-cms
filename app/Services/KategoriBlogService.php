<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Http\Requests\KategoriStoreRequest;
use App\Repositories\KategoriBlogRepository;

class KategoriBlogService
{
    public function __construct(
        protected KategoriBlogRepository $kategori
    ) {}

    public function all()
    {
        return $this->kategori->all();
    }

    public function create(KategoriStoreRequest $request)
    {
        return $this->kategori->create([
            'slug' => Str::slug($request->name),
            ...$request->only('name', 'an')
        ]);
    }

    public function findKategori($id)
    {
        return $this->kategori->find($id);
    }

    public function update(KategoriStoreRequest $request, $id)
    {
        $kategori = $this->findKategori($id);
        return $kategori->update([
            'slug' => Str::slug($request->name),
            ...$request->only('name', 'an')
        ]);
    }

    public function delete(int $id)
    {
        $kategori = $this->findKategori($id);
        return $kategori->delete();
    }
}
