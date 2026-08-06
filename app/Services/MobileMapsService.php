<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MobileMapsService
{
    public function autocomplete(string $input, ?string $sessionToken = null, ?float $latitude = null, ?float $longitude = null): array
    {
        $body = [
            'input' => $input,
            'includedRegionCodes' => [strtolower(config('services.google_maps.region_code', 'ID'))],
            'languageCode' => config('services.google_maps.language_code', 'id'),
            'regionCode' => strtolower(config('services.google_maps.region_code', 'ID')),
        ];

        if ($sessionToken) {
            $body['sessionToken'] = $sessionToken;
        }

        if ($latitude !== null && $longitude !== null) {
            $body['locationBias'] = [
                'circle' => [
                    'center' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                    'radius' => 30000,
                ],
            ];
        }

        $response = $this->placesClient(
            'suggestions.placePrediction.placeId,suggestions.placePrediction.text.text'
        )->post('https://places.googleapis.com/v1/places:autocomplete', $body);

        $this->throwIfFailed($response, 'Gagal memuat saran alamat dari Google Maps.');

        return collect($response->json('suggestions', []))
            ->map(function (array $suggestion) {
                $prediction = $suggestion['placePrediction'] ?? null;

                if (!is_array($prediction) || !is_string($prediction['placeId'] ?? null)) {
                    return null;
                }

                $label = trim((string) Arr::get($prediction, 'text.text', ''));

                if ($label === '') {
                    return null;
                }

                return [
                    'label' => $label,
                    'place_id' => $prediction['placeId'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function resolvePlace(string $placeId, ?string $sessionToken = null): array
    {
        $query = array_filter([
            'languageCode' => config('services.google_maps.language_code', 'id'),
            'regionCode' => strtolower(config('services.google_maps.region_code', 'ID')),
            'sessionToken' => $sessionToken,
        ]);

        $response = $this->placesClient(
            'id,location,formattedAddress,addressComponents'
        )->get("https://places.googleapis.com/v1/places/{$placeId}", $query);

        $this->throwIfFailed($response, 'Gagal mengambil detail lokasi dari Google Maps.');

        return $this->normalizePlacePayload($response->json());
    }

    public function reverseGeocode(float $latitude, float $longitude): array
    {
        // Cache per koordinat dibulatkan (~11m) selama 1 hari → hemat panggilan Google.
        $cacheKey = sprintf('geocode:%.4f,%.4f', $latitude, $longitude);
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $response = Http::connectTimeout(5)->timeout(6)
            ->acceptJson()
            ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'key' => $this->serverKey(),
                'language' => config('services.google_maps.language_code', 'id'),
                'latlng' => "{$latitude},{$longitude}",
                'region' => strtolower(config('services.google_maps.region_code', 'ID')),
            ]);

        $this->throwIfFailed($response, 'Gagal mengambil alamat dari Google Maps.');

        $status = (string) $response->json('status', '');
        if ($status !== 'OK') {
            throw new RuntimeException('Google reverse geocoding gagal diproses.');
        }

        $result = $response->json('results.0');
        if (!is_array($result)) {
            return [
                'administrative' => [],
                'label' => sprintf('Lat %.6f, Lng %.6f', $latitude, $longitude),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        $normalized = $this->normalizeGeocodeResult($result, $latitude, $longitude);
        \Illuminate\Support\Facades\Cache::put($cacheKey, $normalized, now()->addDay());

        return $normalized;
    }

    protected function placesClient(string $fieldMask)
    {
        return Http::connectTimeout(5)->timeout(6)
            ->acceptJson()
            ->withHeaders([
                'X-Goog-Api-Key' => $this->serverKey(),
                'X-Goog-FieldMask' => $fieldMask,
            ]);
    }

    protected function throwIfFailed(Response $response, string $message): void
    {
        if ($response->successful()) {
            return;
        }

        $googleMessage = trim((string) $response->json('error.message', ''));
        $googleStatus = trim((string) $response->json('error.status', ''));
        $details = [];

        if ($response->status() > 0) {
            $details[] = 'HTTP ' . $response->status();
        }

        if ($googleStatus !== '') {
            $details[] = $googleStatus;
        }

        if ($googleMessage !== '') {
            $details[] = $googleMessage;
        }

        if (empty($details)) {
            $body = trim($response->body());
            if ($body !== '') {
                $details[] = $body;
            }
        }

        $detailSuffix = empty($details) ? '' : ' [' . implode(' | ', $details) . ']';

        throw new RuntimeException($message . $detailSuffix);
    }

    protected function serverKey(): string
    {
        $key = (string) config('services.google_maps.server_key', '');

        if ($key === '') {
            throw new RuntimeException('Google Maps server key belum diatur.');
        }

        return $key;
    }

    protected function normalizePlacePayload(array $payload): array
    {
        $location = $payload['location'] ?? [];
        $latitude = (float) ($location['latitude'] ?? 0);
        $longitude = (float) ($location['longitude'] ?? 0);

        return [
            'administrative' => $this->extractAdministrativeFromComponents($payload['addressComponents'] ?? []),
            'label' => trim((string) ($payload['formattedAddress'] ?? '')) ?: sprintf('Lat %.6f, Lng %.6f', $latitude, $longitude),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'place_id' => (string) ($payload['id'] ?? ''),
        ];
    }

    protected function normalizeGeocodeResult(array $result, float $latitude, float $longitude): array
    {
        $geometry = $result['geometry']['location'] ?? [];

        return [
            'administrative' => $this->extractAdministrativeFromComponents($result['address_components'] ?? []),
            'label' => trim((string) ($result['formatted_address'] ?? '')) ?: sprintf('Lat %.6f, Lng %.6f', $latitude, $longitude),
            'latitude' => (float) ($geometry['lat'] ?? $latitude),
            'longitude' => (float) ($geometry['lng'] ?? $longitude),
            'place_id' => (string) ($result['place_id'] ?? ''),
        ];
    }

    protected function extractAdministrativeFromComponents(array $components): array
    {
        $province = $this->findComponentText($components, ['administrative_area_level_1']);
        $regency = $this->findComponentText($components, ['administrative_area_level_2']);
        $district = $this->findComponentText($components, ['administrative_area_level_3', 'sublocality_level_1', 'sublocality']);
        $village = $this->findComponentText($components, ['administrative_area_level_4', 'neighborhood', 'locality']);

        return array_filter([
            'district' => $district,
            'province' => $province,
            'regency' => $regency,
            'village' => $village,
        ]);
    }

    protected function findComponentText(array $components, array $targetTypes): ?string
    {
        foreach ($components as $component) {
            $types = $component['types'] ?? [];

            if (!is_array($types) || !array_intersect($targetTypes, $types)) {
                continue;
            }

            $value = trim((string) ($component['longText'] ?? $component['long_name'] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
