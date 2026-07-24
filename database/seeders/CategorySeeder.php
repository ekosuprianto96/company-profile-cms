<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Master kategori (bersama layanan & produk). Idempotent (kunci = slug).
     * Kategori = pengelompokan murni; layanan/produk menempel ke kategori daun.
     * IT Developer & Event Organizer sengaja jadi kategori daun (tanpa sub) agar
     * tidak duplikat dengan nama layanannya. Ikon = key MaterialIcons.
     */
    public function run(): void
    {
        $tree = [
            [
                'name' => 'Pekerjaan Sipil & Interior',
                'slug' => 'pekerjaan-sipil-interior',
                'icon' => 'engineering',
                'children' => [
                    ['name' => 'Pekerjaan Sipil', 'slug' => 'pekerjaan-sipil', 'icon' => 'construction'],
                    [
                        'name' => 'Fit Out Interior',
                        'slug' => 'fit-out-interior',
                        'icon' => 'weekend',
                        'children' => [
                            // (Set) = layanan jasa ruangan; (Item) = produk furnitur.
                            ['name' => 'Fit Out Interior (Set)', 'slug' => 'fit-out-interior-set', 'icon' => 'grid-view'],
                            ['name' => 'Fit Out Interior (Item)', 'slug' => 'fit-out-interior-item', 'icon' => 'chair'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'IT Developer',
                'slug' => 'it-developer',
                'icon' => 'code',
            ],
            [
                'name' => 'Event Organizer',
                'slug' => 'event-organizer',
                'icon' => 'celebration',
            ],
            [
                'name' => 'Travel Umroh',
                'slug' => 'travel-umroh',
                'icon' => 'mosque',
            ],
        ];

        $keepSlugs = [];
        $this->upsertTree($tree, null, $keepSlugs);
        $this->pruneRemoved($keepSlugs);
    }

    private function upsertTree(array $nodes, ?int $parentId, array &$keepSlugs): void
    {
        foreach ($nodes as $order => $node) {
            $keepSlugs[] = $node['slug'];

            $category = Category::updateOrCreate(
                ['slug' => $node['slug']],
                [
                    'parent_id' => $parentId,
                    'name' => $node['name'],
                    'icon' => $node['icon'],
                    'sort_order' => $order,
                    'is_active' => true,
                ],
            );

            if (! empty($node['children'])) {
                $this->upsertTree($node['children'], $category->id, $keepSlugs);
            }
        }
    }

    /**
     * Hapus kategori lama yang tak lagi didefinisikan (mis. sub Web/Mobile Dev,
     * Wedding/Gathering/Event). Daun-dulu agar tidak kena restrictOnDelete;
     * category_id di layanan/produk otomatis null (nullOnDelete) lalu di-set ulang
     * oleh seeder layanan/produk.
     */
    private function pruneRemoved(array $keepSlugs): void
    {
        do {
            $removable = Category::whereNotIn('slug', $keepSlugs)
                ->whereDoesntHave('children')
                ->get();

            foreach ($removable as $category) {
                $category->delete();
            }
        } while ($removable->isNotEmpty());
    }
}
