<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_support_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40); // whatsapp | email | phone | instagram | other
            $table->string('label', 120);
            $table->string('value', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('mobile_support_contacts')->insert([
            ['type' => 'whatsapp', 'label' => 'WhatsApp', 'value' => '+6281234567890', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'email', 'label' => 'Email', 'value' => 'support@maninjau.app', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_support_contacts');
    }
};
