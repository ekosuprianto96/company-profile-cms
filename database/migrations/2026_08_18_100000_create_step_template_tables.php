<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Template Rules Step: template langkah status pengajuan yang bisa dikonfigurasi
 * admin dan dipakai oleh satu atau banyak layanan. Snapshot step disalin ke tiap
 * pengajuan (menempel ke data) sehingga perubahan template tidak merusak data lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('step_templates')) {
            Schema::create('step_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->boolean('is_default')->default(false); // dipakai layanan tanpa template
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('step_template_steps')) {
            Schema::create('step_template_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('step_template_id')->constrained('step_templates')->cascadeOnDelete();
                $table->string('key', 60); // identitas step (core: draft/waiting_payment/review/approved/completed)
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('kind', 20)->default('custom'); // core (wajib, terkunci) | optional (disediakan) | custom (buatan admin)
                $table->string('trigger_status', 40)->nullable(); // event engine yang otomatis mencentang; null = manual admin
                $table->json('actions')->nullable(); // action terpilih: notif_inapp / notif_push / notif_email
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['step_template_id', 'key']);
            });
        }

        if (! Schema::hasColumn('mobile_services', 'step_template_id')) {
            Schema::table('mobile_services', function (Blueprint $table) {
                // Banyak layanan boleh memakai template step yang sama.
                $table->foreignId('step_template_id')->nullable()->after('form_id')
                    ->constrained('step_templates')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('mobile_service_requests', 'steps_snapshot')) {
            Schema::table('mobile_service_requests', function (Blueprint $table) {
                // Checklist step menempel ke pengajuan (pola sama dengan form_snapshot).
                $table->json('steps_snapshot')->nullable()->after('draft_payload');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mobile_service_requests', 'steps_snapshot')) {
            Schema::table('mobile_service_requests', fn (Blueprint $table) => $table->dropColumn('steps_snapshot'));
        }

        if (Schema::hasColumn('mobile_services', 'step_template_id')) {
            Schema::table('mobile_services', function (Blueprint $table) {
                $table->dropConstrainedForeignId('step_template_id');
            });
        }

        Schema::dropIfExists('step_template_steps');
        Schema::dropIfExists('step_templates');
    }
};
