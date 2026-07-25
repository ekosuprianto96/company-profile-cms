<?php

namespace App\Exports;

use App\Services\ProductImportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return collect(ProductImportService::COLUMNS)->pluck('label')->all();
    }

    public function array(): array
    {
        // Satu baris contoh untuk memandu admin (boleh dihapus sebelum diisi).
        return [
            [
                'Kursi Kayu Jati',        // Nama Produk
                'KRS-001',                // SKU
                'Jati Furniture',         // Brand
                'Furnitur > Kursi',       // Kategori (Induk > Sub)
                750000,                   // Harga
                900000,                   // Harga Coret
                25,                       // Stok
                8000,                     // Berat (gram)
                'Kursi kayu jati solid',  // Deskripsi Singkat
                'Deskripsi lengkap produk di sini.', // Deskripsi
                'internal',               // Metode Pengiriman
                25000,                    // Ongkir Internal
                'ya',                     // Bisa Dibundle
                'all',                    // Cakupan Layanan
                'ya',                     // Aktif
                'tidak',                  // Unggulan
            ],
        ];
    }
}
