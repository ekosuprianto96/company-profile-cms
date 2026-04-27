<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->string('sender_type', 20);
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sender_mobile_user_id')->nullable()->constrained('mobile_users')->nullOnDelete();
            $table->longText('body');
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index(['chat_conversation_id', 'created_at']);
            $table->index(['sender_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
