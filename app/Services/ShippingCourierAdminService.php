<?php

namespace App\Services;

use App\Models\ShippingCourier;

class ShippingCourierAdminService
{
    public function queryForAdmin()
    {
        return ShippingCourier::query()->orderBy('sort_order')->orderBy('id');
    }

    public function find(int $id): ShippingCourier
    {
        return ShippingCourier::query()->findOrFail($id);
    }

    public function create(array $payload): ShippingCourier
    {
        return ShippingCourier::create($payload);
    }

    public function update(int $id, array $payload): ShippingCourier
    {
        $courier = $this->find($id);
        $courier->update($payload);

        return $courier->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }
}
