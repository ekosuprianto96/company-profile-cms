<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\VoucherRedemption;
use App\Services\MobileProductOrderCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileProductOrderController extends ApiController
{
    public function __construct(
        protected MobileProductOrderCheckoutService $checkoutService
    ) {}

    public function couriers()
    {
        return $this->success(['couriers' => $this->checkoutService->couriers()], 'OK');
    }

    public function index(Request $request)
    {
        $orders = ProductOrder::query()
            ->where('mobile_user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return $this->success([
            'orders' => $orders->map(fn ($order) => $this->listPayload($order))->all(),
        ], 'OK');
    }

    public function show(Request $request, string $orderNumber)
    {
        $order = ProductOrder::query()
            ->with(['items.product:id,primary_image', 'reviews'])
            ->where('mobile_user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return $this->error('Pesanan tidak ditemukan.', 404);
        }

        return $this->success(['order' => $this->detailPayload($order)], 'OK');
    }

    public function checkout(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'integer'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'shipping_courier_id' => ['required', 'integer'],
                'voucher_id' => ['nullable', 'integer'],
                'recipient_name' => ['nullable', 'string', 'max:150'],
                'recipient_phone' => ['nullable', 'string', 'max:30'],
                'address' => ['required', 'string', 'max:500'],
                'notes' => ['nullable', 'string', 'max:500'],
                'service_request_id' => ['nullable', 'integer'],
                'linked_service_id' => ['nullable', 'integer', 'exists:mobile_services,id'],
            ]);

            $order = $this->checkoutService->checkout($request->user(), $validated);

            return $this->success(['order' => $this->detailPayload($order->load(['items.product:id,primary_image', 'reviews']))], 'Pesanan berhasil dibuat.');
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Throwable $th) {
            $status = (int) $th->getCode();

            return $this->error($th->getMessage(), $status >= 100 && $status <= 599 ? $status : 500);
        }
    }

    public function selectPaymentMethod(Request $request, string $orderNumber)
    {
        try {
            $validated = $request->validate([
                'payment_method' => ['required', 'string', 'max:50'],
            ]);

            $order = $this->checkoutService->selectPaymentMethod($request->user(), $orderNumber, $validated['payment_method']);

            return $this->success(['order' => $this->detailPayload($order->load(['items.product:id,primary_image', 'reviews']))], 'Metode pembayaran berhasil dipilih.');
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Throwable $th) {
            $status = (int) $th->getCode();

            return $this->error($th->getMessage(), $status >= 100 && $status <= 599 ? $status : 500);
        }
    }

    public function uploadPaymentProof(Request $request, string $orderNumber)
    {
        try {
            $validated = $request->validate([
                'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            ]);

            $order = $this->checkoutService->uploadPaymentProof($request->user(), $orderNumber, $validated['proof']);

            return $this->success(['order' => $this->detailPayload($order->load(['items.product:id,primary_image', 'reviews']))], 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Throwable $th) {
            $status = (int) $th->getCode();

            return $this->error($th->getMessage(), $status >= 100 && $status <= 599 ? $status : 500);
        }
    }

    public function cancel(Request $request, string $orderNumber)
    {
        $order = ProductOrder::query()
            ->with('items')
            ->where('mobile_user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return $this->error('Order tidak ditemukan.', 404);
        }

        if (! $order->canBeCancelled()) {
            return $this->error('Pesanan tidak dapat dibatalkan karena sudah diproses.', 422);
        }

        DB::transaction(function () use ($order) {
            // Kembalikan stok
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::query()->where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
            // Lepas voucher yang di-reserve
            VoucherRedemption::query()
                ->where('order_type', 'product')->where('order_id', $order->id)->where('status', 'reserved')
                ->update(['status' => 'released', 'released_at' => now()]);

            $order->update(['status' => 'cancelled', 'status_label' => 'Dibatalkan', 'cancelled_at' => now()]);
        });

        return $this->success([
            'order' => ['order_number' => $order->order_number, 'status' => 'cancelled', 'can_cancel' => false],
        ], 'Pesanan berhasil dibatalkan.');
    }

    protected function listPayload(ProductOrder $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'product_name' => $order->product_name,
            'image' => storageUrl($order->image),
            'quantity' => (int) $order->quantity,
            'grand_total' => (int) $order->grand_total,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'payment_status' => $order->payment_status,
            'courier' => $order->courier,
            'tracking_number' => $order->tracking_number,
            'can_cancel' => $order->canBeCancelled(),
            'created_at' => optional($order->created_at)?->toISOString(),
        ];
    }

    protected function detailPayload(ProductOrder $order): array
    {
        // Review pesanan ini, dipetakan per produk agar tiap item tahu apakah
        // sudah dinilai (dipakai layar penilaian & untuk sembunyikan CTA).
        $reviewsByProduct = $order->reviews->keyBy('product_id');
        $completed = $order->isCompleted();

        $items = $order->items->map(function ($item) use ($reviewsByProduct, $completed) {
            $review = $item->product_id ? $reviewsByProduct->get($item->product_id) : null;

            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'subtotal' => (int) $item->subtotal,
                'image' => storageUrl($item->product?->primary_image),
                // Hanya produk yang masih terhubung (product_id ada) yang bisa dinilai.
                'can_review' => $completed && $item->product_id && ! $review,
                'reviewed' => (bool) $review,
                'review' => $review ? [
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                ] : null,
            ];
        })->all();

        return array_merge($this->listPayload($order), [
            'subtotal' => (int) $order->subtotal,
            'discount_amount' => (int) $order->discount_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'address' => $order->address,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'notes' => $order->notes,
            'payment_method' => $order->payment_method,
            'payment_gateway_provider' => $order->payment_gateway_provider,
            'payment_proof_url' => storageUrl($order->payment_proof_path),
            'payment_proof_uploaded_at' => optional($order->payment_proof_uploaded_at)?->toISOString(),
            'paid_at' => optional($order->paid_at)?->toISOString(),
            'payment_data' => $order->getAttribute('payment_data') ?? null,
            // True bila pesanan selesai & masih ada minimal 1 produk belum dinilai.
            'can_review' => $completed && collect($items)->contains('can_review', true),
            'items' => $items,
        ]);
    }

}
