<?php

namespace Database\Seeders;

use App\Models\InspirePost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InspirePostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => '7 Cara Bikin Ruang Tamu Terasa Lebih Luas',
                'category' => 'Interior',
                'summary' => 'Trik sederhana yang cocok untuk rumah dengan area terbatas.',
                'content' => 'Gunakan warna netral, furniture ramping, dan pencahayaan bertingkat untuk menciptakan kesan lega.',
                'accent_color' => '#275a56',
                'reading_time' => 3,
                'sort_order' => 1,
                'is_featured' => true,
            ],
            [
                'title' => 'Checklist Renovasi Rumah yang Sering Terlewat',
                'category' => 'Renovasi',
                'summary' => 'Sebelum bongkar, pastikan kamu sudah cek poin penting ini.',
                'content' => 'Mulai dari izin, estimasi biaya, detail material, hingga timeline kerja agar proyek tetap aman.',
                'accent_color' => '#f5b946',
                'reading_time' => 4,
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'title' => 'Pilihan Material yang Tahan Lama untuk Area Basah',
                'category' => 'Material',
                'summary' => 'Tips memilih material supaya awet dan mudah dirawat.',
                'content' => 'Prioritaskan material yang tahan lembap, anti selip, dan mudah dibersihkan untuk dapur serta kamar mandi.',
                'accent_color' => '#3c9e79',
                'reading_time' => 2,
                'sort_order' => 3,
                'is_featured' => false,
            ],
            [
                'title' => 'Inspirasi Kitchen Set Minimalis yang Fungsional',
                'category' => 'Interior',
                'summary' => 'Desain dapur ringkas tetap bisa terasa premium.',
                'content' => 'Pilih layout yang efisien, simpanan tersembunyi, dan kombinasi warna lembut untuk hasil yang bersih.',
                'accent_color' => '#7c3aed',
                'reading_time' => 3,
                'sort_order' => 4,
                'is_featured' => false,
            ],
            [
                'title' => 'Kenapa Survey Awal Penting Sebelum Mulai Proyek',
                'category' => 'Tips',
                'summary' => 'Survey awal membantu tim memahami kondisi lapangan lebih akurat.',
                'content' => 'Dengan survey yang baik, estimasi biaya dan waktu kerja jadi lebih presisi sejak awal.',
                'accent_color' => '#e88779',
                'reading_time' => 2,
                'sort_order' => 5,
                'is_featured' => true,
            ],
        ];

        foreach ($posts as $post) {
            InspirePost::query()->updateOrCreate(
                ['slug' => Str::slug($post['title'])],
                [
                    'title' => $post['title'],
                    'category' => $post['category'],
                    'summary' => $post['summary'],
                    'content' => '<p>' . e($post['content']) . '</p>',
                    'thumbnail' => null,
                    'accent_color' => $post['accent_color'],
                    'reading_time' => $post['reading_time'],
                    'sort_order' => $post['sort_order'],
                    'is_featured' => $post['is_featured'],
                    'is_published' => true,
                ]
            );
        }
    }
}
