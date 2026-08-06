<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;

class SupplierAdminService
{
    public function queryForAdmin(): Builder
    {
        return Supplier::query()->withCount('products')->orderBy('name');
    }

    public function find(int $id): Supplier
    {
        return Supplier::findOrFail($id);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);

        return $supplier;
    }

    public function delete(int $id): bool
    {
        // supplier_id di products di-set null otomatis (nullOnDelete) — produk tetap ada.
        return (bool) Supplier::findOrFail($id)->delete();
    }

    /** Daftar suplier aktif untuk dropdown di form produk. */
    public function activeForSelect()
    {
        return Supplier::active()->orderBy('name')->get(['id', 'name']);
    }
}
