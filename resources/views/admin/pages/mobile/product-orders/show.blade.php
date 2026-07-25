@extends('admin.layouts.main')

@section('content')
@php
    $rp = fn ($v) => 'Rp' . number_format((int) $v, 0, ',', '.');
    $statusMap = ['selesai'=>'success','dikirim'=>'info','dikemas'=>'primary','diproses'=>'secondary','pending'=>'light','cancelled'=>'danger'];
    $payMap = ['paid'=>'success','pending'=>'warning','failed'=>'danger'];
    $steps = ['pending'=>'Masuk','diproses'=>'Diproses','dikemas'=>'Dikemas','dikirim'=>'Dikirim','selesai'=>'Selesai'];
    $stepKeys = array_keys($steps);
    $currentStep = array_search($order->status, $stepKeys, true);
    $isCancelled = $order->status === 'cancelled';
@endphp

<style>
    .po-timeline { display:flex; align-items:flex-start; gap:0; }
    .po-step { flex:1; text-align:center; position:relative; }
    .po-step__dot { width:26px; height:26px; border-radius:50%; margin:0 auto; display:flex;
        align-items:center; justify-content:center; background:#e9ecef; color:#adb5bd; font-size:13px; z-index:2; position:relative; }
    .po-step.done .po-step__dot { background:#275a56; color:#fff; }
    .po-step__label { font-size:11px; margin-top:6px; color:#8a94a6; }
    .po-step.done .po-step__label { color:#275a56; font-weight:600; }
    .po-step:not(:first-child)::before { content:''; position:absolute; top:13px; left:-50%; width:100%; height:2px; background:#e9ecef; z-index:1; }
    .po-step.done:not(:first-child)::before { background:#275a56; }
    .po-info-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; color:#8a94a6; font-weight:600; }
    .po-info-value { font-size:.9rem; font-weight:600; color:#212529; }
</style>

<div class="row">
    {{-- Header + aksi --}}
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                <div>
                    <h4 class="card-title mb-1">{{ $order->order_number }}</h4>
                    <p class="text-muted mb-0">
                        {{ optional($order->created_at)->format('d M Y, H:i') }} &middot;
                        <span class="badge badge-sm badge-{{ $statusMap[$order->status] ?? 'light' }}">{{ $order->status_label ?: ucfirst($order->status) }}</span>
                        <span class="badge badge-sm badge-{{ $payMap[$order->payment_status] ?? 'light' }}">{{ $order->payment_status === 'paid' ? 'Lunas' : ucfirst($order->payment_status) }}</span>
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-end" style="gap:8px;">
                    <a href="{{ route('admin.mobile.product_orders.invoice', $order->id) }}" target="_blank" class="btn btn-info btn-sm"><i class="ri-printer-line me-1"></i> Cetak Invoice</a>
                    <a href="{{ route('admin.mobile.product_orders.invoice', ['id' => $order->id, 'download' => 1]) }}" class="btn btn-primary btn-sm"><i class="ri-download-2-line me-1"></i> Unduh Invoice</a>
                    <a href="{{ route('admin.mobile.product_orders') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline status --}}
    @unless ($isCancelled)
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="po-timeline">
                    @foreach ($steps as $key => $label)
                        <div class="po-step {{ $currentStep !== false && $loop->index <= $currentStep ? 'done' : '' }}">
                            <div class="po-step__dot"><i class="ri-check-line"></i></div>
                            <div class="po-step__label">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
            </div></div>
        </div>
    @else
        <div class="col-12 mb-3"><div class="alert alert-danger mb-0"><i class="ri-close-circle-line me-1"></i> Pesanan ini <b>dibatalkan</b>. Stok &amp; voucher telah dikembalikan.</div></div>
    @endunless

    {{-- Kiri: item + pembayaran --}}
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm border-0 mb-4"><div class="card-body">
            <h6 class="mb-3">Item Pesanan</h6>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Produk</th><th class="text-center" style="width:90px;">Qty</th><th class="text-end" style="width:130px;">Harga</th><th class="text-end" style="width:140px;">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        @if ($item->image)
                                            <img src="{{ \Illuminate\Support\Str::startsWith($item->image, 'http') ? $item->image : \Illuminate\Support\Facades\Storage::disk('public')->url($item->image) }}"
                                                 style="width:44px;height:44px;object-fit:cover;border-radius:8px;" alt="">
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $item->product_name }}</div>
                                            @if ($item->variant)<small class="text-muted">{{ $item->variant }}</small>@endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $rp($item->unit_price) }}</td>
                                <td class="text-end fw-semibold">{{ $rp($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end text-muted">Subtotal</td><td class="text-end">{{ $rp($order->subtotal) }}</td></tr>
                        @if ($order->discount_amount > 0)
                            <tr><td colspan="3" class="text-end text-success">Diskon Voucher</td><td class="text-end text-success">-{{ $rp($order->discount_amount) }}</td></tr>
                        @endif
                        <tr><td colspan="3" class="text-end text-muted">Ongkir{{ $order->courier ? ' (' . $order->courier . ')' : '' }}</td><td class="text-end">{{ $rp($order->shipping_fee) }}</td></tr>
                        <tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold" style="color:#275a56;">{{ $rp($order->grand_total) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div></div>

        @if ($order->payment_method === 'manual_transfer' && $order->payment_status !== 'paid')
            <div class="card shadow-sm border-0 mb-4"><div class="card-body">
                <h6 class="mb-2"><i class="ri-bank-line me-1"></i> Verifikasi Transfer Manual</h6>
                <div class="alert alert-warning py-2">Cek bukti transfer, lalu ubah <b>Status Pembayaran</b> menjadi <b>Lunas</b> di panel kanan.</div>
                @if ($order->payment_proof_path)
                    @php $proofUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof_path); @endphp
                    <small class="text-muted d-block mb-2">Diunggah: {{ optional($order->payment_proof_uploaded_at)->format('d M Y H:i') ?? '-' }}</small>
                    @if (\Illuminate\Support\Str::endsWith(strtolower($order->payment_proof_path), '.pdf'))
                        <a href="{{ $proofUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="ri-file-pdf-line me-1"></i> Lihat Bukti (PDF)</a>
                    @else
                        <a href="{{ $proofUrl }}" target="_blank"><img src="{{ $proofUrl }}" alt="Bukti transfer" class="img-fluid rounded border" style="max-height:300px;"></a>
                    @endif
                @else
                    <small class="text-muted">User belum mengunggah bukti transfer.</small>
                @endif
            </div></div>
        @endif
    </div>

    {{-- Kanan: pelanggan, pengiriman, update --}}
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm border-0 mb-4"><div class="card-body">
            <h6 class="mb-3">Pelanggan</h6>
            <div class="po-info-label">Nama</div>
            <div class="po-info-value mb-2">{{ $order->customer_name ?: optional($order->user)->name ?: '-' }}</div>
            <div class="po-info-label">Telepon</div>
            <div class="po-info-value mb-2">{{ $order->customer_phone ?: '-' }}</div>
            @if ($order->customer_email)
                <div class="po-info-label">Email</div>
                <div class="po-info-value mb-2">{{ $order->customer_email }}</div>
            @endif
            <div class="po-info-label">Alamat Pengiriman</div>
            <div class="po-info-value" style="font-weight:500; line-height:1.5;">{{ $order->address ?: '-' }}</div>
        </div></div>

        <div class="card shadow-sm border-0 mb-4"><div class="card-body">
            <h6 class="mb-3">Pengiriman</h6>
            <div class="po-info-label">Kurir</div>
            <div class="po-info-value mb-2">{{ $order->courier ?: '-' }}</div>
            <div class="po-info-label">Metode Bayar</div>
            <div class="po-info-value">{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : '-' }}</div>
        </div></div>

        <div class="card shadow-sm border-0"><div class="card-body">
            <h6 class="mb-3">Proses Pesanan</h6>
            <form id="productOrderStatusForm">
                @csrf
                <div class="form-group mb-2">
                    <label class="form-label" style="font-size:.85em">Status Pesanan</label>
                    <select name="status" class="form-control form-control-sm">
                        @foreach (['pending'=>'Menunggu','diproses'=>'Diproses','dikemas'=>'Dikemas','dikirim'=>'Dikirim','selesai'=>'Selesai','cancelled'=>'Dibatalkan'] as $v => $label)
                            <option value="{{ $v }}" @selected($order->status === $v)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label class="form-label" style="font-size:.85em">Status Pembayaran</label>
                    <select name="payment_status" class="form-control form-control-sm">
                        @foreach (['pending'=>'Menunggu','paid'=>'Lunas','failed'=>'Gagal'] as $v => $label)
                            <option value="{{ $v }}" @selected($order->payment_status === $v)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label class="form-label" style="font-size:.85em">No. Resi</label>
                    <input name="tracking_number" type="text" class="form-control form-control-sm" value="{{ $order->tracking_number }}" placeholder="opsional">
                </div>
                <small class="text-muted d-block mb-3" style="font-size:.75rem;">Menandai <b>Lunas</b> mengunci pemakaian voucher &amp; menambah terjual; <b>Dibatalkan</b> mengembalikan stok &amp; voucher.</small>
                <button type="button" id="buttonUpdateProductOrder" class="btn btn-primary btn-sm w-100">Simpan Perubahan</button>
            </form>
        </div></div>
    </div>
</div>

<script>
    $('#buttonUpdateProductOrder').on('click', function () {
        $.post('{{ route('admin.mobile.product_orders.update', $order->id) }}', $('#productOrderStatusForm').serialize() + '&_token={{ csrf_token() }}')
            .done((r) => { $.toast({ heading: 'Sukses!', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 700); })
            .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' }));
    });
</script>
@endsection
