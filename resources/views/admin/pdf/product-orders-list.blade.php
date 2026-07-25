<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Order Produk</title>
    <style>
        @page { margin: 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1f2933; margin: 0; }
        .head { border-bottom: 2px solid #275a56; padding-bottom: 6px; margin-bottom: 10px; }
        .head h1 { font-size: 14px; color: #275a56; margin: 0; }
        .head .meta { font-size: 9px; color: #6b7280; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eef5f4; color: #14322f; text-align: left; padding: 5px 6px; border: 1px solid #cddedb; font-size: 8.5px; text-transform: uppercase; letter-spacing: .3px; }
        td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .muted { color: #6b7280; }
        tr:nth-child(even) td { background: #fbfcfc; }
    </style>
</head>
<body>
    <div class="head">
        <h1>Daftar Order Produk</h1>
        <div class="meta">
            {{ config('settings.value.app_name') ?: 'Maninjau' }} &middot;
            Dicetak {{ $generatedAt->format('d M Y H:i') }} &middot;
            {{ $records->count() }} order
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:12%">No. Order</th>
                <th style="width:9%">Tanggal</th>
                <th style="width:16%">Pelanggan</th>
                <th>Produk</th>
                <th style="width:6%" class="num">Item</th>
                <th style="width:11%" class="num">Total</th>
                <th style="width:9%">Status</th>
                <th style="width:9%">Pembayaran</th>
                <th style="width:11%">No. Resi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ optional($order->created_at)->format('d/m/Y') }}</td>
                    <td>
                        {{ $order->customer_name ?: optional($order->user)->name ?: '-' }}
                        <div class="muted">{{ $order->customer_phone ?: $order->customer_email ?: '' }}</div>
                    </td>
                    <td>{{ $order->product_name ?: '-' }}</td>
                    <td class="num">{{ (int) $order->items_count }}</td>
                    <td class="num">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                    <td>{{ $order->status_label ?: ucfirst($order->status) }}</td>
                    <td>{{ $order->payment_status === 'paid' ? 'Lunas' : ucfirst($order->payment_status) }}</td>
                    <td>{{ $order->tracking_number ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center; padding:14px;" class="muted">Tidak ada order.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
