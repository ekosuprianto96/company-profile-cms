<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom kategori ternormalisasi (promo/informasi/konfirmasi) dihitung otomatis
        // dari data->type. STORED + ter-index agar filter per-tab & pagination murni SQL.
        DB::statement(<<<'SQL'
            ALTER TABLE notifications
            ADD COLUMN category VARCHAR(20)
            GENERATED ALWAYS AS (
                CASE LOWER(JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')))
                    WHEN 'info' THEN 'informasi'
                    WHEN 'success' THEN 'konfirmasi'
                    WHEN 'warning' THEN 'informasi'
                    WHEN 'danger' THEN 'konfirmasi'
                    WHEN 'secondary' THEN 'informasi'
                    WHEN 'system.notification' THEN 'informasi'
                    WHEN 'promo' THEN 'promo'
                    WHEN 'konfirmasi' THEN 'konfirmasi'
                    WHEN 'informasi' THEN 'informasi'
                    ELSE 'informasi'
                END
            ) STORED
        SQL);

        Schema::table('notifications', function (Blueprint $table) {
            // List per-tab + urutan/cursor pagination.
            $table->index(
                ['notifiable_type', 'notifiable_id', 'category', 'created_at'],
                'notif_notifiable_category_created_idx',
            );
            // Hitung unread (total & per kategori) tanpa memuat baris ke PHP.
            $table->index(
                ['notifiable_type', 'notifiable_id', 'read_at', 'category'],
                'notif_notifiable_read_category_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notif_notifiable_category_created_idx');
            $table->dropIndex('notif_notifiable_read_category_idx');
        });

        DB::statement('ALTER TABLE notifications DROP COLUMN category');
    }
};
