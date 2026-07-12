<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\VoucherService;
use Illuminate\Http\Request;

class VoucherController extends ApiController
{
    public function __construct(
        protected VoucherService $vouchers
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:active,used,expired'],
        ]);

        $status = $validated['status'] ?? 'active';

        return $this->success([
            'status' => $status,
            'vouchers' => $this->vouchers->listForUser($request->user(), $status),
        ], 'OK');
    }

    public function available(Request $request)
    {
        $validated = $request->validate([
            'order_type' => ['required', 'in:service,product'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'service_id' => ['nullable', 'integer'],
        ]);

        return $this->success([
            'groups' => $this->vouchers->availableForUser(
                $request->user(),
                $validated['order_type'],
                (int) $validated['subtotal'],
                $validated['service_id'] ?? null,
            ),
        ], 'OK');
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'order_type' => ['required', 'in:service,product'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'service_id' => ['nullable', 'integer'],
            'voucher_id' => ['nullable', 'integer'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($validated['voucher_id']) && empty($validated['code'])) {
            return $this->error('Pilih voucher atau masukkan kode.', 422);
        }

        $result = $this->vouchers->preview(
            $request->user(),
            $validated['order_type'],
            (int) $validated['subtotal'],
            $validated['service_id'] ?? null,
            $validated['voucher_id'] ?? null,
            $validated['code'] ?? null,
        );

        return $this->success($result, $result['message']);
    }
}
