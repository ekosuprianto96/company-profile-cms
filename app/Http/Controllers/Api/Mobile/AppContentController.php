<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\MobileContent;
use App\Models\MobileSupportContact;
use Illuminate\Http\Request;

class AppContentController extends ApiController
{
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
            'about' => $this->contentPayload($contents->get('about')),
            'terms' => $this->contentPayload($contents->get('terms')),
            'support_contacts' => $support,
        ], 'Konten aplikasi berhasil dimuat.');
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
