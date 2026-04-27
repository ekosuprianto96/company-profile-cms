<?php

namespace Database\Seeders;

use App\Repositories\MobileAppSettingRepository;
use Illuminate\Database\Seeder;

class MobileAppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        (new MobileAppSettingRepository())->updateSettings([
            'survey_fee' => 150000,
            'tax_percentage' => 0,
            'payment_gateway' => [
                'enabled' => true,
                'provider' => 'midtrans',
            ],
            'survey_coverage' => [
                'enabled' => false,
                'whatsapp_number' => '',
                'whatsapp_message' => 'Alamat / wilayah yang Anda input untuk Survey di luar jangkauan kami. Silakan konsultasi dengan Tim Teknis kami untuk menyepakati proses Survey ke alamat yang sudah Anda input.',
                'rules' => [],
            ],
            'manual_transfers' => [
                [
                    'id' => 'bca',
                    'bank_name' => 'BCA',
                    'account_name' => 'Admin Maninjau',
                    'account_number' => '-',
                    'notes' => '',
                    'is_active' => true,
                    'sort_order' => 1,
                ],
            ],
        ]);
    }
}
