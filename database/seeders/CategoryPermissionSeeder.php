<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CategoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'category:show' => 'Lihat Kategori',
            'category:create' => 'Tambah Kategori',
            'category:update' => 'Ubah Kategori',
            'category:delete' => 'Hapus Kategori',
        ];

        $ids = [];
        foreach ($permissions as $name => $displayName) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'an' => 1],
            );
            $ids[] = $permission->id;
        }

        // Hanya role akses penuh (superadmin=1, developer=6) secara default.
        foreach (Role::whereIn('id_role', [1, 6])->get() as $role) {
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}
