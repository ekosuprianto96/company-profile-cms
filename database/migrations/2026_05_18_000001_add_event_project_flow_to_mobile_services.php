<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_services', function (Blueprint $table) {
            $table->string('request_flow_type', 40)->default('standard')->after('slug')->index();
        });

        Schema::create('mobile_event_project_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mobile_event_project_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_event_project_type_id')->constrained('mobile_event_project_types')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 150);
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['mobile_event_project_type_id', 'slug'], 'event_need_type_slug_unique');
        });

        Schema::create('mobile_event_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_event_project_need_id')->constrained('mobile_event_project_needs')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['mobile_event_project_need_id', 'slug'], 'event_package_need_slug_unique');
        });

        Schema::create('mobile_event_budget_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->unsignedBigInteger('min_amount')->nullable();
            $table->unsignedBigInteger('max_amount')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->string('request_flow_type', 40)->default('standard')->after('mobile_budget_option_id')->index();
            $table->foreignId('mobile_event_project_type_id')->nullable()->after('request_flow_type')->constrained('mobile_event_project_types')->nullOnDelete();
            $table->foreignId('mobile_event_project_need_id')->nullable()->after('mobile_event_project_type_id')->constrained('mobile_event_project_needs')->nullOnDelete();
            $table->foreignId('mobile_event_package_id')->nullable()->after('mobile_event_project_need_id')->constrained('mobile_event_packages')->nullOnDelete();
            $table->foreignId('mobile_event_budget_option_id')->nullable()->after('mobile_event_package_id')->constrained('mobile_event_budget_options')->nullOnDelete();
            $table->date('event_date')->nullable()->after('mobile_event_budget_option_id');
        });

        DB::statement('ALTER TABLE mobile_service_requests MODIFY building_key VARCHAR(50) NULL');
        DB::statement('ALTER TABLE mobile_service_requests MODIFY building_label VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mobile_event_budget_option_id');
            $table->dropConstrainedForeignId('mobile_event_package_id');
            $table->dropConstrainedForeignId('mobile_event_project_need_id');
            $table->dropConstrainedForeignId('mobile_event_project_type_id');
            $table->dropColumn(['request_flow_type', 'event_date']);
        });

        Schema::dropIfExists('mobile_event_budget_options');
        Schema::dropIfExists('mobile_event_packages');
        Schema::dropIfExists('mobile_event_project_needs');
        Schema::dropIfExists('mobile_event_project_types');

        Schema::table('mobile_services', function (Blueprint $table) {
            $table->dropColumn('request_flow_type');
        });
    }
};
