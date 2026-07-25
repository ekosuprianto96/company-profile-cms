@extends('admin.layouts.main')

@section('content')
<style>
    #tableProductOrders { table-layout: fixed; width: 100% !important; }
    #tableProductOrders th, #tableProductOrders td { vertical-align: middle; overflow: hidden; }
    #tableProductOrders td .text-truncate { max-width: 100%; }
    #tableProductOrders th:nth-child(1), #tableProductOrders td:nth-child(1) { width: 150px; }
    #tableProductOrders th:nth-child(2), #tableProductOrders td:nth-child(2) { width: 170px; }
    #tableProductOrders th:nth-child(3), #tableProductOrders td:nth-child(3) { width: auto; }
    #tableProductOrders th:nth-child(4), #tableProductOrders td:nth-child(4) { width: 120px; }
    #tableProductOrders th:nth-child(5), #tableProductOrders td:nth-child(5) { width: 160px; overflow: visible; white-space: normal; }
    #tableProductOrders td:nth-child(5) .badge { display: inline-block; max-width: 100%; }
    #tableProductOrders th:nth-child(6), #tableProductOrders td:nth-child(6) { width: 90px; }
    .po-chip { border:1px solid #e2e8f0; background:#fff; color:#475569; border-radius:999px;
        padding:5px 13px; font-size:12px; font-weight:600; white-space:nowrap; cursor:pointer; }
    .po-chip:hover { border-color:#cbd5e1; }
    .po-chip.active { background:#275a56; border-color:#275a56; color:#fff; }
    .po-chips { display:flex; gap:8px; overflow-x:auto; padding-bottom:2px; }
    .po-chips::-webkit-scrollbar { height:4px; } .po-chips::-webkit-scrollbar-thumb { background:#d7e7e4; border-radius:4px; }
</style>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                <div>
                    <h4 class="card-title mb-1">Order Produk</h4>
                    <p class="text-muted mb-0">Daftar pesanan produk dari aplikasi. Buka detail untuk memproses pesanan, cek pembayaran, input resi, atau cetak invoice.</p>
                </div>
                <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Overview</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="d-flex flex-wrap align-items-center mb-3" style="gap:10px;">
                    <div class="position-relative flex-grow-1" style="min-width:240px;">
                        <i class="ri-search-line position-absolute text-muted" style="left:12px; top:50%; transform:translateY(-50%);"></i>
                        <input type="text" class="form-control" id="filterSearch" style="padding-left:34px;"
                               placeholder="Cari no. order, pelanggan, produk…">
                    </div>

                    <select class="form-select" id="filterPayment" style="max-width:190px;">
                        <option value="">Semua Pembayaran</option>
                        <option value="paid">Lunas</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Gagal</option>
                    </select>

                    <button type="button" class="btn btn-light" id="toggleAdvanced">
                        <i class="ri-equalizer-line me-1"></i> Tanggal
                        <span class="badge bg-danger ms-1 d-none" id="advancedCount">0</span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="ri-download-2-line me-1"></i> Export</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ri-file-excel-2-line me-2 text-success"></i>Excel</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" id="exportPdf"><i class="ri-file-pdf-line me-2 text-danger"></i>PDF</a></li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-light" id="resetFilter" title="Reset filter"><i class="ri-refresh-line"></i></button>
                </div>

                <div class="po-chips mb-3" id="statusChips">
                    <span class="po-chip active" data-status="">Semua</span>
                    @foreach ($statusOptions as $opt)
                        <span class="po-chip" data-status="{{ $opt['value'] }}">{{ $opt['label'] }}</span>
                    @endforeach
                </div>

                <div class="border rounded p-3 mb-3 d-none" id="advancedFilters" style="background:#fafbfc;">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Tanggal Dari</label>
                            <input type="date" class="form-control form-control-sm js-adv" id="filterDateFrom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Tanggal Sampai</label>
                            <input type="date" class="form-control form-control-sm js-adv" id="filterDateTo">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0" id="tableProductOrders">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Pelanggan</th>
                                <th>Produk</th>
                                <th class="text-end">Total</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let currentStatus = '';

        function params() {
            return {
                search: $('#filterSearch').val() || '',
                status: currentStatus || '',
                payment_status: $('#filterPayment').val() || '',
                date_from: $('#filterDateFrom').val() || '',
                date_to: $('#filterDateTo').val() || '',
            };
        }

        window.$productOrdersTable = $('#tableProductOrders').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            autoWidth: false,
            pageLength: 25,
            language: { emptyTable: 'Belum ada order produk.', processing: 'Memuat…' },
            ajax: { method: 'get', url: '{{ route("admin.mobile.product_orders.data") }}', data: (d) => Object.assign(d, params()) },
            columns: [
                {data: 'order', name: 'order', orderable: false, searchable: false},
                {data: 'customer', name: 'customer', orderable: false, searchable: false},
                {data: 'product', name: 'product', orderable: false, searchable: false},
                {data: 'total', name: 'grand_total', className: 'text-end'},
                {data: 'status_cell', name: 'status_cell', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
            ],
        });

        const reload = () => window.$productOrdersTable.ajax.reload();

        function refreshAdvancedCount() {
            const active = $('.js-adv').filter(function () { return !!$(this).val(); }).length;
            $('#advancedCount').text(active).toggleClass('d-none', active === 0);
        }

        $('#statusChips').on('click', '.po-chip', function () {
            $('#statusChips .po-chip').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status') || '';
            reload();
        });
        $('#toggleAdvanced').on('click', () => $('#advancedFilters').toggleClass('d-none'));

        let debounce;
        $('#filterSearch').on('input', function () { clearTimeout(debounce); debounce = setTimeout(reload, 400); });
        $('#filterPayment').on('change', reload);
        $('.js-adv').on('change', function () { refreshAdvancedCount(); reload(); });

        $('#resetFilter').on('click', function () {
            $('#filterSearch, #filterDateFrom, #filterDateTo').val('');
            $('#filterPayment').val('');
            currentStatus = '';
            $('#statusChips .po-chip').removeClass('active').first().addClass('active');
            refreshAdvancedCount();
            reload();
        });

        const exportTo = (url) => { window.location.href = url + '?' + new URLSearchParams(params()).toString(); };
        $('#exportExcel').on('click', () => exportTo('{{ route("admin.mobile.product_orders.export_excel") }}'));
        $('#exportPdf').on('click', () => exportTo('{{ route("admin.mobile.product_orders.export_pdf") }}'));
    });
</script>
@endsection
