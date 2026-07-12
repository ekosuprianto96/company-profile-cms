<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class VoucherPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'voucher:show' => 'Lihat Voucher',
            'voucher:create' => 'Tambah Voucher',
            'voucher:update' => 'Ubah Voucher',
            'voucher:destroy' => 'Hapus Voucher',
        ];

        $ids = [];
        foreach ($permissions as $name => $displayName) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'an' => 1],
            );
            $ids[] = $permission->id;
        }

        // Hanya role akses penuh (superadmin=1, developer=6) yang mendapat akses voucher
        // secara default, supaya tidak semua admin bisa mengelola voucher.
        foreach (Role::whereIn('id_role', [1, 6])->get() as $role) {
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}
