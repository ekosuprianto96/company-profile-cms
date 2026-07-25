@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Produk</h4>
                    <p class="text-muted mb-0">Katalog produk untuk aplikasi mobile (dengan pengaturan bundle, cakupan layanan, &amp; metode pengiriman).</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.categories') }}" class="btn btn-light btn-sm"><i class="ri-price-tag-3-line me-1"></i> Kategori</a>
                    <a href="{{ route('admin.mobile.shipping_couriers') }}" class="btn btn-light btn-sm"><i class="ri-truck-line me-1"></i> Kurir</a>
                    <a href="javascript:void(0)" id="importProduct" class="btn btn-success btn-sm"><i class="ri-file-excel-2-line me-1"></i> Import Excel</a>
                    <a href="{{ route('admin.mobile.products.create') }}" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Produk</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table w-100" id="tableProducts">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Pengaturan</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Wizard Import Produk --}}
    <div class="modal fade" id="modalImportProduct" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="ri-file-excel-2-line me-1 text-success"></i> Import Produk dari Excel</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            {{-- Step 1: Upload --}}
            <div id="importStep1">
                <div class="alert" style="background:#eef5f4;color:#214f4b;border:1px solid #d7e7e4;">
                    <b>Langkah 1.</b> Unduh template, isi datanya, lalu unggah. File dari sumber lain juga bisa — kolomnya dipetakan di langkah berikutnya.
                    Kategori memakai format <code>Induk &gt; Sub</code> (mis. <code>Furnitur &gt; Kursi</code>) dan dibuat otomatis bila belum ada.
                </div>
                <a href="{{ route('admin.mobile.products.import.template') }}" class="btn btn-outline-success btn-sm mb-3"><i class="ri-download-2-line me-1"></i> Unduh Template</a>
                <div class="mb-2"><label class="form-label fw-semibold">File Excel (.xlsx / .xls / .csv)</label>
                    <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls,.csv">
                </div>
                <div class="text-end"><button type="button" id="btnReadFile" class="btn btn-primary btn-sm"><i class="ri-arrow-right-line me-1"></i> Baca File &amp; Lanjut</button></div>
            </div>

            {{-- Step 2: Mapping --}}
            <div id="importStep2" class="d-none">
                <div class="alert" style="background:#fff7ed;color:#8a5a00;border:1px solid #ffd8a8;">
                    <b>Langkah 2.</b> Pasangkan tiap kolom database dengan header dari Excel Anda. Kolom bertanda <span class="text-danger">*</span> wajib.
                </div>
                <div class="table-responsive"><table class="table table-sm align-middle">
                    <thead><tr><th style="width:45%">Kolom Database</th><th>Kolom Excel Anda</th></tr></thead>
                    <tbody id="importMapBody"></tbody>
                </table></div>
                <div class="d-flex justify-content-between">
                    <button type="button" id="btnBackUpload" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Kembali</button>
                    <button type="button" id="btnRunImport" class="btn btn-success btn-sm"><i class="ri-play-line me-1"></i> Jalankan Import</button>
                </div>
            </div>

            {{-- Step 3: Result --}}
            <div id="importStep3" class="d-none">
                <div id="importResult"></div>
                <div class="text-end mt-3"><button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal" id="btnFinishImport">Selesai</button></div>
            </div>
        </div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        window.$productsTable = $('#tableProducts').DataTable({
            processing: true, serverSide: true, pageLength: 25,
            ajax: { method: 'get', url: '{{ route("admin.mobile.products.data") }}' },
            columns: [
                {data:'product', name:'product'},
                {data:'category', name:'category', orderable:false, searchable:false},
                {data:'price', name:'price', orderable:false, searchable:false},
                {data:'stock', name:'stock'},
                {data:'settings', name:'settings', orderable:false, searchable:false},
                {data:'status', name:'status', orderable:false, searchable:false},
                {data:'action', name:'action', orderable:false, searchable:false}
            ]
        });

    });

    // ============ Wizard Import Produk ============
    (function () {
        let importToken = null;
        let importColumns = [];
        const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
        const step = (n) => { [1,2,3].forEach((i) => document.getElementById('importStep'+i).classList.toggle('d-none', i !== n)); };

        $('#importProduct').on('click', function () {
            importToken = null;
            $('#importFile').val('');
            $('#importMapBody').empty();
            $('#importResult').empty();
            step(1);
            new bootstrap.Modal(document.getElementById('modalImportProduct')).show();
        });

        $('#btnReadFile').on('click', function () {
            const file = document.getElementById('importFile').files[0];
            if (!file) { $.toast({ heading:'Perhatian', text:'Pilih file Excel dulu.', position:'top-right', icon:'warning' }); return; }
            const fd = new FormData();
            fd.append('file', file);
            fd.append('_token', '{{ csrf_token() }}');
            const $btn = $(this).prop('disabled', true).html('<i class="ri-loader-4-line me-1"></i> Membaca…');
            $.ajax({ url:'{{ route("admin.mobile.products.import.upload") }}', method:'POST', data:fd, processData:false, contentType:false })
                .done((r) => {
                    importToken = r.token;
                    importColumns = r.columns;
                    const opts = ['<option value="">— lewati kolom ini —</option>']
                        .concat(r.headers.map((h) => `<option value="${esc(h)}">${esc(h)}</option>`)).join('');
                    $('#importMapBody').html(r.columns.map((c) => {
                        const sel = r.suggestion[c.col] || '';
                        return `<tr>
                            <td><span class="fw-semibold">${esc(c.label)}</span>${c.required ? ' <span class="text-danger">*</span>' : ''}<div><code class="text-muted" style="font-size:.75em">${esc(c.col)}</code></div></td>
                            <td><select class="form-select form-select-sm js-map" data-col="${esc(c.col)}">${opts.replace(`value="${esc(sel)}"`, `value="${esc(sel)}" selected`)}</select></td>
                        </tr>`;
                    }).join(''));
                    step(2);
                })
                .fail((e) => $.toast({ heading:'Gagal', text:(e.responseJSON||{}).message||'Gagal membaca file.', position:'top-right', icon:'error' }))
                .always(() => $btn.prop('disabled', false).html('<i class="ri-arrow-right-line me-1"></i> Baca File & Lanjut'));
        });

        $('#btnBackUpload').on('click', () => step(1));

        $('#btnRunImport').on('click', function () {
            const mapping = {};
            $('.js-map').each(function () { const v = $(this).val(); if (v) mapping[$(this).data('col')] = v; });
            if (!mapping.name || !mapping.price) { $.toast({ heading:'Perhatian', text:'Kolom Nama Produk & Harga wajib dipetakan.', position:'top-right', icon:'warning' }); return; }
            const $btn = $(this).prop('disabled', true).html('<i class="ri-loader-4-line me-1"></i> Mengimpor…');
            $.post('{{ route("admin.mobile.products.import.execute") }}', { token:importToken, mapping, _token:'{{ csrf_token() }}' })
                .done((r) => {
                    const res = r.result || {};
                    const errs = (res.errors || []);
                    const errTable = errs.length ? `
                        <div class="mt-3"><b class="text-danger">${errs.length} baris gagal:</b>
                        <div class="table-responsive mt-1"><table class="table table-sm">
                        <thead><tr><th style="width:80px">Baris</th><th>Alasan</th></tr></thead>
                        <tbody>${errs.map((x) => `<tr><td>${x.row}</td><td>${esc(x.message)}</td></tr>`).join('')}</tbody>
                        </table></div></div>` : '';
                    $('#importResult').html(`
                        <div class="text-center py-3">
                            <i class="ri-checkbox-circle-line text-success" style="font-size:44px"></i>
                            <h5 class="mt-2 mb-1">Import Selesai</h5>
                            <p class="text-muted mb-0">${res.created||0} produk baru · ${res.updated||0} diperbarui · ${errs.length} gagal (dari ${res.total||0} baris)</p>
                        </div>${errTable}`);
                    step(3);
                    window.$productsTable && window.$productsTable.ajax.reload(null, false);
                })
                .fail((e) => $.toast({ heading:'Gagal', text:(e.responseJSON||{}).message||'Gagal import.', position:'top-right', icon:'error' }))
                .always(() => $btn.prop('disabled', false).html('<i class="ri-play-line me-1"></i> Jalankan Import'));
        });
    })();

    function deleteProduct(id_product) {
        Swal.fire({ title:'Kamu yakin?', text:'Produk ini akan dihapus.', icon:'warning', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', showConfirmButton:true, showCancelButton:true, customClass:{ cancelButton:'bg-danger', confirmButton:'bg-primary' } }).then((result)=>{
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.products.destroy") }}', { id_product, _token:'{{ csrf_token() }}' })
                .done((r)=>{ window.$productsTable.ajax.reload(); $.toast({ heading:'Sukses', text:r.message, showHideTransition:'plain', position:'top-right', icon:'success' }); })
                .fail((e)=>$.toast({ heading:'Warning', text:(e.responseJSON||{}).message||'Terjadi kesalahan.', showHideTransition:'slide', position:'top-right', icon:'warning' }));
        });
    }
</script>
@endsection
