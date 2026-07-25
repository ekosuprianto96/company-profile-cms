<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MobileService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileServiceSeeder extends Seeder
{
    /**
     * Layanan mobile — hanya hal yang benar-benar "jasa yang diajukan".
     * Item furnitur pindah ke produk (ProductSeeder); paket Umroh jadi jenis
     * kebutuhan dari satu layanan "Travel Umroh". Idempotent (kunci = slug);
     * baris yang sudah ada di-update di tempat agar relasi (pengajuan) terjaga.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
        ]);

        $categoryIds = Category::pluck('id', 'slug');

        $palette = [
            'sipil' => ['card' => '#6ec7d0', 'text' => '#0e4751'],
            'it' => ['card' => '#8aa9f0', 'text' => '#16265e'],
            'event' => ['card' => '#f3a6c0', 'text' => '#5a1030'],
            'umroh' => ['card' => '#86d0a8', 'text' => '#10432b'],
        ];

        // Ruang lingkup pekerjaan sipil/interior.
        $scopeNeeds = ['perencanaan-bahan-pelaksana', 'bahan-pelaksana', 'pelaksana'];
        // Paket durasi umroh.
        $umrohPackages = ['umroh-full-ramadhan', 'umroh-12-hari', 'umroh-9-hari'];

        $services = [
            // A.1 Pekerjaan Sipil
            ['title' => 'Bangun Rumah', 'cat' => 'pekerjaan-sipil', 'icon' => 'home-work', 'group' => 'sipil', 'needs' => $scopeNeeds, 'featured' => true, 'popular' => true],
            ['title' => 'Renovasi Rumah', 'cat' => 'pekerjaan-sipil', 'icon' => 'home-repair-service', 'group' => 'sipil', 'needs' => $scopeNeeds, 'featured' => true, 'popular' => true],
            ['title' => 'Pembuatan / Service Kolam Renang', 'cat' => 'pekerjaan-sipil', 'icon' => 'pool', 'group' => 'sipil', 'needs' => $scopeNeeds, 'featured' => true],

            // A.2 Fit Out Interior (Set) — jasa per ruangan
            ['title' => 'Interior Set untuk Dapur', 'cat' => 'fit-out-interior-set', 'icon' => 'kitchen', 'group' => 'sipil', 'needs' => $scopeNeeds],
            ['title' => 'Interior Set untuk Kamar Tidur', 'cat' => 'fit-out-interior-set', 'icon' => 'king-bed', 'group' => 'sipil', 'needs' => $scopeNeeds],
            ['title' => 'Interior Set untuk Kamar Mandi', 'cat' => 'fit-out-interior-set', 'icon' => 'bathtub', 'group' => 'sipil', 'needs' => $scopeNeeds],
            ['title' => 'Interior Set untuk Ruang Tamu', 'cat' => 'fit-out-interior-set', 'icon' => 'chair', 'group' => 'sipil', 'needs' => $scopeNeeds, 'featured' => true, 'popular' => true],
            ['title' => 'Interior Set untuk Teras', 'cat' => 'fit-out-interior-set', 'icon' => 'deck', 'group' => 'sipil', 'needs' => $scopeNeeds],
            ['title' => 'Interior Set untuk Rooftop', 'cat' => 'fit-out-interior-set', 'icon' => 'roofing', 'group' => 'sipil', 'needs' => $scopeNeeds],

            // B. IT Developer — layanan langsung di bawah kategori IT Developer
            ['title' => 'Web Developer', 'cat' => 'it-developer', 'icon' => 'web', 'group' => 'it'],
            ['title' => 'Mobile Developer', 'cat' => 'it-developer', 'icon' => 'smartphone', 'group' => 'it'],

            // C. Event Organizer — layanan langsung di bawah kategori Event Organizer
            ['title' => 'Wedding Organizer', 'cat' => 'event-organizer', 'icon' => 'favorite', 'group' => 'event', 'flow' => 'event_project', 'featured' => true, 'popular' => true],
            ['title' => 'Gathering', 'cat' => 'event-organizer', 'icon' => 'groups', 'group' => 'event', 'flow' => 'event_project'],
            ['title' => 'Event', 'cat' => 'event-organizer', 'icon' => 'event', 'group' => 'event', 'flow' => 'event_project'],

            // D. Travel Umroh — satu layanan, paket sebagai jenis kebutuhan
            ['title' => 'Travel Umroh', 'cat' => 'travel-umroh', 'icon' => 'mosque', 'group' => 'umroh', 'needs' => $umrohPackages, 'featured' => true],
        ];

        $keepSlugs = [];

        foreach ($services as $order => $service) {
            $slug = Str::slug($service['title']);
            $keepSlugs[] = $slug;
            $group = $service['group'];

            $saved = MobileService::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $service['title'],
                    'category_id' => $categoryIds[$service['cat']] ?? null,
                    'request_flow_type' => $service['flow'] ?? 'standard',
                    'summary' => null,
                    'description' => null,
                    'icon_type' => 'icon',
                    'icon' => $service['icon'],
                    'icon_image' => null,
                    'card_color' => $palette[$group]['card'],
                    'text_color' => $palette[$group]['text'],
                    'badge_text' => null,
                    'price_label' => 'Hubungi Kami',
                    'rating' => null,
                    'projects_label' => null,
                    'estimated_duration' => null,
                    'cta_text' => 'Ajukan Sekarang',
                    'sort_order' => $order + 1,
                    'is_new' => false,
                    'is_featured' => (bool) ($service['featured'] ?? false),
                    'is_popular' => (bool) ($service['popular'] ?? false),
                    'is_active' => true,
                    'is_coming_soon' => false,
                ],
            );
        }

        $this->pruneStaleServices($keepSlugs);
    }

    /**
     * Layanan di luar set baru (mis. item furnitur & paket umroh lama): hapus bila
     * tak direferensikan; jika masih dipakai pengajuan/produk, non-aktifkan saja.
     */
    private function pruneStaleServices(array $keepSlugs): void
    {
        $stale = MobileService::whereNotIn('slug', $keepSlugs)->get();
        $deleted = 0;
        $deactivated = 0;

        foreach ($stale as $service) {
            $referenced = DB::table('mobile_service_requests')->where('mobile_service_id', $service->id)->exists()
                || DB::table('product_service')->where('mobile_service_id', $service->id)->exists();

            if ($referenced) {
                $service->update(['is_active' => false]);
                $deactivated++;
            } else {
                $service->delete();
                $deleted++;
            }
        }

        $this->command?->info("MobileServiceSeeder: {$deleted} layanan lama dihapus, {$deactivated} dinonaktifkan.");
    }
}
