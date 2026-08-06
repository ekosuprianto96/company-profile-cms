<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Notifications\NotificationCatalog;

/**
 * Me-resolve teks notifikasi dari template (custom admin bila ada, jika tidak dari
 * default catalog) lalu mensubstitusi variabel {{ nama }} dengan data konteks +
 * variabel global (app_name, app_logo, dll).
 */
class NotificationTemplateService
{
    /**
     * @param  array<string,mixed>  $context  data variabel spesifik event
     * @return array{subject:string, body:string}
     */
    public function render(string $eventKey, string $channel, string $audience, array $context = []): array
    {
        $template = $this->resolveTemplate($eventKey, $channel, $audience);
        $vars = array_merge($this->globalContext(), $context);

        $subject = $this->substitute((string) ($template['subject'] ?? ''), $vars);
        $body = $this->substitute((string) ($template['body'] ?? ''), $vars);

        // Push / in-app / SMS = teks polos: buang HTML (editor boleh kaya, hasil bersih).
        if (in_array($channel, ['push', 'in_app', 'sms'], true)) {
            $body = $this->toPlainText($body);

            return ['subject' => $subject, 'body' => $body];
        }

        // Email: editor menyimpan body sebagai markdown → konversi ke HTML agar
        // simbol seperti ## / ** tidak tampil mentah di email.
        if ($channel === 'email') {
            $body = $this->markdownToHtml($body);
            $plain = $this->toPlainText($body); // versi teks (deliverability/anti-spam)

            // Desain terpilih: bungkus body ke dalam desain ({{ body }} diganti).
            if (! empty($template['email_design_id'])) {
                $design = \App\Models\EmailDesign::active()->find($template['email_design_id']);
                if ($design && $design->html) {
                    $full = $this->applyDesign((string) $design->html, array_merge($vars, ['body' => $body]));
                    $full = $this->injectPreheader($full, $this->substitute((string) $design->preheader, $vars));

                    return ['subject' => $subject, 'body' => $body, 'html' => $full, 'plain' => $plain];
                }
            }

            return ['subject' => $subject, 'body' => $body, 'plain' => $plain];
        }

        return ['subject' => $subject, 'body' => $body];
    }

    /** Substitusi teks bebas dengan konteks (dipakai untuk preview di editor). */
    public function renderText(string $text, array $context = [], bool $plain = false): string
    {
        $out = $this->substitute($text, array_merge($this->globalContext(), $context));

        return $plain ? $this->toPlainText($out) : $out;
    }

    /**
     * Terapkan konteks ke HTML desain email: {{ body }} diganti isi pesan, sisanya
     * (app_name, recipient_name, dsb) disubstitusi. Dipakai email builder & channel email.
     */
    public function applyDesign(string $designHtml, array $context = []): string
    {
        return $this->substitute($designHtml, array_merge($this->globalContext(), $context));
    }

    /**
     * Konversi teks editor (markdown dari CKEditor) → HTML. Aman untuk input yang
     * sudah HTML (dibiarkan lewat). Dipakai untuk body channel email.
     */
    public function markdownToHtml(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        return \Illuminate\Support\Str::markdown($text, [
            'html_input' => 'allow',
            'allow_unsafe_links' => true,
        ]);
    }

    /** Sisipkan preheader (teks pratinjau inbox) tersembunyi tepat setelah <body>. */
    public function injectPreheader(string $html, string $preheader): string
    {
        $preheader = trim($preheader);
        if ($preheader === '') {
            return $html;
        }

        $hidden = '<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#ffffff;opacity:0;">'
            . e($preheader) . '</div>';

        if (preg_match('/<body[^>]*>/i', $html)) {
            return preg_replace('/(<body[^>]*>)/i', '$1' . $hidden, $html, 1) ?? ($hidden . $html);
        }

        return $hidden . $html;
    }

    /** HTML dari CKEditor → teks polos rapi (untuk push/in-app). */
    public function toPlainText(string $html): string
    {
        $text = preg_replace('/<\/(p|div|br|li|h[1-6])\s*>/i', "$0\n", $html) ?? $html;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Rapikan spasi/baris berlebih.
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{2,}/", "\n", $text) ?? $text;

        return trim($text);
    }

    /** Nilai contoh (sample) untuk semua variabel event (global + spesifik). */
    public function sampleContext(string $eventKey): array
    {
        $ctx = [];
        foreach (NotificationCatalog::globalVariables() as $key => $def) {
            $ctx[$key] = $def['sample'] ?? '';
        }
        $event = NotificationCatalog::events()[$eventKey] ?? null;
        foreach (($event['variables'] ?? []) as $key => $def) {
            $ctx[$key] = $def['sample'] ?? '';
        }

        return $ctx;
    }

    /** Ambil template aktif dari DB; fallback ke default catalog. */
    protected function resolveTemplate(string $eventKey, string $channel, string $audience): array
    {
        // Cache lookup template (jarang berubah) — dibersihkan saat template disimpan/hapus
        // lewat model NotificationTemplate::booted(). Hemat 1 query tiap kirim notifikasi.
        return \Illuminate\Support\Facades\Cache::remember(
            "notif_tpl:{$eventKey}:{$channel}:{$audience}",
            now()->addHours(6),
            function () use ($eventKey, $channel, $audience) {
                $row = NotificationTemplate::query()
                    ->where('event_key', $eventKey)
                    ->where('channel', $channel)
                    ->where('audience', $audience)
                    ->where('is_active', true)
                    ->orderBy('is_default') // dahulukan custom (is_default=false=0) bila keduanya aktif
                    ->latest('id')
                    ->first();

                if ($row) {
                    return ['subject' => $row->subject, 'body' => $row->body, 'email_design_id' => $row->email_design_id];
                }

                $events = NotificationCatalog::events();
                return $events[$eventKey]['templates'][$channel . ':' . $audience] ?? ['subject' => '', 'body' => ''];
            }
        );
    }

    /**
     * Ganti {{ var }} dengan nilai. Toleran terhadap backslash/escape yang kadang
     * disisipkan editor markdown (mis. {{otp\_code}} → otp_code).
     */
    protected function substitute(string $text, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\\\\ ]+?)\s*\}\}/', function ($m) use ($vars) {
            $key = preg_replace('/[\\\\\s]+/', '', $m[1]); // buang backslash & spasi di nama variabel
            return array_key_exists($key, $vars) && $vars[$key] !== null ? (string) $vars[$key] : '';
        }, $text) ?? $text;
    }

    /** Variabel global yang tersedia di semua template. */
    public function globalContext(): array
    {
        $settings = null;
        try {
            $settings = config('settings');
        } catch (\Throwable) {
            $settings = null;
        }

        $val = fn (string $key, $default = '') => data_get($settings, "value.$key", data_get($settings, $key, $default));
        // Beberapa nilai setting bisa berupa array/object → paksa jadi string aman.
        $str = function ($v): string {
            if (is_array($v)) {
                $v = $v['url'] ?? $v['path'] ?? $v['value'] ?? '';
            }
            return is_scalar($v) ? (string) $v : '';
        };

        $logo = $str(config('app.logo'));
        if ($logo === '') {
            $logo = $str($val('app_logo', ''));
        }

        return [
            'app_name' => config('app.name') ?: 'Maninjau PRO',
            'app_logo' => $this->publicUrl($logo),
            'app_url' => config('app.url') ?: url('/'),
            'support_email' => $str($val('email', config('mail.from.address', ''))),
            'support_phone' => $str($val('phone', '')),
            'support_whatsapp' => $str($val('whatsapp', '')),
            'current_year' => date('Y'),
            'recipient_name' => '', // biasanya di-override oleh context per-penerima
        ];
    }

    private function publicUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }
        if (preg_match('#^https?://#', $path)) {
            return $path;
        }
        return function_exists('storageUrl') ? (string) storageUrl($path) : url($path);
    }
}
