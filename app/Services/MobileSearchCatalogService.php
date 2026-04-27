<?php

namespace App\Services;

class MobileSearchCatalogService
{
    /**
     * @return array{current_location_label: string, popular_searches: array<int, array<string, mixed>>}
     */
    public function popular(?string $locationLabel = null, ?float $latitude = null, ?float $longitude = null): array
    {
        $resolvedLocationLabel = trim((string) ($locationLabel ?? ''));

        if ($resolvedLocationLabel === '') {
            $resolvedLocationLabel = 'Lokasi saat ini';
        }

        $locationHint = $resolvedLocationLabel === 'Lokasi saat ini'
            ? 'Populer di sekitar Anda'
            : 'Populer di ' . $resolvedLocationLabel;

        $baseItems = [
            [
                'title' => 'Renovasi Rumah Premium',
                'subtitle' => 'Survey cepat, konsultasi desain, dan estimasi pengerjaan.',
                'badge' => 'Populer',
                'category' => 'service',
            ],
            [
                'title' => 'Bangun Rumah dari Nol',
                'subtitle' => 'Pendampingan lengkap dari survei sampai RAB.',
                'badge' => 'Sering Dicari',
                'category' => 'service',
            ],
            [
                'title' => 'Sofa Minimalis 3 Dudukan',
                'subtitle' => 'Produk furniture siap kirim untuk ruang tamu modern.',
                'badge' => 'Produk',
                'category' => 'product',
            ],
            [
                'title' => 'Meja Makan Solid Wood',
                'subtitle' => 'Pilihan favorit untuk ruang makan keluarga.',
                'badge' => 'Terlaris',
                'category' => 'product',
            ],
            [
                'title' => 'Interior Custom Minimalis',
                'subtitle' => 'Kitchen set, wardrobe, dan built-in furniture.',
                'badge' => 'Trending',
                'category' => 'service',
            ],
            [
                'title' => 'Wedding Organizer',
                'subtitle' => 'Dekorasi dan event support untuk momen spesial.',
                'badge' => 'Populer',
                'category' => 'service',
            ],
        ];

        $popularSearches = array_map(static function (array $item) use ($locationHint, $latitude, $longitude): array {
            return [
                'title' => $item['title'],
                'subtitle' => $item['subtitle'],
                'badge' => $item['badge'],
                'category' => $item['category'],
                'location_hint' => $locationHint,
                'score' => $latitude !== null && $longitude !== null ? 'Rekomendasi berdasarkan lokasi Anda' : 'Rekomendasi umum',
            ];
        }, $baseItems);

        return [
            'current_location_label' => $resolvedLocationLabel,
            'popular_searches' => $popularSearches,
        ];
    }
}
