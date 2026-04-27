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
        Schema::create('mobile_service_need_type_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_service_id');
            $table->unsignedBigInteger('mobile_service_need_type_id');
            $table->timestamps();

            $table->unique(['mobile_service_id', 'mobile_service_need_type_id'], 'mobile_service_need_type_unique');
            $table->foreign('mobile_service_id')->references('id')->on('mobile_services')->cascadeOnDelete();
            $table->foreign('mobile_service_need_type_id', 'mobile_service_need_type_rel_need_type_fk')
                ->references('id')
                ->on('mobile_service_need_types')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_service_need_type_relations');
    }
};

