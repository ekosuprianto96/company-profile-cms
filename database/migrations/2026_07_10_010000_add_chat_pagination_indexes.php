<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cursor pagination membutuhkan last_message_at non-null agar keyset stabil.
        DB::table('chat_conversations')
            ->whereNull('last_message_at')
            ->update(['last_message_at' => DB::raw('created_at')]);

        Schema::table('chat_conversations', function (Blueprint $table) {
            // Urutan aktivitas terbaru per user + cursor pagination daftar chat.
            $table->index(['mobile_user_id', 'last_message_at'], 'chat_conv_user_lastmsg_idx');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            // Infinite scroll pesan (order/cursor by id di dalam satu percakapan).
            $table->index(['chat_conversation_id', 'id'], 'chat_msg_conv_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropIndex('chat_conv_user_lastmsg_idx');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_msg_conv_id_idx');
        });
    }
};
