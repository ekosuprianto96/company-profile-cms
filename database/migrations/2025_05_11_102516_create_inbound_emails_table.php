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
        Schema::create('inbound_emails', function (Blueprint $table) {
            $table->id();
            $table->string('to_email', 150);
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('subject', 200);
            $table->text('message');
            $table->integer('is_read')->default(0);
            $table->string('message_id', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_emails');
    }
};
