<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use App\Notifications\NotificationCatalog;
use Illuminate\Database\Seeder;

/**
 * Seed template notifikasi DEFAULT dari NotificationCatalog. Idempoten: aman
 * dijalankan berulang (updateOrCreate per event/channel/audience default) dan
 * tidak menyentuh template custom buatan admin (is_default=false).
 */
class NotificationTemplateSeeder extends Seeder
{
    /** Pemetaan event → slug desain email default (dari EmailDesignSeeder). */
    private const DESIGN_FOR = [
        'otp.mobile' => 'kode-verifikasi-otp',
        'otp.admin_login' => 'kode-verifikasi-otp',
        'service_request.submitted' => 'pengumuman',
        'service_request.approved' => 'layanan-disetujui',
        'service_request.rejected' => 'layanan-ditolak',
        'service_request.completed' => 'layanan-disetujui',
        'service_request.payment_method_selected' => 'pembayaran-berhasil',
        'service_request.payment_updated' => 'pembayaran-berhasil',
        'proposal.submitted' => 'pengumuman',
        'product_order.created' => 'pengumuman',
        'product_order.paid' => 'pembayaran-berhasil',
        'product_order.shipped' => 'pesanan-dikirim',
        'product_order.completed' => 'layanan-disetujui',
        'chat.message_to_user' => 'notifikasi-umum-minimal',
        'chat.message_to_admins' => 'notifikasi-umum-minimal',
        'campaign' => 'promo-penawaran',
        'contact_form' => 'pengumuman',
    ];

    public function run(): void
    {
        $count = 0;

        foreach (NotificationCatalog::events() as $eventKey => $event) {
            $eventLabel = $event['label'] ?? $eventKey;

            foreach (($event['templates'] ?? []) as $slot => $tpl) {
                [$channel, $audience] = array_pad(explode(':', $slot, 2), 2, 'user');

                $model = NotificationTemplate::updateOrCreate(
                    [
                        'event_key' => $eventKey,
                        'channel' => $channel,
                        'audience' => $audience,
                        'is_default' => true,
                    ],
                    [
                        'name' => sprintf('%s · %s · %s', $eventLabel, strtoupper($channel), $audience),
                        'subject' => $tpl['subject'] ?? '',
                        'body' => $tpl['body'] ?? '',
                        'is_active' => true,
                    ],
                );

                // Auto-pasang desain email default (hanya bila belum diset → hormati pilihan admin).
                if ($channel === 'email' && $model->email_design_id === null && isset(self::DESIGN_FOR[$eventKey])) {
                    $designId = \App\Models\EmailDesign::where('slug', self::DESIGN_FOR[$eventKey])->value('id');
                    if ($designId) {
                        $model->update(['email_design_id' => $designId]);
                    }
                }

                $count++;
            }
        }

        $this->command?->info("Notification templates default: {$count} entri.");
    }
}
