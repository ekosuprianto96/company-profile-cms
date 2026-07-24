<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class HomeSectionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'home-section:show' => 'Lihat Section Home',
            'home-section:create' => 'Tambah Section Home',
            'home-section:update' => 'Ubah Section Home',
            'home-section:delete' => 'Hapus Section Home',
        ];

        $ids = [];
        foreach ($permissions as $name => $displayName) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'an' => 1],
            );
            $ids[] = $permission->id;
        }

        foreach (Role::whereIn('id_role', [1, 6])->get() as $role) {
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}
