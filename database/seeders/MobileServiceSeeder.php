<?php

namespace Database\Seeders;

use App\Models\MobileService;
use App\Models\MobileServiceNeedType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MobileServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'title' => 'Bangun Rumah',
                'summary' => 'Pendampingan desain, RAB, dan pembangunan untuk rumah tinggal modern.',
                'icon' => 'home-repair-service',
                'need_type_slugs' => ['perencanaan', 'jasa-bahan-bangunan', 'jasa'],
                'card_color' => '#6ec7d0',
                'text_color' => '#0e4751',
                'badge_text' => 'Recommended',
                'price_label' => 'Mulai Rp175jt',
                'rating' => 4.8,
                'projects_label' => '187 proyek',
                'estimated_duration' => '90 - 180 hari',
                'sort_order' => 1,
                'is_new' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Renovasi Rumah',
                'summary' => 'Paket renovasi fasad, ruang tamu, dan dapur dengan survey langsung.',
                'icon' => 'architecture',
                'need_type_slugs' => ['perencanaan', 'jasa-bahan-bangunan', 'jasa'],
                'card_color' => '#f3c66a',
                'text_color' => '#4f3b09',
                'badge_text' => 'Best Seller',
                'price_label' => 'Mulai Rp15jt',
                'rating' => 4.9,
                'projects_label' => '320 proyek',
                'estimated_duration' => '14 - 60 hari',
                'sort_order' => 2,
                'is_new' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Pembuatan Kolam',
                'summary' => 'Pembuatan kolam renang dan kolam taman dengan konstruksi rapi dan aman.',
                'icon' => 'pool',
                'need_type_slugs' => ['jasa-bahan-bangunan', 'jasa'],
                'card_color' => '#e88779',
                'text_color' => '#5a1f17',
                'badge_text' => null,
                'price_label' => 'Hubungi Kami',
                'rating' => 4.7,
                'projects_label' => '95 proyek',
                'estimated_duration' => '20 - 75 hari',
                'sort_order' => 3,
                'is_new' => false,
                'is_featured' => true,
                'is_popular' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Konstruksi Baja',
                'summary' => 'Layanan struktur dan konstruksi baja untuk hunian maupun bangunan komersial.',
                'icon' => 'foundation',
                'need_type_slugs' => ['perencanaan', 'jasa-bahan-bangunan', 'jasa'],
                'card_color' => '#8ed2d5',
                'text_color' => '#114a4c',
                'badge_text' => null,
                'price_label' => 'Hubungi Kami',
                'rating' => 4.8,
                'projects_label' => '140 proyek',
                'estimated_duration' => '30 - 90 hari',
                'sort_order' => 4,
                'is_new' => false,
                'is_featured' => true,
                'is_popular' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Pekerjaan Interior',
                'summary' => 'Kitchen set, wardrobe, dan furnitur built-in sesuai ukuran ruangan.',
                'icon' => 'chair',
                'need_type_slugs' => ['perencanaan', 'jasa-bahan-bangunan', 'jasa'],
                'card_color' => '#f4e8b7',
                'text_color' => '#5a4d1f',
                'badge_text' => 'Trending',
                'price_label' => 'Mulai Rp8jt',
                'rating' => 4.9,
                'projects_label' => '241 proyek',
                'estimated_duration' => '10 - 45 hari',
                'sort_order' => 5,
                'is_new' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Wedding Organizer',
                'summary' => 'Jasa wedding organizer dan dekorasi acara dengan konsep custom.',
                'icon' => 'favorite',
                'need_type_slugs' => ['jasa'],
                'card_color' => '#7fc2cc',
                'text_color' => '#10444e',
                'badge_text' => 'NEW',
                'price_label' => 'Mulai Rp12jt',
                'rating' => 4.8,
                'projects_label' => '68 event',
                'estimated_duration' => 'Sesuai rundown',
                'sort_order' => 6,
                'is_new' => true,
                'is_featured' => true,
                'is_popular' => false,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            $title = (string) $service['title'];

            $needTypeIds = MobileServiceNeedType::query()
                ->whereIn('slug', $service['need_type_slugs'] ?? [])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();

            $saved = MobileService::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'summary' => $service['summary'],
                    'description' => $service['summary'],
                    'icon_type' => 'icon',
                    'icon' => $service['icon'],
                    'icon_image' => null,
                    'cover_image' => null,
                    'card_color' => $service['card_color'],
                    'text_color' => $service['text_color'],
                    'badge_text' => $service['badge_text'],
                    'price_label' => $service['price_label'],
                    'rating' => $service['rating'],
                    'projects_label' => $service['projects_label'],
                    'estimated_duration' => $service['estimated_duration'],
                    'cta_text' => 'Ajukan Sekarang',
                    'sort_order' => $service['sort_order'],
                    'is_new' => $service['is_new'],
                    'is_featured' => $service['is_featured'],
                    'is_popular' => $service['is_popular'],
                    'is_active' => $service['is_active'],
                ]
            );

            $saved->needTypes()->sync($needTypeIds);
        }
    }
}
