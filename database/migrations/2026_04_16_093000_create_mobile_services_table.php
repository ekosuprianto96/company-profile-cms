<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mobile_services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 190)->unique();
            $table->string('summary', 255)->nullable();
            $table->longText('description')->nullable();
            $table->enum('icon_type', ['icon', 'image'])->default('icon');
            $table->string('icon', 80)->nullable();
            $table->string('icon_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('card_color', 20)->default('#6ec7d0');
            $table->string('text_color', 20)->default('#0e4751');
            $table->string('badge_text', 50)->nullable();
            $table->string('price_label', 100)->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->string('projects_label', 100)->nullable();
            $table->string('estimated_duration', 100)->nullable();
            $table->string('cta_text', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_featured')->default(true);
            $table->boolean('is_popular')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_services');
    }
};

