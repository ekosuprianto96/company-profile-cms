@php $rp = fn ($v) => 'Rp' . number_format((int) $v, 0, ',', '.'); @endphp

<div class="mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:8px">
        <div>
            <div class="fw-bold text-uppercase">{{ $order->order_number }}</div>
            <small class="text-muted">{{ optional($order->created_at)->format('d M Y, H:i') }}</small>
        </div>
        <span class="badge badge-light">{{ $order->status_label ?: ucfirst($order->status) }}</span>
    </div>
</div>

<div class="card border-0 bg-light mb-3"><div class="card-body py-2">
    <div class="row">
        <div class="col-md-6"><small class="text-muted d-block">Penerima</small><span class="fw-semibold">{{ $order->customer_name ?: optional($order->user)->name ?: '-' }}</span><br><small>{{ $order->customer_phone ?: '-' }}</small></div>
        <div class="col-md-6"><small class="text-muted d-block">Kurir</small><span class="fw-semibold">{{ $order->courier ?: '-' }}</span></div>
    </div>
    <div class="mt-2"><small class="text-muted d-block">Alamat</small><span>{{ $order->address ?: '-' }}</span></div>
</div></div>

<table class="table table-sm">
    <thead><tr><th>Produk</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}<br><small class="text-muted">{{ $rp($item->unit_price) }}</small></td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-end">{{ $rp($item->subtotal) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr><td colspan="2" class="text-end text-muted">Subtotal</td><td class="text-end">{{ $rp($order->subtotal) }}</td></tr>
        @if ($order->discount_amount > 0)
            <tr><td colspan="2" class="text-end text-success">Diskon Voucher</td><td class="text-end text-success">-{{ $rp($order->discount_amount) }}</td></tr>
        @endif
        <tr><td colspan="2" class="text-end text-muted">Ongkir</td><td class="text-end">{{ $rp($order->shipping_fee) }}</td></tr>
        <tr><td colspan="2" class="text-end fw-bold">Total</td><td class="text-end fw-bold">{{ $rp($order->grand_total) }}</td></tr>
    </tfoot>
</table>

@if ($order->payment_method === 'manual_transfer' && $order->payment_status !== 'paid')
    <hr>
    <div class="alert alert-warning py-2 mb-2"><i class="ri-bank-line me-1"></i> <b>Transfer Manual</b> — verifikasi bukti lalu ubah Status Pembayaran menjadi <b>Lunas (paid)</b>.</div>
    @if ($order->payment_proof_path)
        @php $proofUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof_path); @endphp
        <small class="text-muted d-block mb-1">Bukti diupload: {{ optional($order->payment_proof_uploaded_at)->format('d M Y H:i') ?? '-' }}</small>
        @if (\Illuminate\Support\Str::endsWith(strtolower($order->payment_proof_path), '.pdf'))
            <a href="{{ $proofUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm mb-2"><i class="ri-file-pdf-line me-1"></i> Lihat Bukti (PDF)</a>
        @else
            <a href="{{ $proofUrl }}" target="_blank"><img src="{{ $proofUrl }}" alt="Bukti transfer" class="img-fluid rounded border mb-2" style="max-height: 280px;"></a>
        @endif
    @else
        <small class="text-muted d-block">User belum mengunggah bukti transfer.</small>
    @endif
@endif

<hr>

<form id="productOrderStatusForm" class="forms-sample">
    @csrf
    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Status Pesanan</label>
            <select name="status" class="form-control">
                @foreach (['diproses'=>'Diproses','dikemas'=>'Dikemas','dikirim'=>'Dikirim','selesai'=>'Selesai','cancelled'=>'Dibatalkan'] as $v => $label)
                    <option value="{{ $v }}" @selected($order->status === $v)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Status Pembayaran</label>
            <select name="payment_status" class="form-control">
                @foreach (['pending'=>'Menunggu','paid'=>'Lunas','failed'=>'Gagal'] as $v => $label)
                    <option value="{{ $v }}" @selected($order->payment_status === $v)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">No. Resi</label>
            <input name="tracking_number" type="text" class="form-control" value="{{ $order->tracking_number }}" placeholder="opsional">
        </div>
    </div>
    <small class="text-muted d-block mb-2">Menandai <b>Lunas</b> akan mengunci pemakaian voucher; <b>Dibatalkan</b> mengembalikan stok &amp; voucher.</small>
</form>

<div class="d-flex justify-content-end">
    <button type="button" id="buttonUpdateProductOrder" class="btn btn-primary">Simpan Status</button>
</div>

<script>
    $(document).ready(function() {
        $('#buttonUpdateProductOrder').click(function() {
            $.post('{{ route('admin.mobile.product_orders.update', $order->id) }}', $('#productOrderStatusForm').serialize() + '&_token={{ csrf_token() }}')
                .done(function(r) {
                    $('#modalProductOrderDetail').modal('hide');
                    window.$productOrdersTable.ajax.reload();
                    $.toast({ heading: 'Sukses!', text: r.message, showHideTransition: 'slide', position: 'top-right', icon: 'success' });
                })
                .fail(function(e) {
                    const r = e.responseJSON || {};
                    $.toast({ heading: 'Warning', text: r.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' });
                });
        });
    });
</script>
