<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('category', 60)->nullable();     // pengelompokan (mis. transaksi, marketing)
            $table->string('description', 255)->nullable();
            $table->string('subject', 200)->nullable();      // subjek default bawaan desain (opsional)
            $table->longText('html')->nullable();            // HTML terkompilasi (mengandung token {{ body }})
            $table->longText('design_json')->nullable();     // projectData GrapesJS untuk edit ulang
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);   // desain bawaan sistem (dari seeder)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('notification_templates', function (Blueprint $table) {
            // Desain email yang dipakai template channel email (opsional; null = layout bawaan).
            $table->unsignedBigInteger('email_design_id')->nullable()->after('body');
            $table->foreign('email_design_id')->references('id')->on('email_designs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropForeign(['email_design_id']);
            $table->dropColumn('email_design_id');
        });
        Schema::dropIfExists('email_designs');
    }
};
