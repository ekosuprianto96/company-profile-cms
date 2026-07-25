<?php

namespace App\Services;

use App\Repositories\MobileAppSettingRepository;

class MobileAppSettingService
{
    public function __construct(
        protected MobileAppSettingRepository $mobileAppSettingRepository
    ) {}

    public function getSettings(): array
    {
        return array_merge($this->defaults(), $this->mobileAppSettingRepository->getSettings());
    }

    public function update(array $payload): array
    {
        $settings = array_merge($this->defaults(), $payload);

        $this->mobileAppSettingRepository->updateSettings($settings);

        cache()->forget('mobile_app_settings');
        cache()->forget('mobile_request_settings');
        cache()->forget('mobile_app_settings_bootstrap');

        return $settings;
    }

    public function appName(): string
    {
        $name = trim((string) ($this->getSettings()['app_name'] ?? ''));

        return $name !== '' ? $name : 'Maninjau PRO';
    }

    /** Slide onboarding mentah (dengan image_path). Fallback ke default bila kosong. */
    public function onboardingSlidesRaw(): array
    {
        $slides = $this->getSettings()['onboarding_slides'] ?? [];

        if (empty($slides) || ! is_array($slides)) {
            $slides = $this->defaults()['onboarding_slides'];
        }

        return collect($slides)->sortBy(fn ($s) => (int) ($s['sort_order'] ?? 0))->values()->all();
    }

    /** Slide onboarding (splash) untuk aplikasi — siap dipakai API (dengan image_url). */
    public function onboardingSlides(): array
    {
        return collect($this->onboardingSlidesRaw())
            ->map(fn ($s) => [
                'id' => $s['id'] ?? null,
                'title' => (string) ($s['title'] ?? ''),
                'subtitle' => (string) ($s['subtitle'] ?? ''),
                'image_url' => ! empty($s['image_path']) ? storageUrl($s['image_path']) : null,
            ])
            ->filter(fn ($s) => $s['title'] !== '' || $s['subtitle'] !== '' || $s['image_url'])
            ->values()
            ->all();
    }

    public function surveyFee(): int
    {
        return (int) $this->getSettings()['survey_fee'];
    }

    public function otpExpireMinutes(): int
    {
        return max(1, (int) ($this->getSettings()['otp_expire_minutes'] ?? config('mobile_auth.otp_expire_minutes', 10)));
    }

    public function eventConsultationFee(): int
    {
        return (int) $this->getSettings()['event_consultation_fee'];
    }

    public function taxPercentage(): float
    {
        return (float) $this->getSettings()['tax_percentage'];
    }

    public function invoiceTemplateService(): string
    {
        return $this->normalizeInvoiceTemplate($this->getSettings()['invoice_template_service'] ?? null, 'service');
    }

    public function invoiceTemplateProduct(): string
    {
        return $this->normalizeInvoiceTemplate($this->getSettings()['invoice_template_product'] ?? null, 'product');
    }

    protected function normalizeInvoiceTemplate(?string $value, string $type): string
    {
        $available = array_keys(config('invoice.available', []));
        $fallback = config('invoice.templates.' . $type, 'classic');

        return in_array($value, $available, true) ? $value : $fallback;
    }

    public function taxAmount(): int
    {
        $surveyFee = $this->surveyFee();
        $taxPercentage = $this->taxPercentage();

        return (int) round($surveyFee * ($taxPercentage / 100));
    }

    public function totalAmount(): int
    {
        return $this->surveyFee() + $this->taxAmount();
    }

    public function eventConsultationTaxAmount(): int
    {
        return (int) round($this->eventConsultationFee() * ($this->taxPercentage() / 100));
    }

    public function eventConsultationTotalAmount(): int
    {
        return $this->eventConsultationFee() + $this->eventConsultationTaxAmount();
    }

    public function surveyCoverage(): array
    {
        $settings = $this->getSettings();
        $coverage = $settings['survey_coverage'] ?? [];

        if (! is_array($coverage)) {
            $coverage = [];
        }

        $normalizeText = static function ($value): string {
            if (! is_string($value)) {
                return '';
            }

            return trim(preg_replace('/\s+/', ' ', $value) ?? '');
        };

        $normalizeRegion = static function ($value) use ($normalizeText): array {
            if (! is_array($value)) {
                $text = $normalizeText($value);

                return [
                    'code' => '',
                    'name' => $text,
                ];
            }

            return [
                'code' => $normalizeText($value['code'] ?? ''),
                'name' => $normalizeText($value['name'] ?? ''),
            ];
        };

        $rules = collect($coverage['rules'] ?? [])
            ->map(function ($rule, $index) use ($normalizeText, $normalizeRegion) {
                if (! is_array($rule)) {
                    return null;
                }

                $areaName = $normalizeText($rule['area_name'] ?? '');
                $province = $normalizeRegion($rule['province'] ?? []);
                $regency = $normalizeRegion($rule['regency'] ?? []);
                $district = $normalizeRegion($rule['district'] ?? []);
                $village = $normalizeRegion($rule['village'] ?? []);

                if ($areaName === '' && $province['code'] === '' && $regency['code'] === '' && $district['code'] === '' && $village['code'] === '') {
                    return null;
                }

                return [
                    'id' => (string) ($rule['id'] ?? ('survey-coverage-' . ($index + 1))),
                    'area_name' => $areaName,
                    'province' => $province,
                    'regency' => $regency,
                    'district' => $district,
                    'village' => $village,
                    'is_active' => (bool) ($rule['is_active'] ?? true),
                    'sort_order' => (int) ($rule['sort_order'] ?? ($index + 1)),
                ];
            })
            ->filter()
            ->sortBy('sort_order')
            ->values()
            ->all();

        return [
            'enabled' => (bool) ($coverage['enabled'] ?? false),
            'whatsapp_number' => $normalizeText($coverage['whatsapp_number'] ?? ''),
            'whatsapp_message' => $normalizeText($coverage['whatsapp_message'] ?? ''),
            'rules' => $rules,
        ];
    }

    public function paymentMethods(): array
    {
        $settings = $this->getSettings();
        $methods = [];

        if (($settings['payment_gateway']['enabled'] ?? true) === true) {
            $methods[] = ['value' => 'qris', 'label' => 'QRIS'];
            $methods[] = ['value' => 'va_bca', 'label' => 'Virtual Account BCA'];
            $methods[] = ['value' => 'va_bni', 'label' => 'Virtual Account BNI'];
            $methods[] = ['value' => 'va_mandiri', 'label' => 'Virtual Account Mandiri'];
            $methods[] = ['value' => 'gopay', 'label' => 'GoPay'];
            $methods[] = ['value' => 'dana', 'label' => 'DANA'];
            $methods[] = ['value' => 'ovo', 'label' => 'OVO'];
        }

        if (collect($this->manualTransfers())->contains(fn ($item) => (bool) ($item['is_active'] ?? false))) {
            $methods[] = ['value' => 'manual_transfer', 'label' => 'Manual Transfer'];
        }

        return $methods;
    }

    public function manualTransfers(): array
    {
        $settings = $this->getSettings();
        $manualTransfers = $settings['manual_transfers'] ?? [];

        if (! is_array($manualTransfers) || $manualTransfers === []) {
            $legacyTransfer = $settings['manual_transfer'] ?? null;

            if (is_array($legacyTransfer)) {
                $manualTransfers = [[
                    'id' => 'legacy-manual-transfer',
                    'bank_name' => $legacyTransfer['bank_name'] ?? 'BCA',
                    'account_name' => $legacyTransfer['account_name'] ?? '-',
                    'account_number' => $legacyTransfer['account_number'] ?? '-',
                    'notes' => $legacyTransfer['notes'] ?? '',
                    'is_active' => (bool) ($legacyTransfer['enabled'] ?? true),
                    'sort_order' => 1,
                ]];
            }
        }

        return collect($manualTransfers)
            ->map(function ($item, $index) {
                if (! is_array($item)) {
                    return null;
                }

                return [
                    'id' => (string) ($item['id'] ?? ('manual-transfer-' . ($index + 1))),
                    'bank_name' => (string) ($item['bank_name'] ?? 'BCA'),
                    'account_name' => (string) ($item['account_name'] ?? '-'),
                    'account_number' => (string) ($item['account_number'] ?? '-'),
                    'notes' => (string) ($item['notes'] ?? ''),
                    'is_active' => (bool) ($item['is_active'] ?? true),
                    'sort_order' => (int) ($item['sort_order'] ?? ($index + 1)),
                ];
            })
            ->filter()
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    protected function defaults(): array
    {
        return [
            'app_name' => 'Maninjau PRO',
            'onboarding_slides' => [
                ['id' => 'slide-1', 'title' => 'Wujudkan rumah impian dengan layanan terbaik', 'subtitle' => 'Furnitur, jasa bangun, dan inspirasi rumah dalam satu aplikasi yang hangat & mudah.', 'image_path' => null, 'sort_order' => 1],
                ['id' => 'slide-2', 'title' => 'Semua kebutuhan rumah, satu genggaman', 'subtitle' => 'Dari desain, bangun, hingga furnitur — dikerjakan tim terpercaya dan transparan.', 'image_path' => null, 'sort_order' => 2],
                ['id' => 'slide-3', 'title' => 'Aman, transparan, tepat waktu', 'subtitle' => 'Pantau progres pengajuan & pembayaran langsung dari aplikasi.', 'image_path' => null, 'sort_order' => 3],
            ],
            'survey_fee' => 150000,
            'event_consultation_fee' => 150000,
            'tax_percentage' => 0,
            'otp_expire_minutes' => (int) config('mobile_auth.otp_expire_minutes', 10),
            'invoice_template_service' => config('invoice.templates.service', 'classic'),
            'invoice_template_product' => config('invoice.templates.product', 'classic'),
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
        ];
    }
}
