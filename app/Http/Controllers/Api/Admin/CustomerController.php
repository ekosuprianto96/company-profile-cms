<?php

namespace App\Http\Controllers\Api\Admin;

use App\Services\MobileUserAdminService;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function __construct(protected MobileUserAdminService $userAdmin) {}

    public function show(int $id)
    {
        try {
            $detail = $this->userAdmin->userDetail($id);
            $user = $detail['user'];

            return $this->success([
                'customer' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'is_verified' => $user->isVerified(),
                    'is_banned' => $user->isBanned(),
                    'ban_reason' => $user->ban_reason,
                    'created_at' => optional($user->created_at)?->toISOString(),
                    'stats' => [
                        'service_requests' => $user->service_requests_count,
                        'product_orders' => $user->product_orders_count,
                        'proposals' => $user->proposals_count,
                        'vouchers' => $user->voucher_claims_count,
                    ],
                    'recent_service_requests' => $user->serviceRequests->map(fn ($sr) => [
                        'id' => $sr->id,
                        'code' => $sr->transaction_code_label ?? ('SR-' . $sr->id),
                        'title' => optional($sr->service)->title ?? 'Layanan',
                        'status' => $sr->status,
                    ])->values(),
                    'recent_product_orders' => $user->productOrders->map(fn ($po) => [
                        'id' => $po->id,
                        'code' => $po->order_number,
                        'status' => $po->status,
                    ])->values(),
                ],
            ], 'Detail pelanggan.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function ban(Request $request, int $id)
    {
        try {
            $validated = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
            $this->userAdmin->banUser($id, (string) ($validated['reason'] ?? ''), $request->user()->id);

            return $this->success([], 'Pelanggan diblokir. Sesi login dicabut.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function unban(int $id)
    {
        try {
            $this->userAdmin->unbanUser($id);

            return $this->success([], 'Blokir pelanggan dibuka.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
