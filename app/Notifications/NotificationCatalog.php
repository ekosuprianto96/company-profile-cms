<?php

namespace App\Notifications;

/**
 * Katalog (registry) semua jenis notifikasi: label, variabel yang tersedia, dan
 * teks TEMPLATE DEFAULT per channel (email/push/in_app) + audience (user/admin).
 *
 * Sumber tunggal untuk: seeder template default, palette variabel di editor, dan
 * daftar event di UI. Menambah notifikasi baru = tambah entri di sini.
 */
class NotificationCatalog
{
    /** Variabel yang tersedia di SEMUA event. */
    public static function globalVariables(): array
    {
        return [
            'app_name' => ['label' => 'Nama aplikasi', 'sample' => 'Maninjau PRO'],
            'app_logo' => ['label' => 'URL logo aplikasi', 'sample' => 'https://maninjau.id/logo.png'],
            'app_url' => ['label' => 'URL aplikasi/web', 'sample' => 'https://maninjau.id'],
            'recipient_name' => ['label' => 'Nama penerima', 'sample' => 'Eko Suprianto'],
            'support_email' => ['label' => 'Email support', 'sample' => 'support@maninjau.id'],
            'support_phone' => ['label' => 'Telepon support', 'sample' => '0800-1234-5678'],
            'support_whatsapp' => ['label' => 'WhatsApp support', 'sample' => '6281234567890'],
            'current_year' => ['label' => 'Tahun sekarang', 'sample' => '2026'],
        ];
    }

    /**
     * Definisi event. Struktur tiap event:
     *   label, group, variables[name=>[label,sample]], templates['channel:audience'=>[subject,body]]
     */
    public static function events(): array
    {
        return [
            /* ============ OTP ============ */
            'otp.mobile' => [
                'label' => 'OTP pengguna (mobile)',
                'group' => 'Autentikasi',
                'variables' => [
                    'otp_code' => ['label' => 'Kode OTP', 'sample' => '482913'],
                    'otp_expire_minutes' => ['label' => 'Kadaluarsa (menit)', 'sample' => '10'],
                    'otp_purpose' => ['label' => 'Tujuan OTP', 'sample' => 'verifikasi akun'],
                ],
                'templates' => [
                    'email:user' => [
                        'subject' => 'Kode OTP {{app_name}} Anda',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Kode OTP Anda untuk {{otp_purpose}} adalah:</p><h2>{{otp_code}}</h2><p>Berlaku {{otp_expire_minutes}} menit. Jangan bagikan kode ini kepada siapa pun.</p><p>Salam,<br>{{app_name}}</p>',
                    ],
                    'sms:user' => [
                        'subject' => '',
                        'body' => '{{app_name}}: Kode OTP Anda {{otp_code}}. Berlaku {{otp_expire_minutes}} menit. Jangan bagikan ke siapa pun.',
                    ],
                ],
            ],
            'otp.admin_login' => [
                'label' => 'OTP login admin',
                'group' => 'Autentikasi',
                'variables' => [
                    'otp_code' => ['label' => 'Kode OTP', 'sample' => '739210'],
                    'otp_expire_minutes' => ['label' => 'Kadaluarsa (menit)', 'sample' => '10'],
                ],
                'templates' => [
                    'email:admin' => [
                        'subject' => 'Kode OTP Login Admin {{app_name}}',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Kode OTP untuk masuk ke dashboard admin:</p><h2>{{otp_code}}</h2><p>Berlaku {{otp_expire_minutes}} menit.</p>',
                    ],
                ],
            ],

            /* ============ PENGAJUAN LAYANAN ============ */
            'service_request.submitted' => [
                'label' => 'Pengajuan dibuat',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars(),
                'templates' => [
                    'email:user' => [
                        'subject' => 'Pengajuan {{transaction_code}} diterima — {{app_name}}',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Pengajuan <b>{{service_title}}</b> ({{transaction_code}}) telah kami terima dan sedang ditinjau tim kami.</p><p>Jadwal survei: {{survey_date}}<br>Total: {{total_amount}}</p><p>Terima kasih,<br>{{app_name}}</p>',
                    ],
                    'push:user' => ['subject' => 'Pengajuan diterima', 'body' => 'Pengajuan {{service_title}} ({{transaction_code}}) sedang ditinjau.'],
                    'in_app:user' => ['subject' => 'Pengajuan diterima', 'body' => 'Pengajuan {{service_title}} ({{transaction_code}}) sedang ditinjau tim kami.'],
                    'push:admin' => ['subject' => 'Pengajuan baru', 'body' => '{{customer_name}} mengajukan {{service_title}} ({{transaction_code}}).'],
                    'in_app:admin' => ['subject' => 'Pengajuan baru', 'body' => 'Pengajuan baru {{transaction_code}} — {{service_title}} dari {{customer_name}}.'],
                    'email:admin' => ['subject' => 'Pengajuan survey baru — {{transaction_code}}', 'body' => '<p>Pengajuan baru masuk:</p><ul><li>Kode: <b>{{transaction_code}}</b></li><li>Layanan: {{service_title}}</li><li>Pelanggan: {{customer_name}}</li><li>Jadwal survei: {{survey_date}}</li><li>Total: {{total_amount}}</li></ul><p>Mohon segera ditinjau di dashboard admin.</p>'],
                ],
            ],
            'service_request.approved' => [
                'label' => 'Pengajuan disetujui',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars(['admin_note' => ['label' => 'Catatan admin', 'sample' => 'Survei dijadwalkan pekan depan.']]),
                'templates' => [
                    'email:user' => [
                        'subject' => 'Pengajuan {{transaction_code}} disetujui',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Kabar baik! Pengajuan <b>{{service_title}}</b> ({{transaction_code}}) telah <b>disetujui</b>.</p><p>{{admin_note}}</p><p>Salam,<br>{{app_name}}</p>',
                    ],
                    'push:user' => ['subject' => 'Pengajuan disetujui', 'body' => '{{service_title}} ({{transaction_code}}) telah disetujui.'],
                    'in_app:user' => ['subject' => 'Pengajuan disetujui', 'body' => 'Pengajuan {{service_title}} ({{transaction_code}}) disetujui. {{admin_note}}'],
                ],
            ],
            'service_request.rejected' => [
                'label' => 'Pengajuan ditolak',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars(['rejection_reason' => ['label' => 'Alasan penolakan', 'sample' => 'Lokasi di luar jangkauan.']]),
                'templates' => [
                    'email:user' => [
                        'subject' => 'Pengajuan {{transaction_code}} ditolak',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Mohon maaf, pengajuan <b>{{service_title}}</b> ({{transaction_code}}) belum dapat kami proses.</p><p>Alasan: {{rejection_reason}}</p><p>Silakan hubungi kami untuk info lebih lanjut.</p><p>Salam,<br>{{app_name}}</p>',
                    ],
                    'push:user' => ['subject' => 'Pengajuan ditolak', 'body' => '{{service_title}} ({{transaction_code}}) ditolak: {{rejection_reason}}'],
                    'in_app:user' => ['subject' => 'Pengajuan ditolak', 'body' => 'Pengajuan {{transaction_code}} ditolak. Alasan: {{rejection_reason}}'],
                ],
            ],
            'service_request.completed' => [
                'label' => 'Pengajuan selesai',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars(),
                'templates' => [
                    'email:user' => [
                        'subject' => 'Pengajuan {{transaction_code}} selesai',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Pekerjaan untuk <b>{{service_title}}</b> ({{transaction_code}}) telah <b>selesai</b>. Terima kasih telah mempercayai {{app_name}}.</p>',
                    ],
                    'push:user' => ['subject' => 'Pengajuan selesai', 'body' => '{{service_title}} ({{transaction_code}}) telah selesai.'],
                    'in_app:user' => ['subject' => 'Pengajuan selesai', 'body' => 'Pengajuan {{transaction_code}} telah selesai.'],
                ],
            ],
            'service_request.payment_method_selected' => [
                'label' => 'Metode pembayaran dipilih',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars([
                    'payment_method' => ['label' => 'Metode pembayaran', 'sample' => 'Transfer Manual'],
                ]),
                'templates' => [
                    'email:user' => [
                        'subject' => 'Instruksi pembayaran {{transaction_code}}',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Anda memilih {{payment_method}} untuk pengajuan {{transaction_code}}. Total yang harus dibayar: <b>{{total_amount}}</b>.</p>',
                    ],
                    'push:user' => ['subject' => 'Menunggu pembayaran', 'body' => 'Selesaikan pembayaran {{total_amount}} untuk {{transaction_code}}.'],
                    'in_app:user' => ['subject' => 'Menunggu pembayaran', 'body' => 'Total {{total_amount}} untuk {{transaction_code}} via {{payment_method}}.'],
                ],
            ],
            'service_request.payment_updated' => [
                'label' => 'Status pembayaran diperbarui',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars(['payment_status' => ['label' => 'Status pembayaran', 'sample' => 'Lunas']]),
                'templates' => [
                    'push:user' => ['subject' => 'Pembayaran diperbarui', 'body' => 'Pembayaran {{transaction_code}}: {{payment_status}}.'],
                    'in_app:user' => ['subject' => 'Pembayaran diperbarui', 'body' => 'Status pembayaran {{transaction_code}} kini {{payment_status}}.'],
                    'in_app:admin' => ['subject' => 'Pembayaran diperbarui', 'body' => 'Pembayaran {{transaction_code}} dari {{customer_name}}: {{payment_status}}.'],
                ],
            ],
            'service_request.step_completed' => [
                'label' => 'Step pengajuan tercapai (Template Rules Step)',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars([
                    'step_name' => ['label' => 'Nama step', 'sample' => 'Menunggu Survey'],
                    'step_description' => ['label' => 'Keterangan step', 'sample' => 'Tim akan menghubungi dan melakukan survey ke lokasi.'],
                ]),
                'templates' => [
                    'email:user' => [
                        'subject' => '{{step_name}} — {{transaction_code}}',
                        'body' => '<p>Halo {{recipient_name}},</p><p>Pengajuan <b>{{service_title}}</b> ({{transaction_code}}) kini memasuki tahap <b>{{step_name}}</b>.</p><p>{{step_description}}</p><p>Terima kasih,<br>{{app_name}}</p>',
                    ],
                    'push:user' => ['subject' => '{{step_name}}', 'body' => '{{transaction_code}}: {{step_description}}'],
                    'in_app:user' => ['subject' => '{{step_name}}', 'body' => 'Pengajuan {{service_title}} ({{transaction_code}}): {{step_description}}'],
                    'sms:user' => ['subject' => '', 'body' => '{{app_name}}: Pengajuan {{transaction_code}} memasuki tahap {{step_name}}. {{step_description}}'],
                    'in_app:admin' => ['subject' => 'Step pengajuan: {{step_name}}', 'body' => 'Pengajuan {{transaction_code}} ({{customer_name}}) mencapai step {{step_name}}.'],
                ],
            ],
            'proposal.submitted' => [
                'label' => 'Proposal dikirim',
                'group' => 'Pengajuan Layanan',
                'variables' => self::serviceRequestVars(['proposal_number' => ['label' => 'Nomor proposal', 'sample' => 'PRP-000123']]),
                'templates' => [
                    'push:user' => ['subject' => 'Proposal terkirim', 'body' => 'Proposal {{proposal_number}} untuk {{service_title}} telah dikirim.'],
                    'in_app:user' => ['subject' => 'Proposal terkirim', 'body' => 'Proposal {{proposal_number}} berhasil dikirim.'],
                    'in_app:admin' => ['subject' => 'Proposal baru', 'body' => 'Proposal {{proposal_number}} dari {{customer_name}} untuk {{service_title}}.'],
                ],
            ],

            /* ============ ORDER PRODUK ============ */
            'product_order.created' => [
                'label' => 'Order produk dibuat',
                'group' => 'Order Produk',
                'variables' => self::productOrderVars(),
                'templates' => [
                    'email:user' => ['subject' => 'Order {{order_number}} diterima', 'body' => '<p>Halo {{recipient_name}},</p><p>Order <b>{{product_name}}</b> ({{order_number}}) sebesar {{grand_total}} telah kami terima.</p>'],
                    'push:user' => ['subject' => 'Order diterima', 'body' => 'Order {{order_number}} ({{grand_total}}) sedang diproses.'],
                    'in_app:user' => ['subject' => 'Order diterima', 'body' => 'Order {{order_number}} — {{product_name}} sedang diproses.'],
                    'in_app:admin' => ['subject' => 'Order produk baru', 'body' => 'Order {{order_number}} dari {{customer_name}} — {{grand_total}}.'],
                ],
            ],
            'product_order.paid' => [
                'label' => 'Order produk dibayar',
                'group' => 'Order Produk',
                'variables' => self::productOrderVars(),
                'templates' => [
                    'push:user' => ['subject' => 'Pembayaran diterima', 'body' => 'Pembayaran order {{order_number}} diterima. Pesanan diproses.'],
                    'in_app:user' => ['subject' => 'Pembayaran diterima', 'body' => 'Order {{order_number}} lunas, sedang disiapkan.'],
                    'in_app:admin' => ['subject' => 'Order dibayar', 'body' => 'Order {{order_number}} ({{grand_total}}) telah dibayar.'],
                ],
            ],
            'product_order.shipped' => [
                'label' => 'Order produk dikirim',
                'group' => 'Order Produk',
                'variables' => self::productOrderVars([
                    'tracking_number' => ['label' => 'No. resi', 'sample' => 'JNE1234567890'],
                    'courier' => ['label' => 'Kurir', 'sample' => 'JNE'],
                ]),
                'templates' => [
                    'push:user' => ['subject' => 'Pesanan dikirim', 'body' => 'Order {{order_number}} dikirim via {{courier}} (resi {{tracking_number}}).'],
                    'in_app:user' => ['subject' => 'Pesanan dikirim', 'body' => 'Order {{order_number}} dalam pengiriman. Resi: {{tracking_number}}.'],
                ],
            ],
            'product_order.completed' => [
                'label' => 'Order produk selesai',
                'group' => 'Order Produk',
                'variables' => self::productOrderVars(),
                'templates' => [
                    'push:user' => ['subject' => 'Pesanan selesai', 'body' => 'Order {{order_number}} selesai. Terima kasih!'],
                    'in_app:user' => ['subject' => 'Pesanan selesai', 'body' => 'Order {{order_number}} telah selesai.'],
                ],
            ],

            /* ============ CHAT ============ */
            'chat.message_to_user' => [
                'label' => 'Pesan chat masuk (ke pengguna)',
                'group' => 'Live Chat',
                'variables' => [
                    'sender_name' => ['label' => 'Nama pengirim (admin)', 'sample' => 'Admin Maninjau'],
                    'message_preview' => ['label' => 'Cuplikan pesan', 'sample' => 'Halo, ada yang bisa dibantu?'],
                ],
                'templates' => [
                    'push:user' => ['subject' => '{{sender_name}}', 'body' => '{{message_preview}}'],
                    'in_app:user' => ['subject' => 'Pesan dari {{sender_name}}', 'body' => '{{message_preview}}'],
                ],
            ],
            'chat.message_to_admins' => [
                'label' => 'Pesan chat masuk (ke admin)',
                'group' => 'Live Chat',
                'variables' => [
                    'sender_name' => ['label' => 'Nama pengirim (pelanggan)', 'sample' => 'Eko Suprianto'],
                    'message_preview' => ['label' => 'Cuplikan pesan', 'sample' => 'Kapan tim survei datang?'],
                ],
                'templates' => [
                    'in_app:admin' => ['subject' => 'Pesan dari {{sender_name}}', 'body' => '{{message_preview}}'],
                ],
            ],

            /* ============ LAINNYA ============ */
            'campaign' => [
                'label' => 'Kampanye / broadcast',
                'group' => 'Kampanye',
                'variables' => [
                    'campaign_title' => ['label' => 'Judul kampanye', 'sample' => 'Promo Ramadhan'],
                    'campaign_message' => ['label' => 'Isi pesan kampanye', 'sample' => 'Diskon 10% untuk semua layanan!'],
                ],
                'templates' => [
                    'push:user' => ['subject' => '{{campaign_title}}', 'body' => '{{campaign_message}}'],
                    'in_app:user' => ['subject' => '{{campaign_title}}', 'body' => '{{campaign_message}}'],
                    'email:user' => ['subject' => '{{campaign_title}} — {{app_name}}', 'body' => '<p>Halo {{recipient_name}},</p><p>{{campaign_message}}</p><p>Salam,<br>{{app_name}}</p>'],
                ],
            ],
            'contact_form' => [
                'label' => 'Form kontak masuk',
                'group' => 'Website',
                'variables' => [
                    'sender_name' => ['label' => 'Nama pengirim', 'sample' => 'Budi'],
                    'sender_email' => ['label' => 'Email pengirim', 'sample' => 'budi@mail.com'],
                    'sender_phone' => ['label' => 'Telepon pengirim', 'sample' => '0812-3456-7890'],
                    'subject' => ['label' => 'Subjek', 'sample' => 'Konsultasi layanan'],
                    'sender_message' => ['label' => 'Isi pesan', 'sample' => 'Saya ingin konsultasi.'],
                ],
                'templates' => [
                    'email:admin' => ['subject' => 'Pesan kontak baru: {{subject}}', 'body' => '<p>Pesan baru dari form kontak website:</p><ul><li>Nama: <b>{{sender_name}}</b></li><li>Email: {{sender_email}}</li><li>Telepon: {{sender_phone}}</li><li>Subjek: {{subject}}</li></ul><blockquote style="border-left:3px solid #e2e8f0;padding-left:12px;color:#475569;">{{sender_message}}</blockquote>'],
                ],
            ],
        ];
    }

    /** Variabel umum event pengajuan layanan (+ tambahan opsional). */
    private static function serviceRequestVars(array $extra = []): array
    {
        return array_merge([
            'transaction_code' => ['label' => 'Kode transaksi', 'sample' => 'SR-EK00065'],
            'service_title' => ['label' => 'Nama layanan', 'sample' => 'Wedding Organizer'],
            'customer_name' => ['label' => 'Nama pelanggan', 'sample' => 'Eko Suprianto'],
            'survey_date' => ['label' => 'Tanggal survei', 'sample' => '26 Jul 2026'],
            'survey_address' => ['label' => 'Alamat survei', 'sample' => 'Jl. Cipinang No. 1'],
            'total_amount' => ['label' => 'Total biaya', 'sample' => 'Rp 150.000'],
        ], $extra);
    }

    /** Variabel umum event order produk (+ tambahan opsional). */
    private static function productOrderVars(array $extra = []): array
    {
        return array_merge([
            'order_number' => ['label' => 'Nomor order', 'sample' => 'ORD-260726-3569D'],
            'product_name' => ['label' => 'Nama produk', 'sample' => 'HAKATA Sofa 3 Dudukan'],
            'customer_name' => ['label' => 'Nama pelanggan', 'sample' => 'Eko Suprianto'],
            'grand_total' => ['label' => 'Total order', 'sample' => 'Rp 3.649.000'],
        ], $extra);
    }
}
