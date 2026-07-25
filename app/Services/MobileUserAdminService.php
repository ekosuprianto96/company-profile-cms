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

    /**
     * Blokir user: catat alasan/pelaku. Token TIDAK langsung dicabut massal —
     * middleware EnsureMobileUserActive menolak (403 + code account_blocked +
     * alasan) setiap request akun terblokir SEKALIGUS menghapus token yang
     * dipakai, sehingga aplikasi bisa menampilkan layar blokir beralasan lalu
     * auto-logout. Ini menutup semua aktivitas tanpa menghilangkan alasan.
     */
    public function banUser(int $id, string $reason, ?int $adminId): MobileUser
    {
        $user = $this->findUser($id);

        $user->update([
            'banned_at' => now(),
            'ban_reason' => $reason !== '' ? $reason : null,
            'banned_by' => $adminId,
        ]);

        return $user->fresh('bannedBy');
    }

    public function unbanUser(int $id): MobileUser
    {
        $user = $this->findUser($id);
        $user->update([
            'banned_at' => null,
            'ban_reason' => null,
            'banned_by' => null,
        ]);

        return $user->fresh();
    }

    /** Detail lengkap user untuk halaman admin: profil, statistik, & daftar terkait. */
    public function userDetail(int $id): array
    {
        $user = $this->mobileUserRepository->newQuery()
            ->withCount(['serviceRequests', 'proposals', 'productOrders', 'voucherClaims', 'addresses', 'tokens', 'otps'])
            ->with([
                'bannedBy:id,name',
                'addresses',
                'serviceRequests' => fn ($q) => $q->with('service:id,title')->latest()->limit(10),
                'productOrders' => fn ($q) => $q->latest()->limit(10),
                'proposals' => fn ($q) => $q->with('service:id,title')->latest()->limit(10),
                'voucherClaims' => fn ($q) => $q->with('voucher:id,name,code')->latest()->limit(10),
                'tokens' => fn ($q) => $q->latest('last_used_at')->limit(15),
            ])
            ->find($id);

        if (! $user) {
            throw new \Exception('User mobile tidak ditemukan.', 404);
        }

        $recentOtps = $this->mobileUserOtpRepository->newQuery()
            ->where('mobile_user_id', $id)->latest()->limit(10)->get();

        return [
            'user' => $user,
            'recentOtps' => $recentOtps,
            'stats' => [
                ['label' => 'Order Layanan', 'value' => $user->service_requests_count, 'icon' => 'ri-file-list-3-line', 'tone' => 'primary'],
                ['label' => 'Proposal', 'value' => $user->proposals_count, 'icon' => 'ri-draft-line', 'tone' => 'info'],
                ['label' => 'Order Produk', 'value' => $user->product_orders_count, 'icon' => 'ri-shopping-bag-3-line', 'tone' => 'success'],
                ['label' => 'Voucher Diklaim', 'value' => $user->voucher_claims_count, 'icon' => 'ri-coupon-3-line', 'tone' => 'warning'],
                ['label' => 'Alamat', 'value' => $user->addresses_count, 'icon' => 'ri-map-pin-line', 'tone' => 'secondary'],
                ['label' => 'Sesi Aktif', 'value' => $user->tokens_count, 'icon' => 'ri-device-line', 'tone' => 'dark'],
            ],
        ];
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
