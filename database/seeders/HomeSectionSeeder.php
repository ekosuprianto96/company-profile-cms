<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    /**
     * Section default home (dinamis). Menyamai tampilan home saat ini + contoh
     * source lain. Idempotent (kunci = title). Grid layanan & bottom-nav tetap
     * fixed di kode mobile, tidak termasuk di sini.
     */
    public function run(): void
    {
        $sections = [
            [
                'title' => 'Produk Pilihan',
                'subtitle' => 'Produk pilihan terbaik untukmu',
                'source_type' => 'product',
                'layout' => 'slider',
                'selection_mode' => 'auto',
                'auto_filter' => 'featured',
                'max_items' => 8,
                'view_all_target' => '/products',
            ],
            [
                'title' => 'Layanan Populer',
                'subtitle' => 'Paling banyak diminati',
                'source_type' => 'service',
                'layout' => 'list',
                'selection_mode' => 'auto',
                'auto_filter' => 'popular',
                'max_items' => 5,
                'view_all_target' => '/services',
            ],
            [
                'title' => 'Voucher Untukmu',
                'subtitle' => 'Klaim sebelum kedaluwarsa',
                'source_type' => 'voucher',
                'layout' => 'slider',
                'selection_mode' => 'auto',
                'auto_filter' => 'active',
                'max_items' => 6,
                'view_all_target' => null,
            ],
            [
                'title' => 'Inspirasi',
                'subtitle' => 'Ide desain & gaya hidup',
                'source_type' => 'inspire',
                'layout' => 'list',
                'selection_mode' => 'auto',
                'auto_filter' => 'newest',
                'max_items' => 6,
                'view_all_target' => null,
            ],
            [
                'title' => 'Blog Terbaru',
                'subtitle' => 'Tips rumah, desain, dan lifestyle',
                'source_type' => 'blog',
                'layout' => 'list',
                'selection_mode' => 'auto',
                'auto_filter' => 'newest',
                'max_items' => 3,
                'view_all_target' => null,
            ],
        ];

        foreach ($sections as $order => $section) {
            HomeSection::updateOrCreate(
                ['title' => $section['title']],
                array_merge($section, ['sort_order' => $order + 1, 'is_active' => true]),
            );
        }
    }
}
