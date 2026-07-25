<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class FormPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'form:show' => 'Lihat Form Builder',
            'form:create' => 'Tambah Form',
            'form:update' => 'Ubah Form & Field',
            'form:delete' => 'Hapus Form',
        ];

        $ids = [];
        foreach ($permissions as $name => $displayName) {
            $ids[] = Permission::firstOrCreate(['name' => $name], ['display_name' => $displayName, 'an' => 1])->id;
        }

        foreach (Role::whereIn('id_role', [1, 6])->get() as $role) {
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}
