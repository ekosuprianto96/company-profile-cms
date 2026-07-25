<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_user_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->string('label')->nullable();          // mis. "Rumah", "Kantor"
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 40)->nullable();
            $table->text('address')->nullable();          // detail: jalan, no, RT/RW, patokan
            // Wilayah administratif terstruktur (selaras RegionRef {code,name}).
            $table->json('province')->nullable();
            $table->json('regency')->nullable();
            $table->json('district')->nullable();
            $table->json('village')->nullable();
            $table->string('region_label')->nullable();   // ringkasan teks wilayah
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['mobile_user_id', 'is_primary']);
        });

        Schema::table('product_orders', function (Blueprint $table) {
            // Referensi alamat (jejak); teks alamat tetap di-snapshot pada order.
            $table->foreignId('mobile_user_address_id')->nullable()->after('shipping_courier_id')
                ->constrained('mobile_user_addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mobile_user_address_id');
        });

        Schema::dropIfExists('mobile_user_addresses');
    }
};
