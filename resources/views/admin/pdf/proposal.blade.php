<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Proposal {{ $docNumber }}</title>
    <style>
        @page { margin: 28mm 18mm 24mm 18mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1f2933; line-height: 1.6; margin: 0; }

        /* Kop surat */
        .letterhead { width: 100%; border-bottom: 2.5px solid #275a56; padding-bottom: 8px; margin-bottom: 4px; }
        .letterhead td { vertical-align: middle; }
        .company-name { font-size: 17px; font-weight: bold; color: #275a56; letter-spacing: .3px; }
        .company-tagline { font-size: 9.5px; color: #6b7280; }
        .doc-meta { text-align: right; font-size: 9.5px; color: #4b5563; }

        /* Judul dokumen */
        .doc-title { text-align: center; margin: 20px 0 4px; }
        .doc-title h1 { font-size: 15px; letter-spacing: 1.2px; margin: 0; text-transform: uppercase; color: #14322f; }
        .doc-title .sub { font-size: 10px; color: #6b7280; margin-top: 2px; }

        /* Blok tujuan */
        .addressee { margin: 16px 0 4px; }
        .addressee .to { font-weight: bold; }

        /* Bagian */
        h2.section { font-size: 11.5px; text-transform: uppercase; letter-spacing: .6px; color: #275a56;
                     border-bottom: 1px solid #d7e7e4; padding-bottom: 3px; margin: 18px 0 8px; }
        p { margin: 0 0 8px; text-align: justify; }

        /* Tabel */
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.data th { background: #eef5f4; color: #14322f; font-size: 9.5px; text-transform: uppercase;
                        letter-spacing: .4px; padding: 6px 8px; border: 1px solid #cddedb; text-align: left; }
        table.data td { padding: 6px 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        table.data td.label { width: 38%; color: #4b5563; }
        table.data td.value { font-weight: 600; color: #14322f; }
        tr.group td { background: #f7faf9; font-weight: bold; color: #275a56; font-size: 9.5px;
                      text-transform: uppercase; letter-spacing: .4px; }
        .num { text-align: right; white-space: nowrap; }
        tr.total td { background: #275a56; color: #fff; font-weight: bold; font-size: 11px; }
        .words { font-size: 9.5px; font-style: italic; color: #4b5563; margin-top: 2px; }

        ol.terms { margin: 0 0 0 14px; padding: 0; }
        ol.terms li { margin-bottom: 4px; text-align: justify; }

        /* Tanda tangan */
        .sign { width: 100%; margin-top: 26px; }
        .closing { page-break-inside: avoid; }
        .sign td { width: 50%; vertical-align: top; font-size: 10px; }
        .sign .space { height: 58px; }
        .sign .name { font-weight: bold; border-top: 1px solid #9ca3af; padding-top: 3px; display: inline-block; min-width: 165px; }
        .footnote { margin-top: 22px; font-size: 8.5px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>

<table class="letterhead">
    <tr>
        @if (!empty($company['logo']))
            <td style="width:56px;"><img src="{{ $company['logo'] }}" alt="" style="height:44px;"></td>
        @endif
        <td>
            <div class="company-name">{{ $company['name'] }}</div>
            @if ($company['tagline'])<div class="company-tagline">{{ $company['tagline'] }}</div>@endif
        </td>
        <td class="doc-meta">
            <strong>{{ $docNumber }}</strong><br>
            {{ $issuedAt->translatedFormat('d F Y') }}
        </td>
    </tr>
</table>

<div class="doc-title">
    <h1>Proposal Penawaran Layanan</h1>
    <div class="sub">{{ $serviceTitle }}</div>
</div>

<div class="addressee">
    <div>Kepada Yth.</div>
    <div class="to">{{ $client['name'] ?: '-' }}</div>
    <div>{{ $client['phone'] }}{{ $client['email'] ? ' · ' . $client['email'] : '' }}</div>
    <div>di Tempat</div>
</div>

<h2 class="section">I. Pendahuluan</h2>
<p>
    Terima kasih atas kepercayaan Anda kepada <strong>{{ $company['name'] }}</strong>. Menindaklanjuti pengajuan
    Anda untuk layanan <strong>{{ $serviceTitle }}</strong> yang kami terima pada
    {{ $issuedAt->translatedFormat('d F Y') }}, bersama ini kami sampaikan proposal penawaran yang memuat
    rincian kebutuhan serta biaya yang berlaku.
</p>
<p>
    Dokumen ini disusun berdasarkan data yang Anda isikan pada formulir pengajuan. Apabila terdapat
    ketidaksesuaian, mohon informasikan kepada kami agar dapat segera kami sesuaikan.
</p>

<h2 class="section">II. Ruang Lingkup &amp; Rincian Kebutuhan</h2>
@if (count($answers) === 0)
    <p><em>Tidak ada rincian kebutuhan yang tercatat.</em></p>
@else
    <table class="data">
        @foreach ($answers as $row)
            @if ($row['type'] === 'section')
                <tr class="group"><td colspan="2">{{ $row['label'] }}</td></tr>
            @else
                <tr>
                    <td class="label">{{ $row['label'] }}</td>
                    <td class="value">
                        @if (!empty($row['files']))
                            @foreach ($row['files'] as $file)
                                {{ $loop->iteration }}. {{ $file['name'] }}@if(!$loop->last)<br>@endif
                            @endforeach
                        @else
                            {{ $row['value'] !== '' ? $row['value'] : '-' }}
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
@endif

<h2 class="section">III. Rincian Biaya</h2>
@if ($priceItems->isEmpty())
    <p>Tidak ada biaya yang dikenakan di tahap ini. Penawaran biaya pekerjaan akan disampaikan setelah peninjauan kebutuhan.</p>
@else
    <table class="data">
        <thead>
            <tr>
                <th style="width:42px;">No</th>
                <th>Komponen Biaya</th>
                <th style="width:90px;">Sifat</th>
                <th style="width:120px;" class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($priceItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td>{{ !empty($item['is_required']) ? 'Wajib' : 'Opsional' }}</td>
                    <td class="num">Rp {{ number_format((int) $item['amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3">TOTAL (biaya wajib)</td>
                <td class="num">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <div class="words">Terbilang: {{ ucfirst(trim($totalWords)) }}.</div>
@endif

<h2 class="section">IV. Syarat &amp; Ketentuan</h2>
<ol class="terms">
    <li>Penawaran ini berlaku sampai dengan <strong>{{ $validUntil->translatedFormat('d F Y') }}</strong>.</li>
    <li>Biaya pada bagian III merupakan biaya tahap awal sesuai skema layanan yang dipilih dan belum termasuk biaya pelaksanaan pekerjaan, kecuali dinyatakan lain.</li>
    <li>Pelaksanaan pekerjaan dimulai setelah pembayaran tahap awal diterima dan lingkup pekerjaan disepakati kedua belah pihak.</li>
    <li>Perubahan lingkup pekerjaan setelah kesepakatan dapat memengaruhi biaya dan jadwal penyelesaian.</li>
    <li>Dokumen dan data yang Anda sampaikan diperlakukan sebagai informasi rahasia dan hanya digunakan untuk keperluan pekerjaan ini.</li>
</ol>

<div class="closing">
<h2 class="section">V. Penutup</h2>
<p>
    Demikian proposal ini kami sampaikan. Kami siap mendiskusikan setiap bagian dari penawaran ini lebih lanjut
    sesuai kebutuhan Anda. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
</p>

<table class="sign">
    <tr>
        <td>&nbsp;</td>
        <td>
            {{ $issuedAt->translatedFormat('d F Y') }}<br>
            Hormat kami,<br>
            <strong>{{ $company['name'] }}</strong>
            <div class="space"></div>
            <span class="name">&nbsp;</span>
        </td>
    </tr>
</table>
</div>

<div class="footnote">
    Dokumen ini dibuat secara otomatis oleh sistem {{ $company['name'] }} — {{ $docNumber }}
</div>

</body>
</html>
