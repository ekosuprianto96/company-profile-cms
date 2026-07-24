<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pembersihan satu kali: hapus permanen 3 layanan uji lama (di luar taksonomi
 * baru) beserta pengajuan ujinya. Dikonfirmasi user ("bersihkan total").
 * FK cascade: hapus mobile_services -> mobile_service_requests ->
 * service_request_products; chat_conversations.service_request_id di-null.
 */
return new class extends Migration
{
    private array $slugs = ['pembuatan-kolam', 'konstruksi-baja', 'pekerjaan-interior'];

    public function up(): void
    {
        $ids = DB::table('mobile_services')->whereIn('slug', $this->slugs)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('mobile_service_need_type_relations')->whereIn('mobile_service_id', $ids)->delete();
        DB::table('mobile_services')->whereIn('id', $ids)->delete();
    }

    public function down(): void
    {
        // Data uji lama — tidak dapat dipulihkan (sengaja no-op).
    }
};
