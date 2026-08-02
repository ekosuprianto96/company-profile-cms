<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 110px 48px 70px 48px; }
    * { font-family: "DejaVu Sans", sans-serif; }
    body { color: #1f2937; font-size: 11px; line-height: 1.6; }

    /* Running header & footer (muncul tiap halaman) */
    .rheader { position: fixed; top: -78px; left: 0; right: 0; height: 40px; border-bottom: 1.5px solid #275a56; }
    .rheader .ttl { color: #275a56; font-size: 10px; font-weight: bold; letter-spacing: .5px; }
    .rheader .app { color: #94a3b8; font-size: 9px; text-align: right; }
    .rfooter { position: fixed; bottom: -50px; left: 0; right: 0; height: 30px; border-top: 1px solid #e5e7eb; color: #94a3b8; font-size: 8.5px; }

    h1.chapter { color: #275a56; font-size: 20px; margin: 0 0 4px 0; }
    .chapter-kicker { color: #c8915c; font-size: 9px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 2px; }
    h2.sec { color: #14201e; font-size: 14px; margin: 18px 0 6px 0; border-left: 4px solid #c8915c; padding-left: 8px; }
    h3.sub { color: #275a56; font-size: 12px; margin: 12px 0 4px 0; }
    p { margin: 0 0 8px 0; }
    ul, ol { margin: 0 0 8px 0; padding-left: 18px; }
    li { margin-bottom: 3px; }
    code { background: #f1f5f4; color: #275a56; padding: 1px 4px; border-radius: 3px; font-size: 10px; }
    b, strong { color: #14201e; }

    /* Breadcrumb lokasi menu */
    .path { display: inline-block; background: #eef5f4; color: #275a56; border: 1px solid #d7e7e4; border-radius: 5px; padding: 3px 9px; font-size: 10px; font-weight: bold; margin: 2px 0 10px 0; }

    /* Kotak tips / catatan / peringatan */
    .box { border-radius: 6px; padding: 9px 12px; margin: 8px 0; font-size: 10.5px; }
    .box.tip { background: #edf7f0; border: 1px solid #cdead7; }
    .box.note { background: #eef4fb; border: 1px solid #d4e5f6; }
    .box.warn { background: #fdf2ec; border: 1px solid #f6dcc9; }
    .box .bt { font-weight: bold; display: block; margin-bottom: 2px; }
    .box.tip .bt { color: #2e7d32; } .box.note .bt { color: #0369a1; } .box.warn .bt { color: #b45309; }

    table.tbl { width: 100%; border-collapse: collapse; margin: 8px 0; }
    table.tbl th { background: #275a56; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px; }
    table.tbl td { border: 1px solid #e5e7eb; padding: 6px 8px; font-size: 10px; vertical-align: top; }
    table.tbl tr:nth-child(even) td { background: #f9fafb; }

    /* dompdf tidak mendukung counter() pada content:, jadi pakai penomoran <ol> native */
    ol.steps { padding-left: 20px; margin: 8px 0; }
    ol.steps > li { margin-bottom: 6px; padding-left: 4px; color: #1f2937; }
    ol.steps > li::marker { color: #275a56; font-weight: bold; }

    .chapter-page { page-break-before: always; }
</style>
</head>
<body>

{{-- Header & footer berjalan --}}
<div class="rheader">
    <table style="width:100%; border:0;"><tr>
        <td class="ttl">PANDUAN PENGGUNAAN DASHBOARD ADMIN</td>
        <td class="app">{{ $appName }}</td>
    </tr></table>
</div>
<div class="rfooter">
    <table style="width:100%; border:0;"><tr>
        <td>{{ $appName }} · Panduan Admin · © ESV Digital Solution</td>
        <td style="text-align:right;">Diperbarui {{ $generatedAt }}</td>
    </tr></table>
</div>

{{-- ================= COVER ================= --}}
<table style="width:100%; height:640px; border:0;">
    <tr><td style="vertical-align:middle; text-align:center;">
        <div style="background:#275a56; color:#fff; border-radius:14px; padding:40px 30px;">
            <div style="color:#ffd9b0; font-size:11px; letter-spacing:2px; font-weight:bold;">BUKU PANDUAN · LOG BOOK</div>
            <div style="font-size:30px; font-weight:bold; margin-top:14px; line-height:1.25;">Panduan Penggunaan<br>Dashboard Admin</div>
            <div style="color:#cfe3df; font-size:13px; margin-top:14px;">{{ $appName }}</div>
        </div>
        <div style="margin-top:26px; color:#64748b; font-size:11px;">
            Petunjuk pengaturan &amp; penggunaan setiap modul dan fitur di dashboard admin.
        </div>
        <div style="margin-top:6px; color:#94a3b8; font-size:10px;">Dokumen diperbarui: {{ $generatedAt }}</div>

        <div style="margin-top:40px; border-top:1px solid #e5e7eb; width:280px; margin-left:auto; margin-right:auto; padding-top:14px;">
            <div style="color:#94a3b8; font-size:9px; letter-spacing:1.5px; font-weight:bold;">DIKEMBANGKAN OLEH</div>
            <div style="color:#275a56; font-size:15px; font-weight:bold; margin-top:4px;">ESV Digital Solution</div>
            <div style="color:#64748b; font-size:10px; margin-top:2px;">Eko Suprianto — Founder ESV Digital Solution</div>
        </div>
    </td></tr>
</table>

{{-- ================= DAFTAR ISI ================= --}}
<div class="chapter-page">
    <div class="chapter-kicker">Daftar Isi</div>
    <h1 class="chapter">Daftar Isi</h1>
    @php $no = 1; @endphp
    @foreach ($groups as $group)
        <h2 class="sec">{{ $group['title'] }}</h2>
        <table style="width:100%; border:0;">
            @foreach ($group['chapters'] as $chapter)
                <tr>
                    <td style="width:28px; color:#c8915c; font-weight:bold;">{{ $no }}.</td>
                    <td>{{ $chapter['title'] }}</td>
                </tr>
                @php $no++; @endphp
            @endforeach
        </table>
    @endforeach
    <div class="box note" style="margin-top:16px;">
        <span class="bt">Cara membaca</span>
        Ikon <span class="path" style="margin:0;">Menu → Submenu</span> menunjukkan lokasi fitur di dashboard.
        Kotak hijau = tips, biru = catatan, oranye = perhatian.
    </div>
</div>

{{-- ================= BAB ================= --}}
@php $chapterNo = 1; @endphp
@foreach ($groups as $group)
    @foreach ($group['chapters'] as $chapter)
        <div class="chapter-page">
            <div class="chapter-kicker">{{ $group['title'] }} · Bab {{ $chapterNo }}</div>
            <h1 class="chapter">{{ $chapter['title'] }}</h1>
            @include('admin.pdf.guide.chapters.' . $chapter['key'])
        </div>
        @php $chapterNo++; @endphp
    @endforeach
@endforeach

{{-- Nomor halaman --}}
<script type="text/php">
if (isset($pdf)) {
    $txt = "Hal. {PAGE_NUM} / {PAGE_COUNT}";
    $font = $fontMetrics->getFont("DejaVu Sans", "normal");
    $size = 8;
    $w = $fontMetrics->get_text_width($txt, $font, $size);
    $pdf->page_text(($pdf->get_width() - $w) / 2, $pdf->get_height() - 32, $txt, $font, $size, array(0.58,0.65,0.72));
}
</script>

</body>
</html>
