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
        }

        return ['subject' => $subject, 'body' => $body];
    }

    /** Substitusi teks bebas dengan konteks (dipakai untuk preview di editor). */
    public function renderText(string $text, array $context = [], bool $plain = false): string
    {
        $out = $this->substitute($text, array_merge($this->globalContext(), $context));

        return $plain ? $this->toPlainText($out) : $out;
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
        $row = NotificationTemplate::query()
            ->where('event_key', $eventKey)
            ->where('channel', $channel)
            ->where('audience', $audience)
            ->where('is_active', true)
            ->orderBy('is_default') // dahulukan custom (is_default=false=0) bila keduanya aktif
            ->latest('id')
            ->first();

        if ($row) {
            return ['subject' => $row->subject, 'body' => $row->body];
        }

        $events = NotificationCatalog::events();
        return $events[$eventKey]['templates'][$channel . ':' . $audience] ?? ['subject' => '', 'body' => ''];
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
