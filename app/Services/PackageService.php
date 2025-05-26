<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repositories\PackageRepository;

class PackageService
{
    public function __construct(
        private PackageRepository $package,
        private ?Request $request = null
    ) {}

    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    public function findPaket(string $id)
    {
        return $this->package->findPackage($id);
    }

    public function getAllPackage(?callable $closure = null)
    {
        return $this->package->gatAllPackage($closure);
    }

    public function create()
    {
        return $this->package->create([
            'id' => Str::uuid(),
            ...$this->request->only('name', 'description', 'price', 'is_recommended', 'show_price', 'features')
        ]);
    }

    public function update(string $id)
    {
        return $this->package->updatePackage($id, $this->request->all());
    }

    public function delete()
    {
        $package = $this->package->findPackage($this->request->id);

        if (!$package) {
            throw new \Exception('Paket harga tidak ditemukan.', 404);
        }

        return $this->package->deletePackage($this->request->id);
    }
}
