<?php

namespace App\Services;

use App\Models\MobilePushToken;
use App\Models\MobileUser;
use Illuminate\Support\Carbon;

class MobilePushTokenService
{
    public function sync(MobileUser $user, array $payload): MobilePushToken
    {
        return MobilePushToken::query()->updateOrCreate(
            ['expo_push_token' => $payload['expo_push_token']],
            [
                'mobile_user_id' => $user->id,
                'platform' => $payload['platform'] ?? null,
                'device_name' => $payload['device_name'] ?? null,
                'is_active' => true,
                'last_seen_at' => Carbon::now(),
            ]
        );
    }
}
