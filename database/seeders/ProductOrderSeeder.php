<?php

namespace Database\Seeders;

use App\Models\MobileUser;
use App\Models\ProductOrder;
use Illuminate\Database\Seeder;

class ProductOrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = MobileUser::query()->orderBy('id')->first();

        $orders = [
            [
                'order_number' => 'ORD-260418-01',
                'product_name' => 'HAKATA Sofa 3 Dudukan Ash Grey',
                'variant' => 'Ash Grey • 3 Dudukan',
                'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDpqsNkAgbqbjx2Il29wqoCj4ows9By6RReC2pX0jS3-liIzBWD_jyRIrgJoZlNwijY6izYwSuqlprH4Y-3gJJ9zemAyjKyTGDXdMeUGZ5lPTEZRt9oLrLyLtt4X_nqLazinYUNZOlYyEK6Q-FetFDaSJZSCLzO3JoAyDd1j5F-hdTzOBfrzgIQ4-EAyVEeQSVzkRuSCBXA9g7gPDFTX-iLJP5Tr8Il0OkoaFPPSeQiXVcCFyRs36VkBEHoqZcWO9Mm5230eTuUrYI',
                'quantity' => 1,
                'unit_price' => 3499000,
                'subtotal' => 3499000,
                'shipping_fee' => 45000,
                'grand_total' => 3544000,
                'courier' => 'JNE Regular',
                'tracking_number' => 'JNE-992188321',
                'address' => 'Jl. Cempaka No. 12, Cakranegara, Mataram',
                'payment_method' => 'Virtual Account BCA',
                'payment_status' => 'paid',
                'status' => 'dikirim',
                'status_label' => 'Dikirim',
            ],
            [
                'order_number' => 'ORD-260418-02',
                'product_name' => 'NARA Meja Makan Solid Wood',
                'variant' => 'Natural Oak • 4 Kursi',
                'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAvs2NoIlUz-wIZDIuHHd_lz0FQTSwunHgrteixQrxuaA2tUXABlKInciOnr-eOSILVc3N2YF0yZyqKKBYerq0gNsuXuY4VxzwVFNh9PAaF_kL5ccwal4EcWl9LtjsJAFWCc8qsHcTaeUThHLc_QnmzW4WoLUSPJ_oCeCsLbqD6Q6ldoh_9xCVQKf4ajGc8sHUDhvhQVAhBww0KupnYEkuYIaAotylMZRK_kFp2ZLxDYZSQHYkgqvsO4jF4EjTm1huB3ScEC4QMbtw',
                'quantity' => 1,
                'unit_price' => 2150000,
                'subtotal' => 2150000,
                'shipping_fee' => 38000,
                'grand_total' => 2188000,
                'courier' => 'SiCepat REG',
                'tracking_number' => 'SICEPAT-44920121',
                'address' => 'Perumahan Green Hill Blok B-10, Lombok Barat',
                'payment_method' => 'GoPay',
                'payment_status' => 'paid',
                'status' => 'dikemas',
                'status_label' => 'Dikemas',
            ],
            [
                'order_number' => 'ORD-260419-03',
                'product_name' => 'IKARA Rak Buku Minimalis 5 Tingkat',
                'variant' => 'Walnut • 5 Tingkat',
                'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDpqsNkAgbqbjx2Il29wqoCj4ows9By6RReC2pX0jS3-liIzBWD_jyRIrgJoZlNwijY6izYwSuqlprH4Y-3gJJ9zemAyjKyTGDXdMeUGZ5lPTEZRt9oLrLyLtt4X_nqLazinYUNZOlYyEK6Q-FetFDaSJZSCLzO3JoAyDd1j5F-hdTzOBfrzgIQ4-EAyVEeQSVzkRuSCBXA9g7gPDFTX-iLJP5Tr8Il0OkoaFPPSeQiXVcCFyRs36VkBEHoqZcWO9Mm5230eTuUrYI',
                'quantity' => 1,
                'unit_price' => 1850000,
                'subtotal' => 1850000,
                'shipping_fee' => 35000,
                'grand_total' => 1885000,
                'courier' => 'JNE Regular',
                'tracking_number' => '-',
                'address' => 'Jl. Merdeka No. 5, Ampenan, Mataram',
                'payment_method' => 'GoPay',
                'payment_status' => 'paid',
                'status' => 'diproses',
                'status_label' => 'Diproses',
            ],
        ];

        foreach ($orders as $order) {
            ProductOrder::updateOrCreate(
                ['order_number' => $order['order_number']],
                array_merge($order, [
                    'mobile_user_id' => $user?->id,
                    'customer_name' => $user?->name ?? 'Pelanggan Maninjau',
                    'customer_email' => $user?->email ?? 'pelanggan@maninjau.app',
                    'customer_phone' => $user?->phone ?? '-',
                    'cancelled_at' => null,
                ]),
            );
        }
    }
}
