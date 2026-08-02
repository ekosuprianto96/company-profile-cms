<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Template pesan notifikasi: sumber tunggal teks untuk email, push (FCM/Expo),
 * dan in-app. Satu baris = 1 template untuk (event_key, channel, audience).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->index();          // mis. service_request.approved
            $table->string('channel');                      // email | push | in_app
            $table->string('audience')->default('user');    // user | admin
            $table->string('name');                         // label template (bisa diubah admin)
            $table->string('subject')->nullable();          // subjek email / judul push & in-app
            $table->longText('body');                       // isi (berisi {{ variabel }})
            $table->boolean('is_active')->default(true);    // template yang dipakai
            $table->boolean('is_default')->default(false);  // template bawaan sistem (dari catalog)
            $table->timestamps();

            $table->index(['event_key', 'channel', 'audience', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
