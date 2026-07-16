<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ProductPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'product:show' => 'Lihat Produk',
            'product:create' => 'Tambah Produk',
            'product:update' => 'Ubah Produk',
            'product:destroy' => 'Hapus Produk',
            'product-category:show' => 'Lihat Kategori Produk',
            'product-category:create' => 'Tambah Kategori Produk',
            'product-category:update' => 'Ubah Kategori Produk',
            'product-category:destroy' => 'Hapus Kategori Produk',
            'shipping:show' => 'Lihat Kurir',
            'shipping:create' => 'Tambah Kurir',
            'shipping:update' => 'Ubah Kurir',
            'shipping:destroy' => 'Hapus Kurir',
            'product-order:show' => 'Lihat Order Produk',
            'product-order:update' => 'Proses Order Produk',
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
