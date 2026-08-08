<?php

namespace App\Services;

use App\Models\PageConfig;

/**
 * Sumber tunggal konfigurasi page builder & home section.
 *
 * Dulu disimpan sebagai file (config/page.json, config/sections.json) yang
 * rentan hilang saat deploy/pull. Sekarang disimpan di DB (tabel page_configs).
 *
 * Tetap kompatibel-mundur: bila DB masih kosong tapi file lama masih ada di
 * server, isinya otomatis diimpor sekali ke DB (migrasi mulus, tanpa kehilangan
 * data). Bila keduanya kosong, mengembalikan array/JSON kosong — bukan error —
 * sehingga landing page tak pernah crash lagi.
 */
class PageConfigStore
{
    private const FILES = [
        'page' => 'config/page.json',
        'sections' => 'config/sections.json',
    ];

    // ---- Page (config/page.json) ----

    /** JSON string mentah — pengganti langsung file_get_contents(config/page.json). */
    public function pageJsonString(): string
    {
        return $this->rawString('page');
    }

    public function pageArray(): array
    {
        return $this->toArray($this->rawString('page'));
    }

    public function writePage(array $pages): void
    {
        $this->put('page', $pages);
    }

    // ---- Sections (config/sections.json) ----

    public function sectionsJsonString(): string
    {
        return $this->rawString('sections');
    }

    public function sectionsArray(): array
    {
        return $this->toArray($this->rawString('sections'));
    }

    public function writeSections(array $sections): void
    {
        $this->put('sections', $sections);
    }

    // ---- Internal ----

    private function rawString(string $key): string
    {
        try {
            $row = PageConfig::where('key', $key)->first();
            if ($row && $row->value !== null && $row->value !== '') {
                return $row->value;
            }
        } catch (\Throwable $e) {
            // Tabel belum ada (migrasi belum jalan) → jatuh ke file.
        }

        // Fallback + auto-impor dari file lama bila DB masih kosong.
        $path = base_path(self::FILES[$key]);
        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $this->tryPut($key, $decoded);

                return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return '[]';
    }

    private function toArray(string $json): array
    {
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function put(string $key, array $data): void
    {
        PageConfig::updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    private function tryPut(string $key, array $data): void
    {
        try {
            $this->put($key, $data);
        } catch (\Throwable $e) {
            // Tabel belum ada — abaikan; cukup pakai isi file.
        }
    }
}
