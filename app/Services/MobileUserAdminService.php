<?php

namespace App\Services;

use App\Models\MobileUser;
use App\Repositories\MobileUserOtpRepository;
use App\Repositories\MobileUserRepository;
use Illuminate\Support\Facades\DB;

class MobileUserAdminService
{
    public function __construct(
        protected MobileUserRepository $mobileUserRepository,
        protected MobileUserOtpRepository $mobileUserOtpRepository
    ) {}

    public function userQuery()
    {
        return $this->mobileUserRepository
            ->withCount(['tokens', 'otps'])
            ->latest();
    }

    public function otpQuery()
    {
        return $this->mobileUserOtpRepository
            ->with('user')
            ->latest();
    }

    public function findUser(int $id): MobileUser
    {
        $user = $this->mobileUserRepository->find($id);

        if (! $user) {
            throw new \Exception('User mobile tidak ditemukan.', 404);
        }

        return $user;
    }

    public function toggleStatus(int $id): MobileUser
    {
        $user = $this->findUser($id);

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return $user->fresh();
    }

    public function revokeTokens(int $id): MobileUser
    {
        $user = $this->findUser($id);
        $user->tokens()->delete();

        return $user->fresh();
    }

    public function stats(): array
    {
        return [
            [
                'label' => 'Total Mobile Users',
                'value' => $this->mobileUserRepository->count(),
                'icon' => 'ri-smartphone-line',
                'tone' => 'primary',
            ],
            [
                'label' => 'User Terverifikasi',
                'value' => $this->mobileUserRepository
                    ->where(function ($query) {
                        $query->whereNotNull('email_verified_at')
                            ->orWhereNotNull('phone_verified_at');
                    })
                    ->count(),
                'icon' => 'ri-verified-badge-line',
                'tone' => 'success',
            ],
            [
                'label' => 'OTP Pending/Sent',
                'value' => $this->mobileUserOtpRepository
                    ->whereIn('status', ['pending', 'sent'])
                    ->count(),
                'icon' => 'ri-timer-flash-line',
                'tone' => 'warning',
            ],
            [
                'label' => 'Token Aktif',
                'value' => DB::table('personal_access_tokens')
                    ->where('tokenable_type', MobileUser::class)
                    ->count(),
                'icon' => 'ri-key-2-line',
                'tone' => 'info',
            ],
        ];
    }
}
