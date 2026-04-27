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

    public function surveyFee(): int
    {
        return (int) $this->getSettings()['survey_fee'];
    }

    public function taxPercentage(): float
    {
        return (float) $this->getSettings()['tax_percentage'];
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
        ];
    }
}
