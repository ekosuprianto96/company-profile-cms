<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('slug')->unique();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('compare_at_price')->nullable();
            $table->unsignedInteger('weight_grams')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->string('primary_image')->nullable();
            // 3 pengaturan wajib
            $table->boolean('can_be_bundled')->default(false);
            $table->string('service_scope', 20)->default('all');       // all | specific
            $table->string('shipping_method', 20)->default('internal'); // internal | courier
            $table->unsignedBigInteger('internal_shipping_fee')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'product_category_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('mobile_service_id')->constrained('mobile_services')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'mobile_service_id']);
        });

        Schema::create('shipping_couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_third_party')->default(false); // true = jasa kurir pihak ke-3 (belum aktif)
            $table->boolean('is_active')->default(true);
            $table->string('etd')->nullable();
            $table->unsignedBigInteger('base_cost')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_couriers');
        Schema::dropIfExists('product_service');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
