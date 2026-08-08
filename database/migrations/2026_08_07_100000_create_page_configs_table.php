<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'page' | 'sections'
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Impor data yang SUDAH ADA dari file JSON lama (bila masih ada di server)
        // ke database, agar konfigurasi page builder & home section tidak hilang.
        foreach (['page' => 'config/page.json', 'sections' => 'config/sections.json'] as $key => $rel) {
            $path = base_path($rel);
            if (! is_file($path)) {
                continue;
            }
            $decoded = json_decode((string) file_get_contents($path), true);
            if (! is_array($decoded)) {
                continue;
            }
            DB::table('page_configs')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('page_configs');
    }
};
