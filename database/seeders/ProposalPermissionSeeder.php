<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ProposalPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'proposal:show' => 'Lihat Proposal',
            'proposal:update' => 'Ubah Status Proposal',
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
