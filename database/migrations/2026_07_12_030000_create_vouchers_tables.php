<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('order_type', 20);               // service | product
            $table->string('discount_type', 20);            // percentage | fixed
            $table->unsignedInteger('discount_value')->default(0);
            $table->unsignedBigInteger('max_discount_amount')->nullable(); // cap untuk percentage
            $table->unsignedBigInteger('min_purchase_amount')->default(0);
            $table->string('item_scope', 20)->default('all');   // all | specific
            $table->string('user_scope', 20)->default('all');   // all | specific
            $table->unsignedInteger('usage_limit')->nullable();     // kuota total
            $table->unsignedInteger('usage_limit_per_user')->default(1);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['order_type', 'is_active']);
        });

        Schema::create('voucher_target_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->string('target_type', 20);  // service | product
            $table->unsignedBigInteger('target_id');
            $table->timestamps();

            $table->unique(['voucher_id', 'target_type', 'target_id'], 'voucher_target_unique');
        });

        Schema::create('voucher_mobile_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('mobile_user_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['voucher_id', 'mobile_user_id']);
        });

        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('mobile_user_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->string('order_type', 20);               // service | product
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('base_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->string('status', 20)->default('reserved'); // reserved | used | released | expired
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['voucher_id', 'mobile_user_id', 'status']);
            $table->index(['order_type', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('voucher_mobile_user');
        Schema::dropIfExists('voucher_target_items');
        Schema::dropIfExists('vouchers');
    }
};
