<?php

namespace App\Services;

use App\Models\MobileUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MobileProfileService
{
    public function update(MobileUser $user, array $payload, ?UploadedFile $avatar = null): MobileUser
    {
        $updatePayload = [
            'name' => $payload['name'],
        ];

        if (array_key_exists('email', $payload)) {
            $updatePayload['email'] = $payload['email'] !== '' ? $payload['email'] : null;
        }

        if (array_key_exists('phone', $payload)) {
            $updatePayload['phone'] = $payload['phone'] !== '' ? $payload['phone'] : null;
        }

        if ($avatar) {
            $updatePayload['avatar_path'] = $this->storeAvatar($user, $avatar);
        }

        $user->update($updatePayload);

        return $user->fresh();
    }

    private function storeAvatar(MobileUser $user, UploadedFile $avatar): string
    {
        if (!empty($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $extension = $avatar->getClientOriginalExtension() ?: $avatar->extension() ?: 'jpg';
        $fileName = now()->format('Y-m-d') . '-' . Str::uuid() . '.' . $extension;

        return $avatar->storeAs('mobile/avatars', $fileName, 'public');
    }
}
