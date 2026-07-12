<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; margin: 0; }
        .muted { color: #6b7280; }
        .brand { color: #275a56; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .band { background: #275a56; color: #fff; padding: 26px 40px; }
        .band td { vertical-align: middle; color: #fff; }
        .logo { width: 44px; height: 44px; background: rgba(255,255,255,.18); color: #fff; font-size: 20px;
                font-weight: bold; text-align: center; border-radius: 10px; }
        .company-name { font-size: 16px; font-weight: bold; }
        .inv-title { font-size: 22px; font-weight: bold; letter-spacing: 3px; opacity: .85; }
        .pill { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 10px; font-weight: bold;
                text-transform: uppercase; }
        .pill-paid { background: #d1fae5; color: #047857; }
        .pill-unpaid { background: #fde68a; color: #92400e; }
        .metabar { background: #f3f4f6; padding: 12px 40px; }
        .metabar td { font-size: 11px; }
        .wrap { padding: 26px 40px; }
        .section-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
                         color: #9ca3af; margin-bottom: 6px; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 13px 15px; }
        .items th { background: #275a56; color: #fff; text-align: left; padding: 10px 12px; font-size: 10px;
                    text-transform: uppercase; letter-spacing: .5px; }
        .items th:last-child { text-align: right; }
        .items td { padding: 11px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .total-box { background: #eef5f4; border-radius: 10px; padding: 10px 16px; }
        .total-box .grand { font-size: 17px; font-weight: bold; color: #275a56; }
        .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 10px; line-height: 1.5; }
    </style>
</head>
<body>
{{-- Header band --}}
<table class="band">
    <tr>
        <td style="width: 60%;">
            <table>
                <tr>
                    <td style="width: 52px;">
                        @if ($company['logo'])
                            <img src="{{ $company['logo'] }}" alt="logo" style="width:44px;height:44px;object-fit:contain;">
                        @else
                            <div class="logo">{{ mb_substr($company['name'], 0, 1) }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="company-name">{{ $company['name'] }}</div>
                        <div style="font-size:11px; opacity:.85;">{{ $company['tagline'] }}</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="right" style="width: 40%;">
            <div class="inv-title">INVOICE</div>
            <div style="margin-top:6px;">
                <span class="pill {{ $invoice['paid'] ? 'pill-paid' : 'pill-unpaid' }}">{{ $invoice['status_label'] }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- Meta bar --}}
<table class="metabar">
    <tr>
        <td><span class="muted">No. Invoice</span><br><strong>{{ $invoice['number'] }}</strong></td>
        <td class="right"><span class="muted">Tanggal Terbit</span><br><strong>{{ $invoice['date'] }}</strong></td>
    </tr>
</table>

<div class="wrap">
    {{-- Billed to + context --}}
    <table>
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <div class="section-label">Ditagihkan Kepada</div>
                <div class="card">
                    <div style="font-weight:bold; font-size:13px;">{{ $invoice['customer']['name'] }}</div>
                    <div class="muted">{{ $invoice['customer']['email'] }}</div>
                    <div class="muted">{{ $invoice['customer']['phone'] }}</div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-label">{{ $invoice['type'] === 'product' ? 'Pengiriman' : 'Survei' }}</div>
                <div class="card">
                    @foreach ($invoice['context_rows'] as $row)
                        <div style="margin-bottom:3px;"><span class="muted">{{ $row['label'] }}:</span> {{ $row['value'] }}</div>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table class="items" style="margin-top: 20px; border-radius: 10px; overflow: hidden;">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th style="width: 30%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="font-weight:bold;">{{ $invoice['item']['title'] }}</div>
                    <div class="muted" style="font-size:11px;">{{ $invoice['item']['subtitle'] }}</div>
                </td>
                <td class="right">{{ $invoice['item']['amount'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Totals --}}
    <table style="margin-top: 14px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <table style="margin-bottom: 8px;">
                    @foreach ($invoice['summary'] as $line)
                        <tr>
                            <td class="muted" style="padding:3px 0;">{{ $line['label'] }}</td>
                            <td class="right" style="padding:3px 0;">{{ $line['value'] }}</td>
                        </tr>
                    @endforeach
                </table>
                <table class="total-box">
                    <tr>
                        <td style="font-weight:bold;">Total Tagihan</td>
                        <td class="right grand">{{ $invoice['total'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Payment --}}
    <div style="margin-top: 22px;">
        <div class="section-label">Pembayaran</div>
        <div class="card">
            <table>
                @foreach ($invoice['payment'] as $line)
                    <tr>
                        <td class="muted" style="padding:3px 0; width: 40%;">{{ $line['label'] }}</td>
                        <td style="padding:3px 0; font-weight:bold;">{{ $line['value'] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="footer">
        Terima kasih telah mempercayakan pekerjaan Anda kepada {{ $company['name'] }}.<br>
        Invoice ini sah &amp; diterbitkan otomatis oleh sistem.
    </div>
</div>
</body>
</html>
