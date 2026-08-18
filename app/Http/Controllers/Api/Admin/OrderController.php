<?php

namespace App\Http\Controllers\Api\Admin;

use App\Services\MobileServiceRequestAdminService;
use App\Services\MobileServiceRequestService;
use App\Services\ProductOrderAdminService;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function __construct(
        protected MobileServiceRequestAdminService $serviceAdmin,
        protected MobileServiceRequestService $serviceRequestService,
        protected ProductOrderAdminService $productAdmin,
    ) {}

    /** List order — type=service|product, + status, payment_status, search. */
    public function index(Request $request)
    {
        try {
            $type = $request->query('type', 'service');
            $filters = [
                'search' => $request->query('q', ''),
                'status' => $request->query('status', ''),
                'payment_status' => $request->query('payment_status', ''),
            ];
            $perPage = (int) $request->query('per_page', 20);
            $from = $request->query('date_from');
            $to = $request->query('date_to');
            $applyDates = function ($query) use ($from, $to) {
                return $query
                    ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                    ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
            };

            if ($type === 'product') {
                $paginator = $applyDates($this->productAdmin->query($filters))->latest()->paginate($perPage);
                $items = collect($paginator->items())->map(fn ($po) => $this->productListItem($po));
            } else {
                $paginator = $applyDates($this->serviceAdmin->query($filters))->latest()->paginate($perPage);
                $items = collect($paginator->items())->map(fn ($sr) => $this->serviceListItem($sr));
            }

            return $this->success([
                'type' => $type,
                'orders' => $items->values(),
                'has_more' => $paginator->hasMorePages(),
                'current_page' => $paginator->currentPage(),
            ], 'Daftar order.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function showService(int $id)
    {
        try {
            $sr = $this->serviceAdmin->findOrFail($id)->loadMissing(['service', 'user', 'products.product', 'handledBy']);

            return $this->success(['order' => $this->serviceDetail($sr)], 'Detail order layanan.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Centang manual step Template Rules Step → action step dieksekusi. */
    public function completeServiceStep(Request $request, int $id)
    {
        try {
            $sr = $this->serviceAdmin->findOrFail($id)->loadMissing(['service', 'user', 'products.product', 'handledBy']);
            app(\App\Services\StepTemplateService::class)
                ->completeStep($sr, (string) $request->input('step_key'), $request->user()?->name ?? 'admin');

            return $this->success(['order' => $this->serviceDetail($sr->refresh())], 'Step ditandai selesai.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Batalkan centang manual step (koreksi). */
    public function reopenServiceStep(Request $request, int $id)
    {
        try {
            $sr = $this->serviceAdmin->findOrFail($id)->loadMissing(['service', 'user', 'products.product', 'handledBy']);
            app(\App\Services\StepTemplateService::class)
                ->reopenStep($sr, (string) $request->input('step_key'));

            return $this->success(['order' => $this->serviceDetail($sr->refresh())], 'Centang step dibatalkan.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function showProduct(int $id)
    {
        try {
            $po = $this->productAdmin->find($id)->loadMissing(['items', 'user']);

            return $this->success(['order' => $this->productDetail($po)], 'Detail order produk.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Perbarui status order layanan (termasuk approve/reject via status). */
    public function updateServiceStatus(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'status' => ['required', 'string', 'max:50'],
                'note' => ['nullable', 'string', 'max:2000'],
                'rejection_reason' => ['nullable', 'string', 'max:2000'],
            ]);

            $sr = $this->serviceAdmin->updateStatus(
                $id,
                $request->user(),
                $validated['status'],
                $validated['note'] ?? null,
                $validated['rejection_reason'] ?? null,
            );

            return $this->success(['order' => $this->serviceDetail($sr->loadMissing(['service', 'user', 'products.product', 'handledBy']))], 'Status diperbarui.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Verifikasi bukti bayar order layanan (approve = konfirmasi manual, reject = tolak bukti). */
    public function verifyServicePayment(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'decision' => ['required', 'in:approve,reject'],
                'reason' => ['nullable', 'string', 'max:2000'],
            ]);

            if ($validated['decision'] === 'approve') {
                $sr = $this->serviceRequestService->confirmManualPayment($id);
            } else {
                $sr = $this->serviceAdmin->findOrFail($id);
                $sr->update([
                    'payment_status' => 'unpaid',
                    'rejection_reason' => $validated['reason'] ?? $sr->rejection_reason,
                ]);
                $sr = $sr->fresh(['service', 'user', 'products.product', 'handledBy']);
            }

            return $this->success(['order' => $this->serviceDetail($sr)], 'Verifikasi pembayaran diproses.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Perbarui status / pengiriman / pembayaran order produk. */
    public function updateProductStatus(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'status' => ['nullable', 'string', 'max:50'],
                'tracking_number' => ['nullable', 'string', 'max:100'],
                'mark_paid' => ['nullable', 'boolean'],
            ]);

            $payload = array_filter([
                'status' => $validated['status'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
            ], fn ($v) => $v !== null);

            if ($request->boolean('mark_paid')) {
                $payload['payment_status'] = 'paid';
            }

            $po = $this->productAdmin->updateStatus($id, $payload);

            return $this->success(['order' => $this->productDetail($po->loadMissing(['items', 'user']))], 'Order produk diperbarui.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    // ---------- payloads ----------

    protected function serviceListItem($sr): array
    {
        return [
            'id' => $sr->id,
            'code' => $sr->transaction_code_label ?? ('SR-' . $sr->id),
            'title' => optional($sr->service)->title ?? 'Layanan',
            'customer' => optional($sr->user)->name ?? '-',
            'amount' => (int) $sr->total_amount,
            'status' => $sr->status,
            'payment_status' => $sr->payment_status,
            'created_at' => optional($sr->created_at)?->toISOString(),
        ];
    }

    protected function serviceDetail($sr): array
    {
        return array_merge($this->serviceListItem($sr), [
            'customer_id' => $sr->mobile_user_id,
            'customer_phone' => optional($sr->user)->phone,
            'customer_email' => optional($sr->user)->email,
            'building_label' => $sr->building_label,
            'description' => $sr->description,
            'survey_date' => optional($sr->survey_date)?->format('Y-m-d'),
            'survey_address' => $sr->survey_address,
            'survey_address_detail' => $sr->survey_address_detail,
            'survey_region' => $sr->survey_region ?? [],
            'survey_fee' => (int) $sr->survey_fee,
            'tax_amount' => (int) $sr->tax_amount,
            'discount_amount' => (int) $sr->discount_amount,
            'products_amount' => (int) $sr->products_amount,
            'payment_method' => $sr->payment_method,
            'payment_proof_url' => storageUrl($sr->payment_proof_path),
            'payment_proof_uploaded_at' => optional($sr->payment_proof_uploaded_at)?->toISOString(),
            'submitted_at' => optional($sr->submitted_at)?->toISOString(),
            'approved_at' => optional($sr->approved_at)?->toISOString(),
            'paid_at' => optional($sr->paid_at)?->toISOString(),
            'rejection_reason' => $sr->rejection_reason,
            'handled_by' => optional($sr->handledBy)->name,
            // Timeline "Status Pengajuan" dari Template Rules Step (state dihitung server).
            'steps' => app(\App\Services\StepTemplateService::class)->timelineFor($sr),
            'products' => $sr->products->map(fn ($p) => [
                'name' => $p->product_name,
                'quantity' => (int) $p->quantity,
                'unit_price' => (int) $p->unit_price,
                'subtotal' => (int) $p->subtotal,
            ])->values(),
        ]);
    }

    protected function productListItem($po): array
    {
        return [
            'id' => $po->id,
            'code' => $po->order_number,
            'title' => $po->product_name ?? 'Order Produk',
            'customer' => $po->customer_name ?? optional($po->user)->name ?? '-',
            'amount' => (int) $po->grand_total,
            'status' => $po->status,
            'status_label' => $po->status_label,
            'payment_status' => $po->payment_status,
            'created_at' => optional($po->created_at)?->toISOString(),
        ];
    }

    protected function productDetail($po): array
    {
        return array_merge($this->productListItem($po), [
            'customer_id' => $po->mobile_user_id,
            'service_request_id' => $po->service_request_id,
            'customer_phone' => $po->customer_phone,
            'customer_email' => $po->customer_email,
            'address' => $po->address,
            'address_detail' => $po->address_detail,
            'courier' => $po->courier,
            'tracking_number' => $po->tracking_number,
            'shipping_fee' => (int) $po->shipping_fee,
            'discount_amount' => (int) $po->discount_amount,
            'grand_total' => (int) $po->grand_total,
            'payment_method' => $po->payment_method,
            'payment_proof_url' => storageUrl($po->payment_proof_path),
            'paid_at' => optional($po->paid_at)?->toISOString(),
            'items' => $po->items->map(fn ($it) => [
                'name' => $it->product_name ?? optional($it->product)->name,
                'quantity' => (int) $it->quantity,
                'unit_price' => (int) $it->unit_price,
                'subtotal' => (int) $it->subtotal,
                'image' => storageUrl(optional($it->product)->primary_image ?? $it->image ?? null),
            ])->values(),
        ]);
    }
}
