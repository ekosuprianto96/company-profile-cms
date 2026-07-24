<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ProductReviewPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Read-only: penilaian dibuat dari mobile, admin hanya melihat.
        $permissions = [
            'product-review:show' => 'Lihat Penilaian Produk',
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
