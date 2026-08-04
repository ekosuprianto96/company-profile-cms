<?php

namespace App\Support;

/**
 * Sumber tunggal komponen email (email-safe: tabel + inline style).
 * Dipakai bersama oleh: Email Builder (palette blok), blank template baru,
 * dan seeder desain default — sehingga ketiganya konsisten.
 *
 * Token {{ var }} ditulis literal (disubstitusi saat kirim oleh NotificationTemplateService).
 */
class EmailBlocks
{
    public const ACCENT = '#275a56';

    // ---------------- Snippet blok (potongan yang bisa diseret) ----------------

    public static function headerText(string $accent = self::ACCENT, string $tagline = 'Tagline atau slogan singkat perusahaan'): string
    {
        $tag = $tagline !== ''
            ? '<div style="color:rgba(255,255,255,.82);font-size:12px;margin-top:5px;font-family:Arial,sans-serif;">' . $tagline . '</div>'
            : '';

        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="background:' . $accent . ';padding:22px 28px;"><span style="color:#ffffff;font-size:19px;font-weight:800;letter-spacing:.3px;font-family:Arial,sans-serif;">{{ app_name }}</span>' . $tag . '</td></tr></table>';
    }

    public static function headerLogo(string $accent = self::ACCENT): string
    {
        $logo = "data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22160%22%20height%3D%2244%22%3E%3Crect%20fill%3D%22%23ffffff22%22%20width%3D%22160%22%20height%3D%2244%22%20rx%3D%226%22%2F%3E%3Ctext%20x%3D%2280%22%20y%3D%2228%22%20fill%3D%22%23ffffff%22%20font-size%3D%2214%22%20font-family%3D%22Arial%22%20text-anchor%3D%22middle%22%3ELogo%3C%2Ftext%3E%3C%2Fsvg%3E";

        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="background:' . $accent . ';padding:24px 28px;"><img src="' . $logo . '" alt="Logo" style="display:inline-block;height:44px;max-width:200px;" /><div style="color:#ffffff;font-size:16px;font-weight:800;margin-top:10px;font-family:Arial,sans-serif;">{{ app_name }}</div></td></tr></table>';
    }

    /** Sel konten berpadding — pembungkus isi utama. */
    public static function contentSection(string $inner): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:26px 28px;font-family:Arial,sans-serif;">' . $inner . '</td></tr></table>';
    }

    public static function section(): string
    {
        return self::contentSection('<div style="font-size:15px;line-height:1.7;color:#334155;">Tulis konten di sini.</div>');
    }

    public static function twoCol(): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td width="50%" valign="top" style="padding:12px;font-family:Arial,sans-serif;font-size:14px;color:#334155;">Kolom kiri</td><td width="50%" valign="top" style="padding:12px;font-family:Arial,sans-serif;font-size:14px;color:#334155;">Kolom kanan</td></tr></table>';
    }

    public static function divider(): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:0 28px;"><div style="border-top:1px solid #e5e9ee;font-size:0;line-height:0;height:1px;">&nbsp;</div></td></tr></table>';
    }

    public static function spacer(): string
    {
        return '<div style="height:24px;line-height:24px;font-size:0;">&nbsp;</div>';
    }

    public static function footerFull(string $accent = self::ACCENT): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:24px 28px;background:#f8fafc;border-top:1px solid #eef1f0;font-family:Arial,sans-serif;"><p style="margin:0 0 8px;font-size:13.5px;font-weight:700;color:#334155;">{{ app_name }}</p><p style="margin:0 0 4px;font-size:12.5px;color:#64748b;">Email: <a href="mailto:{{ support_email }}" style="color:' . $accent . ';text-decoration:none;">{{ support_email }}</a></p><p style="margin:0 0 4px;font-size:12.5px;color:#64748b;">Telp / WhatsApp: {{ support_phone }}</p><p style="margin:14px 0 0;font-size:11px;color:#9aa8a4;line-height:1.6;">Email ini dikirim otomatis, mohon tidak membalas. &copy; {{ current_year }} {{ app_name }}. Semua hak cipta dilindungi.</p></td></tr></table>';
    }

    public static function footerSimple(string $accent = self::ACCENT): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:18px 28px;background:#f8fafc;border-top:1px solid #eef1f0;font-family:Arial,sans-serif;"><p style="margin:0;font-size:11.5px;color:#9aa8a4;">&copy; {{ current_year }} {{ app_name }} &nbsp;·&nbsp; <a href="mailto:{{ support_email }}" style="color:' . $accent . ';text-decoration:none;">{{ support_email }}</a></p></td></tr></table>';
    }

    public static function heading(string $text = 'Judul Email'): string
    {
        return '<h1 style="margin:0 0 14px;font-size:22px;line-height:1.3;color:#111827;font-weight:700;font-family:Arial,sans-serif;">' . $text . '</h1>';
    }

    public static function subheading(string $text = 'Sub judul'): string
    {
        return '<h2 style="margin:0 0 10px;font-size:17px;line-height:1.4;color:#1f2937;font-weight:700;font-family:Arial,sans-serif;">' . $text . '</h2>';
    }

    public static function greeting(): string
    {
        return '<p style="margin:0 0 12px;font-size:15px;color:#334155;font-family:Arial,sans-serif;">Halo <b>{{ recipient_name }}</b>,</p>';
    }

    public static function paragraph(string $text = 'Tulis isi paragraf di sini. Klik dua kali untuk menyunting teks.'): string
    {
        return '<p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#334155;font-family:Arial,sans-serif;">' . $text . '</p>';
    }

    public static function button(string $accent = self::ACCENT, string $label = 'Klik di sini', string $url = '{{ app_url }}'): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0 6px;"><tr><td style="border-radius:10px;background:' . $accent . ';"><a href="' . $url . '" style="display:inline-block;padding:12px 26px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px;font-family:Arial,sans-serif;">' . $label . '</a></td></tr></table>';
    }

    public static function note(string $text = 'Informasi penting untuk penerima.'): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0;"><tr><td style="padding:12px 14px;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:6px;font-size:13px;color:#334155;font-family:Arial,sans-serif;">' . $text . '</td></tr></table>';
    }

    public static function otp(): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:10px 0;"><tr><td align="center" style="padding:18px;background:#f1f5f9;border-radius:10px;"><div style="font-size:30px;font-weight:800;letter-spacing:8px;color:#111827;font-family:Arial,sans-serif;">{{ otp_code }}</div></td></tr></table>';
    }

    public static function image(): string
    {
        $img = "data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22560%22%20height%3D%22200%22%3E%3Crect%20fill%3D%22%23e2e8f0%22%20width%3D%22560%22%20height%3D%22200%22%2F%3E%3Ctext%20x%3D%22280%22%20y%3D%22108%22%20fill%3D%22%2394a3b8%22%20font-size%3D%2218%22%20font-family%3D%22Arial%22%20text-anchor%3D%22middle%22%3EGambar%3C%2Ftext%3E%3C%2Fsvg%3E";

        return '<img src="' . $img . '" alt="" style="display:block;width:100%;max-width:100%;border-radius:8px;" />';
    }

    public static function bodySlot(): string
    {
        return '<div style="padding:8px 0;font-size:15px;line-height:1.7;color:#334155;font-family:Arial,sans-serif;">{{ body }}</div>';
    }

    /** Logo aplikasi otomatis dari Settings ({{ app_logo }}). */
    public static function logoAuto(): string
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:8px 0;"><img src="{{ app_logo }}" alt="{{ app_name }}" style="display:inline-block;height:48px;max-width:220px;" /></td></tr></table>';
    }

    // ---------------- Palette untuk builder ----------------

    /** @return array<int, array{id:string,label:string,category:string,icon:string,content:string}> */
    public static function palette(string $accent = self::ACCENT): array
    {
        return [
            ['id' => 'eb-header', 'label' => 'Kop / Header (Teks)', 'category' => 'Struktur', 'icon' => 'ri-layout-top-2-line', 'content' => self::headerText($accent)],
            ['id' => 'eb-header-logo', 'label' => 'Kop / Header (Logo)', 'category' => 'Struktur', 'icon' => 'ri-image-2-line', 'content' => self::headerLogo($accent)],
            ['id' => 'eb-section', 'label' => 'Bagian (1 kolom)', 'category' => 'Struktur', 'icon' => 'ri-layout-row-line', 'content' => self::section()],
            ['id' => 'eb-2col', 'label' => 'Dua Kolom', 'category' => 'Struktur', 'icon' => 'ri-layout-column-line', 'content' => self::twoCol()],
            ['id' => 'eb-divider', 'label' => 'Garis Pemisah', 'category' => 'Struktur', 'icon' => 'ri-separator', 'content' => self::divider()],
            ['id' => 'eb-spacer', 'label' => 'Jarak Kosong', 'category' => 'Struktur', 'icon' => 'ri-space', 'content' => self::spacer()],
            ['id' => 'eb-footer', 'label' => 'Footer (Lengkap)', 'category' => 'Struktur', 'icon' => 'ri-layout-bottom-2-line', 'content' => self::footerFull($accent)],
            ['id' => 'eb-footer-simple', 'label' => 'Footer (Ringkas)', 'category' => 'Struktur', 'icon' => 'ri-subtract-line', 'content' => self::footerSimple($accent)],

            ['id' => 'eb-body', 'label' => 'Isi Pesan', 'category' => 'Konten', 'icon' => 'ri-file-text-line', 'content' => self::bodySlot()],
            ['id' => 'eb-heading', 'label' => 'Judul', 'category' => 'Konten', 'icon' => 'ri-heading', 'content' => self::heading()],
            ['id' => 'eb-subheading', 'label' => 'Sub Judul', 'category' => 'Konten', 'icon' => 'ri-h-2', 'content' => self::subheading()],
            ['id' => 'eb-greeting', 'label' => 'Sapaan', 'category' => 'Konten', 'icon' => 'ri-user-smile-line', 'content' => self::greeting()],
            ['id' => 'eb-text', 'label' => 'Paragraf', 'category' => 'Konten', 'icon' => 'ri-align-left', 'content' => self::paragraph()],
            ['id' => 'eb-button', 'label' => 'Tombol (CTA)', 'category' => 'Konten', 'icon' => 'ri-cursor-line', 'content' => self::button($accent)],
            ['id' => 'eb-note', 'label' => 'Kotak Info', 'category' => 'Konten', 'icon' => 'ri-information-line', 'content' => self::note()],
            ['id' => 'eb-otp', 'label' => 'Kotak Kode/OTP', 'category' => 'Konten', 'icon' => 'ri-key-2-line', 'content' => self::otp()],
            ['id' => 'eb-image', 'label' => 'Gambar', 'category' => 'Konten', 'icon' => 'ri-image-line', 'content' => self::image()],
            ['id' => 'eb-logo', 'label' => 'Logo Aplikasi', 'category' => 'Konten', 'icon' => 'ri-store-2-line', 'content' => self::logoAuto()],
        ];
    }

    // ---------------- Dokumen lengkap (blank & default) ----------------

    /** Bungkus rangkaian blok ke dalam kerangka email (outer bg + kartu 600px). */
    public static function document(string $inner): string
    {
        return <<<HTML
<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0; padding:0; background:#eef2f5;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f5; padding:24px 12px;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background:#ffffff; border-radius:14px; overflow:hidden;">
        <tr><td style="padding:0;">
{$inner}
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * Kerangka email default dari blok (header + konten + footer).
     * @param array{accent?:string,heading?:?string,greeting?:bool,cta?:?array{label:string,url:string},note?:?string,tagline?:string} $o
     */
    public static function scaffold(array $o = []): string
    {
        $accent = $o['accent'] ?? self::ACCENT;

        $inner = ($o['heading'] ?? null) !== null ? self::heading($o['heading']) : '';
        if ($o['greeting'] ?? false) {
            $inner .= self::greeting();
        }
        $inner .= self::bodySlot();
        if (! empty($o['cta'])) {
            $inner .= self::button($accent, $o['cta']['label'], $o['cta']['url']);
        }
        if (! empty($o['note'])) {
            $inner .= self::note($o['note']);
        }

        $body = self::headerText($accent, $o['tagline'] ?? '')
            . self::contentSection($inner)
            . self::footerFull($accent);

        return self::document($body);
    }
}
