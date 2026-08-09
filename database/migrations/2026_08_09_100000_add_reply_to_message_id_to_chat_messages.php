<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Pesan yang dibalas (fitur reply/quote). nullOnDelete: menghapus
            // pesan yang dikutip tidak ikut menghapus balasannya.
            $table->foreignId('reply_to_message_id')->nullable()->after('attachments')
                ->constrained('chat_messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_to_message_id');
        });
    }
};
