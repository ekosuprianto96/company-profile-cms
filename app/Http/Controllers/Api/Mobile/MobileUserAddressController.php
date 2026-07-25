<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\MobileUserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileUserAddressController extends ApiController
{
    /** Aturan validasi region {code,name} opsional per tingkat. */
    private function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:60'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'recipient_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'province' => ['nullable', 'array'],
            'regency' => ['nullable', 'array'],
            'district' => ['nullable', 'array'],
            'village' => ['nullable', 'array'],
            'region_label' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }

    private function payload(MobileUserAddress $a): array
    {
        return [
            'id' => $a->id,
            'label' => $a->label,
            'recipient_name' => $a->recipient_name,
            'recipient_phone' => $a->recipient_phone,
            'address' => $a->address,
            'province' => $a->province,
            'regency' => $a->regency,
            'district' => $a->district,
            'village' => $a->village,
            'region_label' => $a->region_label,
            'latitude' => $a->latitude,
            'longitude' => $a->longitude,
            'is_primary' => (bool) $a->is_primary,
        ];
    }

    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->get()->map(fn ($a) => $this->payload($a))->values();

        return $this->success(['addresses' => $addresses], 'Daftar alamat berhasil dimuat.');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate($this->rules());
            $user = $request->user();

            $address = DB::transaction(function () use ($user, $data) {
                // Alamat pertama otomatis jadi utama.
                $makePrimary = ! empty($data['is_primary']) || $user->addresses()->count() === 0;
                if ($makePrimary) {
                    $user->addresses()->update(['is_primary' => false]);
                }

                return $user->addresses()->create(array_merge($data, ['is_primary' => $makePrimary]));
            });

            return $this->success(['address' => $this->payload($address)], 'Alamat berhasil ditambahkan.', 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Data alamat belum lengkap.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return $this->error('Gagal menyimpan alamat.', 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);
            $data = $request->validate($this->rules());

            DB::transaction(function () use ($user, $address, $data) {
                if (! empty($data['is_primary'])) {
                    $user->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
                } else {
                    // Jangan biarkan user menonaktifkan satu-satunya alamat utama.
                    if ($address->is_primary) {
                        $data['is_primary'] = true;
                    }
                }
                $address->update($data);
            });

            return $this->success(['address' => $this->payload($address->fresh())], 'Alamat berhasil diperbarui.');
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Data alamat belum lengkap.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return $this->error('Alamat tidak ditemukan atau gagal diperbarui.', 404);
        }
    }

    public function setPrimary(Request $request, int $id)
    {
        try {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);

            DB::transaction(function () use ($user, $address) {
                $user->addresses()->update(['is_primary' => false]);
                $address->update(['is_primary' => true]);
            });

            return $this->success(['address' => $this->payload($address->fresh())], 'Alamat utama diperbarui.');
        } catch (\Throwable $th) {
            return $this->error('Alamat tidak ditemukan.', 404);
        }
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);
            $wasPrimary = $address->is_primary;

            DB::transaction(function () use ($user, $address, $wasPrimary) {
                $address->delete();
                // Bila alamat utama dihapus, angkat alamat lain sebagai utama.
                if ($wasPrimary) {
                    $next = $user->addresses()->orderByDesc('id')->first();
                    $next?->update(['is_primary' => true]);
                }
            });

            return $this->success([], 'Alamat berhasil dihapus.');
        } catch (\Throwable $th) {
            return $this->error('Alamat tidak ditemukan.', 404);
        }
    }
}
