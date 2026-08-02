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
    public function run(): void
    {
        $count = 0;

        foreach (NotificationCatalog::events() as $eventKey => $event) {
            $eventLabel = $event['label'] ?? $eventKey;

            foreach (($event['templates'] ?? []) as $slot => $tpl) {
                [$channel, $audience] = array_pad(explode(':', $slot, 2), 2, 'user');

                NotificationTemplate::updateOrCreate(
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

                $count++;
            }
        }

        $this->command?->info("Notification templates default: {$count} entri.");
    }
}
