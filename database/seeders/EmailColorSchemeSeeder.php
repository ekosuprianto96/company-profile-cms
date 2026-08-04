<?php

namespace Database\Seeders;

use App\Models\EmailColorScheme;
use Illuminate\Database\Seeder;

class EmailColorSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = [
            ['name' => 'Maninjau (Hijau)', 'colors' => ['#275a56', '#1c433f', '#c8915c', '#334155', '#f8fafc', '#ffffff']],
            ['name' => 'Biru Profesional', 'colors' => ['#1f4d78', '#14324f', '#3b82f6', '#334155', '#eff6ff', '#ffffff']],
            ['name' => 'Oranye Hangat', 'colors' => ['#ea580c', '#c2410c', '#f59e0b', '#334155', '#fff7ed', '#ffffff']],
            ['name' => 'Emerald', 'colors' => ['#0f766e', '#115e59', '#14b8a6', '#334155', '#f0fdfa', '#ffffff']],
            ['name' => 'Netral Elegan', 'colors' => ['#334155', '#0f172a', '#64748b', '#1f2937', '#f1f5f9', '#ffffff']],
        ];

        foreach ($schemes as $s) {
            EmailColorScheme::updateOrCreate(
                ['name' => $s['name']],
                ['colors' => $s['colors'], 'is_default' => true],
            );
        }
    }
}
