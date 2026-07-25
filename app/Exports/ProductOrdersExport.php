<?php

namespace App\Exports;

use App\Services\ProductOrderAdminService;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductOrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ProductOrderAdminService $service,
        protected array $filters = []
    ) {}

    public function query(): Builder
    {
        return $this->service->query($this->filters);
    }

    public function headings(): array
    {
        return ['No. Order', 'Tanggal', 'Pelanggan', 'Kontak', 'Produk', 'Jumlah Item', 'Total', 'Status', 'Pembayaran', 'No. Resi'];
    }

    /** @param \App\Models\ProductOrder $order */
    public function map($order): array
    {
        return [
            $order->order_number,
            optional($order->created_at)->format('Y-m-d H:i'),
            $order->customer_name ?: optional($order->user)->name ?: '-',
            $order->customer_phone ?: $order->customer_email ?: '-',
            $order->product_name ?: '-',
            (int) $order->items_count,
            (int) $order->grand_total,
            $order->status_label ?: ucfirst($order->status),
            $order->payment_status === 'paid' ? 'Lunas' : ucfirst($order->payment_status),
            $order->tracking_number ?: '-',
        ];
    }
}
