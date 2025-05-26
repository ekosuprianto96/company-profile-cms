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
        Schema::create('email_message_sending', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('customer_contact')->onDelete('cascade');
            $table->string('subject', 200);
            $table->text('message');
            $table->unsignedBigInteger('send_by');
            $table->foreign('send_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_message_sending');
    }
};
