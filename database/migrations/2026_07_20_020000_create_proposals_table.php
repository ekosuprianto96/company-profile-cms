<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Proposal = wadah seluruh isian form pengajuan layanan + harga yang berlaku.
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('proposal_number', 40)->unique();
            $table->foreignId('mobile_user_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->foreignId('mobile_service_id')->constrained('mobile_services')->cascadeOnDelete();
            $table->foreignId('form_id')->nullable()->constrained('forms')->nullOnDelete();

            $table->string('status', 20)->default('submitted'); // submitted|in_review|approved|rejected|cancelled

            $table->json('answers');                    // {field_key: value}
            // Snapshot schema saat submit — supaya jawaban tetap terbaca walau form diubah nanti.
            $table->json('form_snapshot')->nullable();
            $table->json('price_items')->nullable();    // snapshot komponen biaya layanan
            $table->unsignedBigInteger('total_amount')->default(0);

            $table->text('admin_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['mobile_user_id', 'status']);
            $table->index('mobile_service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
