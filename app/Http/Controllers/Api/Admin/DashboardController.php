<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\MobileServiceRequest;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends ApiController
{
    /** Ringkasan untuk layar Dashboard. */
    public function index(Request $request)
    {
        try {
            $today = Carbon::today();

            $srToday = MobileServiceRequest::whereDate('created_at', $today)->count();
            $poToday = ProductOrder::whereDate('created_at', $today)->count();

            $revenueToday = (int) MobileServiceRequest::where('payment_status', 'paid')->whereDate('created_at', $today)->sum('total_amount')
                + (int) ProductOrder::where('payment_status', 'paid')->whereDate('created_at', $today)->sum('grand_total');

            $pendingVerify = MobileServiceRequest::whereNotNull('payment_proof_path')->where('payment_status', '!=', 'paid')->count()
                + ProductOrder::whereNotNull('payment_proof_path')->where('payment_status', '!=', 'paid')->count();

            $unreadChat = DB::table('chat_conversations')
                ->whereNotNull('last_message_at')
                ->where(function ($q) {
                    $q->whereNull('admin_last_read_at')->orWhereColumn('last_message_at', '>', 'admin_last_read_at');
                })->count();

            $recentService = MobileServiceRequest::with('service:id,title', 'user:id,name')->latest()->limit(5)->get()
                ->map(fn ($sr) => [
                    'type' => 'service',
                    'id' => $sr->id,
                    'code' => $sr->transaction_code_label ?? ('SR-' . $sr->id),
                    'title' => $sr->service->title ?? 'Layanan',
                    'customer' => optional($sr->user)->name ?? '-',
                    'amount' => (int) $sr->total_amount,
                    'status' => $sr->status,
                    'created_at' => optional($sr->created_at)?->toISOString(),
                ]);

            $recentProduct = ProductOrder::latest()->limit(5)->get()
                ->map(fn ($po) => [
                    'type' => 'product',
                    'id' => $po->id,
                    'code' => $po->order_number,
                    'title' => $po->product_name ?? 'Order Produk',
                    'customer' => $po->customer_name ?? '-',
                    'amount' => (int) $po->grand_total,
                    'status' => $po->status,
                    'created_at' => optional($po->created_at)?->toISOString(),
                ]);

            $recent = $recentService->concat($recentProduct)
                ->sortByDesc('created_at')->take(6)->values();

            return $this->success([
                'orders_today' => $srToday + $poToday,
                'revenue_today' => $revenueToday,
                'pending_verification' => $pendingVerify,
                'unread_chat' => $unreadChat,
                'recent_orders' => $recent,
            ], 'Ringkasan dashboard.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Analitik penjualan & keuangan. range = today|week|month (default month). */
    public function analytics(Request $request)
    {
        try {
            $range = $request->query('range', 'month');
            [$from, $to] = $this->rangeBounds($range);

            $srPaid = MobileServiceRequest::where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]);
            $poPaid = ProductOrder::where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]);

            $serviceRevenue = (int) (clone $srPaid)->sum('total_amount');
            $productRevenue = (int) (clone $poPaid)->sum('grand_total');
            $totalRevenue = $serviceRevenue + $productRevenue;

            $completed = MobileServiceRequest::where('status', 'completed')->whereBetween('created_at', [$from, $to])->count()
                + ProductOrder::whereIn('status', ['selesai', 'completed'])->whereBetween('created_at', [$from, $to])->count();

            $paidCount = (clone $srPaid)->count() + (clone $poPaid)->count();
            $avgOrder = $paidCount > 0 ? (int) round($totalRevenue / $paidCount) : 0;

            $voucherDiscount = (int) (clone $srPaid)->sum('discount_amount') + (int) (clone $poPaid)->sum('discount_amount');

            $pendingAmount = (int) MobileServiceRequest::where('payment_status', '!=', 'paid')->whereNotNull('payment_proof_path')->sum('total_amount')
                + (int) ProductOrder::where('payment_status', '!=', 'paid')->whereNotNull('payment_proof_path')->sum('grand_total');

            // Tren 7 hari terakhir (pendapatan paid per hari).
            $trend = collect(range(6, 0))->map(function ($daysAgo) {
                $day = Carbon::today()->subDays($daysAgo);
                $rev = (int) MobileServiceRequest::where('payment_status', 'paid')->whereDate('created_at', $day)->sum('total_amount')
                    + (int) ProductOrder::where('payment_status', 'paid')->whereDate('created_at', $day)->sum('grand_total');

                return ['label' => $day->isoFormat('dd'), 'date' => $day->toDateString(), 'revenue' => $rev];
            })->values();

            $topServices = MobileServiceRequest::select('mobile_service_id', DB::raw('count(*) as total'))
                ->with('service:id,title')->groupBy('mobile_service_id')->orderByDesc('total')->limit(5)->get()
                ->map(fn ($r) => ['name' => optional($r->service)->title ?? 'Layanan', 'total' => (int) $r->total]);

            $topProducts = ProductOrder::select('product_name', DB::raw('count(*) as total'))
                ->groupBy('product_name')->orderByDesc('total')->limit(5)->get()
                ->map(fn ($r) => ['name' => $r->product_name ?? 'Produk', 'total' => (int) $r->total]);

            return $this->success([
                'range' => $range,
                'total_revenue' => $totalRevenue,
                'service_revenue' => $serviceRevenue,
                'product_revenue' => $productRevenue,
                'completed_orders' => $completed,
                'avg_order_value' => $avgOrder,
                'voucher_discount' => $voucherDiscount,
                'pending_amount' => $pendingAmount,
                'net_revenue' => $totalRevenue - $voucherDiscount,
                'trend' => $trend,
                'top_services' => $topServices,
                'top_products' => $topProducts,
            ], 'Analitik penjualan & keuangan.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    protected function rangeBounds(string $range): array
    {
        return match ($range) {
            'today' => [Carbon::today(), Carbon::now()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()],
        };
    }
}
