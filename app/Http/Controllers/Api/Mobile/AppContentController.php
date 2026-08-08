<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\MobileContent;
use App\Models\MobileSupportContact;
use App\Services\MobileAppSettingService;
use Illuminate\Http\Request;

class AppContentController extends ApiController
{
    public function __construct(
        protected MobileAppSettingService $mobileAppSettingService,
    ) {}

    public function index(Request $request)
    {
        $contents = MobileContent::query()->whereIn('key', ['about', 'terms'])->get()->keyBy('key');

        $support = MobileSupportContact::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (MobileSupportContact $contact) => [
                'type' => $contact->type,
                'label' => $contact->label,
                'value' => $contact->value,
                'action_url' => $this->actionUrl($contact),
            ])
            ->values();

        return $this->success([
            'app_name' => $this->mobileAppSettingService->appName(),
            'logo_url' => $this->appLogoUrl(),
            'onboarding' => $this->mobileAppSettingService->onboardingSlides(),
            'about' => $this->contentPayload($contents->get('about')),
            'terms' => $this->contentPayload($contents->get('terms')),
            'support_contacts' => $support,
        ], 'Konten aplikasi berhasil dimuat.');
    }

    /** URL logo aplikasi (kop) dari settings — sama dengan yang dipakai invoice PDF. */
    private function appLogoUrl(): ?string
    {
        $logo = config('settings.value.app_logo');
        if (is_array($logo)) {
            $logo = $logo['file'] ?? $logo['url'] ?? null;
        } elseif (is_object($logo)) {
            $logo = $logo->file ?? $logo->url ?? null;
        }
        if (! is_string($logo) || $logo === '') {
            return null;
        }

        return str_starts_with($logo, 'http') ? $logo : image_url('informasi', $logo);
    }

    private function contentPayload(?MobileContent $content): ?array
    {
        if (! $content) {
            return null;
        }

        return [
            'title' => $content->title,
            'body' => $content->body,
            'updated_at' => optional($content->updated_at)?->toISOString(),
        ];
    }

    private function actionUrl(MobileSupportContact $contact): ?string
    {
        $value = trim($contact->value);

        return match ($contact->type) {
            'whatsapp' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value),
            'email' => 'mailto:' . $value,
            'phone' => 'tel:' . preg_replace('/[^0-9+]/', '', $value),
            'instagram' => str_starts_with($value, 'http') ? $value : 'https://instagram.com/' . ltrim($value, '@'),
            default => str_starts_with($value, 'http') ? $value : null,
        };
    }
}
