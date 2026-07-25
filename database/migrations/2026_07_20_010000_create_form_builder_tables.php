<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Form (schema) pengajuan — khusus layanan, dipakai bersama antar layanan.
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 190)->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Field/pertanyaan dalam sebuah form.
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('key', 100);                 // nama mesin, dipakai saat submit
            $table->string('label', 255);
            $table->string('type', 30);                 // text|number|select|file|location|...
            $table->string('placeholder', 255)->nullable();
            $table->string('help_text', 500)->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('options_source', 20)->default('static');   // static|datasource
            $table->string('options_source_key', 60)->nullable();      // kunci registry datasource
            $table->json('options')->nullable();        // [{label,value}] utk static
            $table->json('validation')->nullable();     // {min,max,min_length,accept,max_size_mb,...}
            $table->json('conditional')->nullable();    // {field,operator,value}
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['form_id', 'key']);
            $table->index(['form_id', 'sort_order']);
        });

        // Layanan memilih form-nya (banyak layanan boleh pakai form yang sama).
        Schema::table('mobile_services', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_services', 'form_id')) {
                $table->foreignId('form_id')->nullable()->after('category_id')
                    ->constrained('forms')->nullOnDelete();
            }
        });

        // Skema harga per layanan — menggantikan survey_fee global yang kaku.
        Schema::create('service_price_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_service_id')->constrained('mobile_services')->cascadeOnDelete();
            $table->string('type', 30);                 // survey|consultation|dp|fixed|other
            $table->string('label', 150);               // mis. "Biaya Survei", "Biaya Konsultasi"
            $table->unsignedBigInteger('amount')->default(0);
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mobile_service_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_price_items');

        Schema::table('mobile_services', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_services', 'form_id')) {
                $table->dropConstrainedForeignId('form_id');
            }
        });

        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
