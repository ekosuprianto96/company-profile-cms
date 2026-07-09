<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileMapsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapsController extends ApiController
{
    public function __construct(
        protected MobileMapsService $mobileMapsService
    ) {}

    public function autocomplete(Request $request)
    {
        $validated = $request->validate([
            'input' => 'required|string|min:3|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'session_token' => 'nullable|string|max:255',
        ]);

        try {
            return $this->success([
                'predictions' => $this->mobileMapsService->autocomplete(
                    $validated['input'],
                    $validated['session_token'] ?? null,
                    isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                    isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                ),
            ], 'Saran alamat berhasil dimuat.');
        } catch (\Throwable $throwable) {
            Log::error('Mobile maps autocomplete error: ' . $throwable->getMessage(), [
                'stack' => $throwable->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat saran alamat.', 500);
        }
    }

    public function resolve(Request $request)
    {
        $validated = $request->validate([
            'place_id' => 'required|string|max:255',
            'session_token' => 'nullable|string|max:255',
        ]);

        try {
            return $this->success([
                'location' => $this->mobileMapsService->resolvePlace(
                    $validated['place_id'],
                    $validated['session_token'] ?? null,
                ),
            ], 'Detail lokasi berhasil dimuat.');
        } catch (\Throwable $throwable) {
            Log::error('Mobile maps resolve place error: ' . $throwable->getMessage(), [
                'stack' => $throwable->getTraceAsString(),
            ]);

            return $this->error('Gagal mengambil detail lokasi.', 500);
        }
    }

    public function reverseGeocode(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            return $this->success([
                'location' => $this->mobileMapsService->reverseGeocode(
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                ),
            ], 'Alamat berhasil dimuat.');
        } catch (\Throwable $throwable) {
            Log::error('Mobile maps reverse geocode error: ' . $throwable->getMessage(), [
                'stack' => $throwable->getTraceAsString(),
            ]);

            return $this->error('Gagal mengambil alamat lokasi.', 500);
        }
    }
}
