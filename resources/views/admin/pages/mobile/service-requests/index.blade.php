@extends('admin.layouts.main')

@section('content')
<style>
    /* Lebar kolom dikunci agar teks panjang (alamat) terpotong "…" dan
       kolom Total/Status/Aksi tidak terdorong keluar layar. */
    #tableServiceRequests { table-layout: fixed; width: 100% !important; }
    #tableServiceRequests th, #tableServiceRequests td { vertical-align: middle; overflow: hidden; }
    #tableServiceRequests th:nth-child(1), #tableServiceRequests td:nth-child(1) { width: 130px; }
    #tableServiceRequests th:nth-child(2), #tableServiceRequests td:nth-child(2) { width: 180px; }
    #tableServiceRequests th:nth-child(3), #tableServiceRequests td:nth-child(3) { width: 150px; }
    #tableServiceRequests th:nth-child(4), #tableServiceRequests td:nth-child(4) { width: auto; }
    #tableServiceRequests th:nth-child(5), #tableServiceRequests td:nth-child(5) { width: 110px; }
    #tableServiceRequests th:nth-child(6), #tableServiceRequests td:nth-child(6) { width: 125px; }
    #tableServiceRequests th:nth-child(7), #tableServiceRequests td:nth-child(7) { width: 132px; overflow: visible; }
    /* Aksi: jaga tetap satu baris, tidak terpotong */
    #tableServiceRequests td:nth-child(7) .btn-xs { padding: 4px 7px; }

    /* Potong teks panjang dengan elipsis */
    .service-request-clamp-1,
    #tableServiceRequests td .fw-semibold,
    #tableServiceRequests td .text-muted {
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .service-request-clamp-2 {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; white-space: normal;
    }
    /* Badge status tidak boleh ikut terpotong */
    #tableServiceRequests td .rounded-pill { overflow: visible; text-overflow: clip; }

    /* Chip status: filter utama yang paling sering dipakai admin */
    .sr-chip {
        border: 1px solid #e2e8f0; background: #fff; color: #475569; border-radius: 999px;
        padding: 5px 13px; font-size: 12px; font-weight: 600; white-space: nowrap; cursor: pointer;
    }
    .sr-chip:hover { border-color: #cbd5e1; }
    .sr-chip.active { background: #275a56; border-color: #275a56; color: #fff; }
    .sr-chips { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 2px; }
    .sr-chips::-webkit-scrollbar { height: 4px; }
    .sr-chips::-webkit-scrollbar-thumb { background: #d7e7e4; border-radius: 4px; }
</style>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Order Layanan</h4>
                    <p class="text-muted mb-0">Daftar order layanan yang masuk dari aplikasi. Buka detail untuk approve/reject, cek pembayaran, atau unduh proposal.</p>
                </div>
                <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Overview</a>
            </div>
        </div>
    </div>

    {{-- Kartu ringkasan: kelola berdasarkan prioritas. Klik untuk memfilter. --}}
    <div class="col-md-12 mb-3">
        <div class="row g-3" id="srSummary">
            @php
                $cards = [
                    ['key' => 'waiting_payment', 'label' => 'Menunggu Bayar', 'icon' => 'ri-time-line', 'c' => '#1d4ed8', 'bg' => '#eff6ff', 'status' => 'waiting_payment'],
                    ['key' => 'verify_transfer', 'label' => 'Verifikasi Transfer', 'icon' => 'ri-bank-line', 'c' => '#c2410c', 'bg' => '#fff7ed', 'status' => 'waiting_transfer'],
                    ['key' => 'need_review', 'label' => 'Perlu Review', 'icon' => 'ri-search-eye-line', 'c' => '#7c3aed', 'bg' => '#f5f3ff', 'payment' => 'paid'],
                    ['key' => 'active', 'label' => 'Aktif', 'icon' => 'ri-loader-4-line', 'c' => '#047857', 'bg' => '#ecfdf5', 'status' => 'approved'],
                    ['key' => 'completed', 'label' => 'Selesai', 'icon' => 'ri-flag-2-line', 'c' => '#334155', 'bg' => '#f1f5f9', 'status' => 'completed'],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="col-6 col-md">
                    <div class="card border-0 shadow-sm h-100 sr-summary-card" role="button"
                         data-status="{{ $card['status'] ?? '' }}" data-payment="{{ $card['payment'] ?? '' }}"
                         style="cursor:pointer;">
                        <div class="card-body d-flex align-items-center" style="gap:12px; padding:14px 16px;">
                            <span class="d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:{{ $card['bg'] }};color:{{ $card['c'] }};font-size:20px;">
                                <i class="{{ $card['icon'] }}"></i>
                            </span>
                            <div>
                                <div class="fw-bold" style="font-size:1.3rem;line-height:1;">{{ $summary[$card['key']] ?? 0 }}</div>
                                <div class="text-muted" style="font-size:.76rem;">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                {{-- Toolbar: cari + layanan + aksi --}}
                <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 10px;">
                    <div class="position-relative flex-grow-1" style="min-width: 240px;">
                        <i class="ri-search-line position-absolute text-muted" style="left:12px; top:50%; transform:translateY(-50%);"></i>
                        <input type="text" class="form-control" id="filterSearch" style="padding-left: 34px;"
                               placeholder="Cari kode order, nama pemesan, atau alamat…">
                    </div>

                    <select class="form-select" id="filterService" style="max-width: 220px;">
                        <option value="">Semua Layanan</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-light" id="toggleAdvanced">
                        <i class="ri-equalizer-line me-1"></i> Filter lain
                        <span class="badge bg-danger ms-1 d-none" id="advancedCount">0</span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ri-download-2-line me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="javascript:void(0)" id="exportServiceRequestsExcel"><i class="ri-file-excel-2-line me-2 text-success"></i>Excel</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" id="exportServiceRequestsPdf"><i class="ri-file-pdf-line me-2 text-danger"></i>PDF</a></li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-light" id="resetServiceRequestFilter" title="Reset semua filter">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>

                {{-- Status = filter utama, tampil sebagai chip --}}
                <div class="sr-chips mb-3" id="statusChips">
                    @foreach ($statusOptions as $option)
                        <span class="sr-chip {{ $option['value'] === '' ? 'active' : '' }}" data-status="{{ $option['value'] }}">
                            {{ $option['value'] === '' ? 'Semua' : $option['label'] }}
                        </span>
                    @endforeach
                </div>

                {{-- Filter lanjutan: disembunyikan agar halaman tidak penuh --}}
                <div class="border rounded p-3 mb-3 d-none" id="advancedFilters" style="background:#fafbfc;">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Status Pembayaran</label>
                            <select class="form-select form-select-sm js-adv" id="filterPaymentStatus">
                                @foreach ($paymentStatusOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Survei Dari</label>
                            <input type="date" class="form-control form-control-sm js-adv" id="filterSurveyFrom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Survei Sampai</label>
                            <input type="date" class="form-control form-control-sm js-adv" id="filterSurveyTo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Wilayah (bebas)</label>
                            <input type="text" class="form-control form-control-sm js-adv" id="filterRegion" placeholder="Provinsi, kota, kecamatan…">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Provinsi</label>
                            <input type="text" class="form-control form-control-sm js-adv" id="filterProvince" placeholder="mis. Nusa Tenggara Barat">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Kota / Kabupaten</label>
                            <input type="text" class="form-control form-control-sm js-adv" id="filterRegency" placeholder="mis. Lombok Barat">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Kecamatan</label>
                            <input type="text" class="form-control form-control-sm js-adv" id="filterDistrict" placeholder="mis. Selong">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:.82em">Kelurahan / Desa</label>
                            <input type="text" class="form-control form-control-sm js-adv" id="filterVillage" placeholder="mis. Pringgasela">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0" id="tableServiceRequests">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Pemesan</th>
                                <th>Layanan</th>
                                <th>Jadwal &amp; Lokasi</th>
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
    $(document).ready(function() {
        let currentStatus = '';

        function filterParams() {
            return {
                search: $('#filterSearch').val() || '',
                service_id: $('#filterService').val() || '',
                status: currentStatus || '',
                payment_status: $('#filterPaymentStatus').val() || '',
                region: $('#filterRegion').val() || '',
                province: $('#filterProvince').val() || '',
                regency: $('#filterRegency').val() || '',
                district: $('#filterDistrict').val() || '',
                village: $('#filterVillage').val() || '',
                survey_from: $('#filterSurveyFrom').val() || '',
                survey_to: $('#filterSurveyTo').val() || '',
            };
        }

        window.$serviceRequestsTable = $('#tableServiceRequests').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            autoWidth: false,
            pageLength: 25,
            language: { emptyTable: 'Belum ada order layanan.', processing: 'Memuat…' },
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.service_requests.data") }}',
                data: (d) => Object.assign(d, filterParams()),
            },
            columns: [
                {data: 'order', name: 'order', orderable: false, searchable: false},
                {data: 'requester', name: 'requester', orderable: false, searchable: false},
                {data: 'service', name: 'service', orderable: false, searchable: false},
                {data: 'schedule', name: 'schedule', orderable: false, searchable: false},
                {data: 'amount', name: 'total_amount', className: 'text-end'},
                {data: 'status_cell', name: 'status_cell', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
            ],
        });

        const reload = () => window.$serviceRequestsTable.ajax.reload();

        // Hitung filter lanjutan yang aktif → tampil sebagai badge.
        function refreshAdvancedCount() {
            const active = $('.js-adv').filter(function () { return !!$(this).val(); }).length;
            $('#advancedCount').text(active).toggleClass('d-none', active === 0);
        }

        $('#statusChips').on('click', '.sr-chip', function () {
            $('#statusChips .sr-chip').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status') || '';
            reload();
        });

        // Kartu ringkasan → set filter status/pembayaran yang relevan.
        $('#srSummary').on('click', '.sr-summary-card', function () {
            const status = String($(this).data('status') || '');
            const payment = String($(this).data('payment') || '');
            $('#filterPaymentStatus').val(payment);
            currentStatus = status;
            $('#statusChips .sr-chip').removeClass('active');
            const chip = $('#statusChips .sr-chip[data-status="' + status + '"]');
            (chip.length ? chip : $('#statusChips .sr-chip').first()).addClass('active');
            refreshAdvancedCount();
            reload();
        });

        $('#toggleAdvanced').on('click', () => $('#advancedFilters').toggleClass('d-none'));

        let debounce;
        $('#filterSearch').on('input', function () {
            clearTimeout(debounce);
            debounce = setTimeout(reload, 400);
        });

        $('#filterService').on('change', reload);
        $('.js-adv').on('change', function () { refreshAdvancedCount(); reload(); });

        $('#resetServiceRequestFilter').on('click', function () {
            $('#filterSearch, #filterRegion, #filterProvince, #filterRegency, #filterDistrict, #filterVillage, #filterSurveyFrom, #filterSurveyTo').val('');
            $('#filterService, #filterPaymentStatus').val('');
            currentStatus = '';
            $('#statusChips .sr-chip').removeClass('active').first().addClass('active');
            refreshAdvancedCount();
            reload();
        });

        const exportTo = (url) => { window.location.href = url + '?' + new URLSearchParams(filterParams()).toString(); };
        $('#exportServiceRequestsExcel').on('click', () => exportTo('{{ route("admin.mobile.service_requests.export") }}'));
        $('#exportServiceRequestsPdf').on('click', () => exportTo('{{ route("admin.mobile.service_requests.export_pdf") }}'));
    });
</script>
@endsection
