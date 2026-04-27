<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_user_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_user_id')->nullable()->constrained('mobile_users')->nullOnDelete();
            $table->string('purpose', 50);
            $table->string('channel', 20);
            $table->string('recipient', 150);
            $table->string('provider', 50)->nullable();
            $table->string('provider_sid', 100)->nullable();
            $table->string('code_hash')->nullable();
            $table->text('code_encrypted')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->index(['recipient', 'purpose', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_user_otps');
    }
};
