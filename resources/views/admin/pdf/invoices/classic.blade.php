<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; margin: 0; }
        .wrap { padding: 36px 40px; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #6b7280; }
        .brand { color: #275a56; }
        .right { text-align: right; }
        .head td { vertical-align: top; }
        .logo { width: 46px; height: 46px; background: #275a56; color: #fff; font-size: 22px; font-weight: bold;
                text-align: center; border-radius: 8px; }
        .company-name { font-size: 15px; font-weight: bold; color: #111827; }
        .inv-title { font-size: 26px; font-weight: bold; letter-spacing: 3px; color: #cbd5e1; }
        .pill { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 10px; font-weight: bold;
                text-transform: uppercase; }
        .pill-paid { background: #d1fae5; color: #047857; }
        .pill-unpaid { background: #fef3c7; color: #b45309; }
        .rule { border: none; border-top: 2px solid #275a56; margin: 18px 0; }
        .section-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
                         color: #9ca3af; margin-bottom: 6px; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
        .items th { background: #f3f4f6; text-align: left; padding: 9px 12px; font-size: 10px; text-transform: uppercase;
                    letter-spacing: .5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .items td { padding: 11px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .total-row td { padding: 6px 0; }
        .total-final td { border-top: 2px solid #e5e7eb; padding-top: 10px; font-size: 15px; font-weight: bold; }
        .footer { margin-top: 34px; text-align: center; color: #9ca3af; font-size: 10px; line-height: 1.5; }
    </style>
</head>
<body>
<div class="wrap">
    {{-- Kop --}}
    <table class="head">
        <tr>
            <td style="width: 60%;">
                <table>
                    <tr>
                        <td style="width: 54px;">
                            @if ($company['logo'])
                                <img src="{{ $company['logo'] }}" alt="logo" style="width:46px;height:46px;object-fit:contain;">
                            @else
                                <div class="logo">{{ mb_substr($company['name'], 0, 1) }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="company-name">{{ $company['name'] }}</div>
                            <div class="muted" style="font-size:11px;">{{ $company['tagline'] }}</div>
                            @if ($company['address'])<div class="muted" style="font-size:10px;">{{ $company['address'] }}</div>@endif
                            @if ($company['phone'] || $company['email'])
                                <div class="muted" style="font-size:10px;">{{ collect([$company['phone'], $company['email']])->filter()->implode(' · ') }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td class="right" style="width: 40%;">
                <div class="inv-title">INVOICE</div>
                <div style="margin-top:6px;"><span class="muted">No. </span><strong>{{ $invoice['number'] }}</strong></div>
                <div><span class="muted">Tanggal </span><strong>{{ $invoice['date'] }}</strong></div>
                <div style="margin-top:6px;">
                    <span class="pill {{ $invoice['paid'] ? 'pill-paid' : 'pill-unpaid' }}">{{ $invoice['status_label'] }}</span>
                </div>
            </td>
        </tr>
    </table>

    <hr class="rule">

    {{-- Billed to + context --}}
    <table>
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 12px;">
                <div class="section-label">Ditagihkan Kepada</div>
                <div style="font-weight:bold; font-size:13px;">{{ $invoice['customer']['name'] }}</div>
                <div class="muted">{{ $invoice['customer']['email'] }}</div>
                <div class="muted">{{ $invoice['customer']['phone'] }}</div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-label">{{ $invoice['type'] === 'product' ? 'Pengiriman' : 'Survei' }}</div>
                @foreach ($invoice['context_rows'] as $row)
                    <div style="margin-bottom:3px;"><span class="muted">{{ $row['label'] }}:</span> {{ $row['value'] }}</div>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table class="items" style="margin-top: 22px;">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th class="right" style="width: 30%;">Jumlah</th>
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
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                <table>
                    @foreach ($invoice['summary'] as $line)
                        <tr class="total-row">
                            <td class="muted">{{ $line['label'] }}</td>
                            <td class="right">{{ $line['value'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-final">
                        <td>Total Tagihan</td>
                        <td class="right brand">{{ $invoice['total'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Payment --}}
    <div style="margin-top: 24px;">
        <div class="section-label">Pembayaran</div>
        <div class="box">
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
