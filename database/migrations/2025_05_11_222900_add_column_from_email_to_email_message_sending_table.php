<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_message_sending', function (Blueprint $table) {
            $table->string('from_email', 150)->nullable();
            $table->integer('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_message_sending', function (Blueprint $table) {
            $table->dropColumn('from_email');
            $table->dropColumn('status');
        });
    }
};
