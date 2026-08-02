<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Akses aplikasi Admin (mobile): admin login pakai akun `users` yang sudah ada.
 * - mobile_admin_access: apakah admin ini boleh masuk ke app admin.
 * - credential_key: kunci kredensial per-admin (dipakai saat login, selain password).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mobile_admin_access')) {
                $table->boolean('mobile_admin_access')->default(false)->after('id_role');
            }
            if (! Schema::hasColumn('users', 'credential_key')) {
                $table->string('credential_key', 40)->nullable()->unique()->after('mobile_admin_access');
            }
        });

        // Dev convenience: aktifkan akses + generate credential key untuk admin yang
        // sudah ada supaya bisa langsung login. Bisa dibatasi lagi lewat dashboard.
        DB::table('users')->get()->each(function ($user) {
            $updates = [];
            if (empty($user->credential_key)) {
                $updates['credential_key'] = 'MJ-ADM-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(3));
            }
            if (! $user->mobile_admin_access) {
                $updates['mobile_admin_access'] = true;
            }
            if (! empty($updates)) {
                DB::table('users')->where('id', $user->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'credential_key')) {
                $table->dropColumn('credential_key');
            }
            if (Schema::hasColumn('users', 'mobile_admin_access')) {
                $table->dropColumn('mobile_admin_access');
            }
        });
    }
};
