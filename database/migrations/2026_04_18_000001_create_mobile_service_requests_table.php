<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_user_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->foreignId('mobile_service_id')->constrained('mobile_services')->cascadeOnDelete();
            $table->foreignId('mobile_service_need_type_id')->nullable()->constrained('mobile_service_need_types')->nullOnDelete();
            $table->foreignId('mobile_budget_option_id')->nullable()->constrained('mobile_budget_options')->nullOnDelete();
            $table->string('building_key', 50);
            $table->string('building_label');
            $table->text('description')->nullable();
            $table->json('issue_photos')->nullable();
            $table->text('survey_address');
            $table->decimal('survey_latitude', 10, 7);
            $table->decimal('survey_longitude', 10, 7);
            $table->date('survey_date');
            $table->unsignedBigInteger('survey_fee');
            $table->unsignedBigInteger('total_amount');
            $table->string('status', 30)->default('draft');
            $table->string('payment_status', 30)->default('unpaid');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('payment_payload')->nullable();
            $table->json('draft_payload')->nullable();
            $table->timestamp('drafted_at')->nullable();
            $table->timestamp('payment_method_selected_at')->nullable();
            $table->timestamps();

            $table->index(['mobile_user_id', 'status']);
            $table->index(['mobile_user_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_service_requests');
    }
};
