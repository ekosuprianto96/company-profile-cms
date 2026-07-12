<?php

namespace App\Services;

use App\Models\MobileSupportContact;
use Illuminate\Support\Facades\Auth;

class MobileSupportContactAdminService
{
    public function queryForAdmin()
    {
        return MobileSupportContact::query()->orderBy('sort_order')->orderBy('id');
    }

    public function find(int $id): MobileSupportContact
    {
        return MobileSupportContact::query()->findOrFail($id);
    }

    public function create(array $payload): MobileSupportContact
    {
        return MobileSupportContact::query()->create(array_merge($payload, [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));
    }

    public function update(int $id, array $payload): MobileSupportContact
    {
        $contact = $this->find($id);
        $contact->update(array_merge($payload, ['updated_by' => Auth::id()]));

        return $contact->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }
}
