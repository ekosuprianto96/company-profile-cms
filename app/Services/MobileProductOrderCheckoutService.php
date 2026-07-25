<?php

namespace App\Services;

use App\Models\MobileUser;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ShippingCourier;
use App\Models\MobileService;
use App\Models\MobileServiceRequest;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MobileProductOrderCheckoutService
{
    public function __construct(
        protected VoucherService $voucherService,
        protected MobileMidtransService $mobileMidtransService,
        protected MobileAppSettingService $mobileAppSettingService,
    ) {}

    /** Kurir aktif; kurir internal siap dipakai, jasa kurir pihak ke-3 ditandai belum aktif. */
    public function couriers(): array
    {
        return ShippingCourier::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'is_third_party' => (bool) $c->is_third_party,
                'available' => ! $c->is_third_party, // pihak ke-3 belum aktif
                'etd' => $c->etd,
                'base_cost' => (int) $c->base_cost,
            ])->all();
    }

    public function checkout(MobileUser $user, array $payload): ProductOrder
    {
        return DB::transaction(function () use ($user, $payload) {
            $courier = ShippingCourier::query()->where('is_active', true)->find($payload['shipping_courier_id'] ?? null);
            if (! $courier) {
                throw new \Exception('Kurir tidak valid.', 422);
            }
            if ($courier->is_third_party) {
                throw new \Exception('Jasa kurir pihak ke-3 belum tersedia. Gunakan kurir internal.', 422);
            }

            $lines = [];
            $subtotal = 0;
            $internalShipping = 0;

            foreach ($payload['items'] as $line) {
                $product = Product::query()->where('is_active', true)->lockForUpdate()->find($line['product_id']);
                if (! $product) {
                    throw new \Exception('Produk tidak ditemukan atau tidak aktif.', 422);
                }
                $qty = max(1, (int) $line['quantity']);
                if ($product->stock < $qty) {
                    throw new \Exception('Stok "' . $product->name . '" tidak mencukupi.', 422);
                }
                $lineSubtotal = (int) $product->price * $qty;
                $subtotal += $lineSubtotal;
                $internalShipping += (int) ($product->internal_shipping_fee ?? 0) * $qty;

                $lines[] = ['product' => $product, 'qty' => $qty, 'subtotal' => $lineSubtotal];
            }

            if ($subtotal <= 0) {
                throw new \Exception('Keranjang kosong.', 422);
            }

            $shippingCost = $internalShipping > 0 ? $internalShipping : (int) $courier->base_cost;

            // Voucher (order_type=product)
            $discount = 0;
            $voucher = null;
            if (! empty($payload['voucher_id'])) {
                $voucher = Voucher::query()->with('targetItems')
                    ->where('order_type', 'product')->where('is_active', true)
                    ->where(fn ($q) => $q->where('user_scope', 'all')
                        ->orWhereHas('targetUsers', fn ($u) => $u->where('mobile_users.id', $user->id)))
                    ->lockForUpdate()->find($payload['voucher_id']);
                if (! $voucher) {
                    throw new \Exception('Voucher tidak tersedia.', 422);
                }
                $reason = $this->voucherService->ineligibilityReason($voucher, $user, 'product', $subtotal, null);
                if ($reason) {
                    throw new \Exception($reason, 422);
                }
                $discount = $this->voucherService->calculateDiscount($voucher, $subtotal);
            }

            // Gabung layanan (Phase 6): buat pengajuan survey berbayar minimal yang tertaut
            // ke order ini; biaya survey + pajaknya ditambahkan ke total order (1 transaksi).
            $linkedServiceRequest = null;
            $surveyCharge = 0;
            if (! empty($payload['linked_service_id'])) {
                $service = MobileService::where('is_active', true)->find($payload['linked_service_id']);
                if (! $service) {
                    throw new \Exception('Layanan yang dipilih tidak tersedia.', 422);
                }

                $surveyFee = $this->mobileAppSettingService->surveyFee();
                $taxPercentage = $this->mobileAppSettingService->taxPercentage();
                $surveyTax = (int) round($surveyFee * $taxPercentage / 100);
                $surveyCharge = $surveyFee + $surveyTax;

                $linkedServiceRequest = MobileServiceRequest::create([
                    'mobile_user_id' => $user->id,
                    'mobile_service_id' => $service->id,
                    'request_flow_type' => 'standard',
                    'description' => 'Digabung dari order produk (jasa pemasangan).',
                    'survey_address' => $payload['address'] ?? '-',
                    'survey_latitude' => 0,
                    'survey_longitude' => 0,
                    'survey_date' => now()->addDays(3)->toDateString(),
                    'survey_fee' => $surveyFee,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $surveyTax,
                    'total_amount' => $surveyCharge,
                    'status' => 'waiting_payment',
                    'payment_status' => 'pending',
                    'drafted_at' => now(),
                    'submitted_at' => now(),
                ]);
                $linkedServiceRequest->forceFill([
                    'transaction_code' => sprintf('SR-EK%05d', (int) $linkedServiceRequest->id),
                ])->save();
            }

            $grandTotal = max(0, $subtotal - $discount) + $shippingCost + $surveyCharge;

            $order = ProductOrder::create([
                'mobile_user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'product_name' => $this->headerName($lines),
                'image' => $lines[0]['product']->primary_image,
                'quantity' => collect($lines)->sum('qty'),
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingCost,
                'discount_amount' => $discount,
                'grand_total' => $grandTotal,
                'voucher_id' => $voucher?->id,
                'courier' => $courier->name,
                'shipping_courier_id' => $courier->id,
                'mobile_user_address_id' => $payload['mobile_user_address_id'] ?? null,
                'service_request_id' => $linkedServiceRequest?->id ?? ($payload['service_request_id'] ?? null),
                'notes' => $payload['notes'] ?? null,
                'address' => $payload['address'] ?? null,
                'customer_name' => $payload['recipient_name'] ?? $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $payload['recipient_phone'] ?? $user->phone,
                'payment_method' => null,
                'payment_status' => 'pending',
                'status' => 'menunggu_pembayaran',
                'status_label' => 'Menunggu Pembayaran',
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'unit_price' => (int) $line['product']->price,
                    'quantity' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                ]);
                // Kurangi stok (reservasi). Dikembalikan bila order dibatalkan (Phase 4).
                $line['product']->decrement('stock', $line['qty']);
            }

            if ($voucher) {
                VoucherRedemption::create([
                    'voucher_id' => $voucher->id,
                    'mobile_user_id' => $user->id,
                    'order_type' => 'product',
                    'order_id' => $order->id,
                    'base_amount' => $subtotal,
                    'discount_amount' => $discount,
                    'status' => 'reserved',
                    'reserved_at' => now(),
                ]);
            }

            return $order->fresh(['items']);
        });
    }

    /** Pilih metode pembayaran untuk order produk: Midtrans (snap) atau transfer manual. */
    public function selectPaymentMethod(MobileUser $user, string $orderNumber, string $paymentMethod): ProductOrder
    {
        $order = $this->findUserOrder($user, $orderNumber);

        if ($order->payment_status === 'paid') {
            throw new \Exception('Pesanan sudah dibayar.', 422);
        }

        $order->update([
            'payment_method' => $paymentMethod,
            'payment_gateway_provider' => $paymentMethod === 'manual_transfer' ? null : 'midtrans',
            'payment_status' => $paymentMethod === 'manual_transfer' ? 'waiting_transfer' : 'pending',
            'payment_method_selected_at' => now(),
        ]);

        $paymentData = null;

        if ($paymentMethod !== 'manual_transfer') {
            $paymentData = $this->mobileMidtransService->createProductOrderSnapTransaction($order->fresh(['items', 'user']), $paymentMethod);

            $order->update([
                'midtrans_order_id' => $paymentData['order_id'],
                'midtrans_snap_token' => $paymentData['token'] ?? null,
                'midtrans_redirect_url' => $paymentData['redirect_url'] ?? null,
                'payment_reference' => $paymentData['order_id'],
                'payment_payload' => $paymentData['raw'] ?? null,
            ]);
        }

        $fresh = $order->fresh(['items', 'user']);
        if ($paymentData) {
            $fresh->setAttribute('payment_data', $paymentData);
        }

        return $fresh;
    }

    /** Upload bukti transfer manual (order produk) → antre verifikasi admin. */
    public function uploadPaymentProof(MobileUser $user, string $orderNumber, UploadedFile $file): ProductOrder
    {
        $order = $this->findUserOrder($user, $orderNumber);

        if ($order->payment_method !== 'manual_transfer') {
            throw new \Exception('Pesanan ini tidak menggunakan pembayaran transfer manual.', 422);
        }
        if ($order->payment_status === 'paid') {
            throw new \Exception('Pesanan sudah dibayar.', 422);
        }

        $fileName = now()->format('Y-m-d') . '-' . \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('mobile/payment-proofs', $fileName, 'public');

        if ($order->payment_proof_path) {
            Storage::disk('public')->delete($order->payment_proof_path);
        }

        $order->update([
            'payment_proof_path' => $path,
            'payment_proof_uploaded_at' => now(),
            'payment_status' => 'waiting_verification',
        ]);

        return $order->fresh(['items']);
    }

    /** Admin menyetujui bukti transfer manual produk: lunas + settle voucher. */
    public function confirmManualPayment(string $orderNumber): ProductOrder
    {
        $order = ProductOrder::where('order_number', $orderNumber)->firstOrFail();

        if ($order->payment_status === 'paid') {
            throw new \Exception('Pembayaran sudah dikonfirmasi.', 422);
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'diproses',
            'status_label' => 'Diproses',
            'paid_at' => now(),
        ]);

        $this->settleVoucher($order, 'paid');
        $this->markLinkedServiceRequestPaid($order);

        return $order->fresh(['items']);
    }

    /** Admin menolak bukti transfer manual produk. */
    public function rejectManualPayment(string $orderNumber): ProductOrder
    {
        $order = ProductOrder::where('order_number', $orderNumber)->firstOrFail();

        $order->update([
            'payment_status' => 'waiting_transfer',
            'payment_proof_path' => null,
            'payment_proof_uploaded_at' => null,
        ]);

        return $order->fresh(['items']);
    }

    /** Handler notifikasi Midtrans untuk order produk (dipanggil dari webhook). */
    public function handleMidtransNotification(array $notification): ?ProductOrder
    {
        // Wajib: verifikasi signature Midtrans sebelum memproses (cegah pemalsuan
        // status bayar oleh pihak tak berwenang). Melempar bila signature tak valid.
        $this->mobileMidtransService->handleNotification($notification);

        $orderId = (string) ($notification['order_id'] ?? '');
        // order_id = "{order_number}-{timestamp}" → ambil order_number (buang suffix timestamp).
        $orderNumber = preg_replace('/-\d{14}$/', '', $orderId);

        $order = ProductOrder::where('order_number', $orderNumber)->first();
        if (! $order) {
            return null;
        }

        $transactionStatus = (string) ($notification['transaction_status'] ?? '');
        $fraudStatus = (string) ($notification['fraud_status'] ?? '');

        $paymentStatus = match ($transactionStatus) {
            'settlement', 'capture' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => $order->payment_status,
        };
        if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
            $paymentStatus = 'challenge';
        }

        $previousPaymentStatus = $order->payment_status;

        DB::transaction(function () use ($order, $paymentStatus, $previousPaymentStatus, $transactionStatus, $notification) {
            $order->update([
                'payment_status' => $paymentStatus,
                'midtrans_transaction_status' => $transactionStatus,
                'midtrans_payment_type' => (string) ($notification['payment_type'] ?? '') ?: null,
                'paid_at' => $paymentStatus === 'paid' ? ($order->paid_at ?? now()) : $order->paid_at,
                'status' => $paymentStatus === 'paid' ? 'diproses' : $order->status,
                'status_label' => $paymentStatus === 'paid' ? 'Diproses' : $order->status_label,
            ]);

            $this->settleVoucher($order, $paymentStatus);

            // Gagal/expire → kembalikan stok yang direservasi saat checkout.
            // Dedup: hanya saat transisi (status bayar sebelumnya belum 'failed').
            if ($paymentStatus === 'failed' && $previousPaymentStatus !== 'failed') {
                $this->restoreStock($order);
            }

            if ($paymentStatus === 'paid') {
                $this->markLinkedServiceRequestPaid($order);
            }
        });

        return $order->fresh(['items']);
    }

    /** Kembalikan stok produk yang direservasi (dipakai saat order gagal/expire). */
    protected function restoreStock(ProductOrder $order): void
    {
        foreach ($order->loadMissing('items')->items as $item) {
            if ($item->product_id) {
                Product::query()->where('id', $item->product_id)->increment('stock', $item->quantity);
            }
        }
    }

    /** Tandai pengajuan survey yang digabung (service_request_id) sebagai lunas saat order dibayar. */
    protected function markLinkedServiceRequestPaid(ProductOrder $order): void
    {
        if (! $order->service_request_id) {
            return;
        }

        MobileServiceRequest::where('id', $order->service_request_id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'status' => 'pending',
                'paid_at' => now(),
                'payment_method' => $order->payment_method,
            ]);
    }

    /** Reserved → used (bayar sukses) atau released (gagal/batal). */
    protected function settleVoucher(ProductOrder $order, string $paymentStatus): void
    {
        $query = VoucherRedemption::query()
            ->where('order_type', 'product')
            ->where('order_id', $order->id)
            ->where('status', 'reserved');

        if ($paymentStatus === 'paid') {
            $query->update(['status' => 'used', 'used_at' => now()]);
        } elseif (in_array($paymentStatus, ['failed', 'cancelled'], true)) {
            $query->update(['status' => 'released', 'released_at' => now()]);
        }
    }

    protected function findUserOrder(MobileUser $user, string $orderNumber): ProductOrder
    {
        $order = ProductOrder::where('mobile_user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            throw new \Exception('Pesanan tidak ditemukan.', 404);
        }

        return $order;
    }

    protected function headerName(array $lines): string
    {
        $first = $lines[0]['product']->name ?? 'Produk';
        $rest = count($lines) - 1;

        return $rest > 0 ? $first . ' (+' . $rest . ' lainnya)' : $first;
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
}
