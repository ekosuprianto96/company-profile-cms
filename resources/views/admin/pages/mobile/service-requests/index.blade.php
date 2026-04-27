@extends('admin.layouts.main')

@section('content')
<style>
    #tableServiceRequests {
        width: 100% !important;
        table-layout: fixed;
    }

    #tableServiceRequests thead th {
        white-space: nowrap;
        vertical-align: middle;
    }

    #tableServiceRequests tbody td {
        vertical-align: middle;
    }

    .service-request-cell {
        overflow: hidden;
        min-width: 0;
    }

    .service-request-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .service-request-clamp-1 {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Pengajuan Mobile</h4>
                    <p class="text-muted mb-0">Pantau semua pengajuan dari aplikasi mobile, lalu buka detail untuk approve, reject, atau download proposal PDF.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form id="serviceRequestFilterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kata Kunci</label>
                        <input type="text" class="form-control" id="filterSearch" placeholder="Kode transaksi, nama, alamat...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Layanan</label>
                        <select class="form-select" id="filterService">
                            <option value="">Semua Layanan</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Pembayaran</label>
                        <select class="form-select" id="filterPaymentStatus">
                            @foreach ($paymentStatusOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Wilayah</label>
                        <input type="text" class="form-control" id="filterRegion" placeholder="Provinsi, kota, kecamatan...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" class="form-control" id="filterProvince" placeholder="Contoh: Nusa Tenggara Barat">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kota / Kabupaten</label>
                        <input type="text" class="form-control" id="filterRegency" placeholder="Contoh: Lombok Barat">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" class="form-control" id="filterDistrict" placeholder="Contoh: Selong">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelurahan / Desa</label>
                        <input type="text" class="form-control" id="filterVillage" placeholder="Contoh: Pringgasela">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Survey Dari</label>
                        <input type="date" class="form-control" id="filterSurveyFrom">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Survey Sampai</label>
                        <input type="date" class="form-control" id="filterSurveyTo">
                    </div>
                    <div class="col-md-6 d-flex align-items-end justify-content-end" style="gap: 10px;">
                        <button type="button" class="btn btn-light" id="resetServiceRequestFilter">
                            <i class="ri-refresh-line me-1"></i> Reset
                        </button>
                        <button type="button" class="btn btn-success" id="exportServiceRequestsExcel">
                            <i class="ri-file-excel-2-line me-1"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-danger" id="exportServiceRequestsPdf">
                            <i class="ri-file-pdf-line me-1"></i> Export PDF
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle w-100 mb-0" id="tableServiceRequests">
                        <thead>
                            <tr>
                                <th>Pemesan</th>
                                <th>Layanan</th>
                                <th>Jadwal</th>
                                <th>Alamat</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th class="text-center">Action</th>
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
        window.$serviceRequestsTable = $('#tableServiceRequests').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 25,
            responsive: false,
            columnDefs: [
                { targets: 0, width: '220px' },
                { targets: 1, width: '180px' },
                { targets: 2, width: '300px' },
                { targets: 3, width: '260px' },
                { targets: 4, width: '120px', className: 'text-nowrap' },
                { targets: 5, width: '120px', className: 'text-nowrap' },
                { targets: 6, width: '130px', className: 'text-nowrap' },
                { targets: 7, width: '110px', className: 'text-center text-nowrap' },
            ],
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.service_requests.data") }}',
                data: function (d) {
                    d.search = $('#filterSearch').val();
                    d.service_id = $('#filterService').val();
                    d.status = $('#filterStatus').val();
                    d.payment_status = $('#filterPaymentStatus').val();
                    d.region = $('#filterRegion').val();
                    d.province = $('#filterProvince').val();
                    d.regency = $('#filterRegency').val();
                    d.district = $('#filterDistrict').val();
                    d.village = $('#filterVillage').val();
                    d.survey_from = $('#filterSurveyFrom').val();
                    d.survey_to = $('#filterSurveyTo').val();
                }
            },
            columns: [
                {data: 'requester', name: 'requester', orderable: false, searchable: false, width: '220px'},
                {data: 'service', name: 'service', orderable: false, searchable: false, width: '180px'},
                {data: 'schedule', name: 'schedule', orderable: false, searchable: false, width: '320px'},
                {data: 'region', name: 'region', orderable: false, searchable: false, width: '240px'},
                {data: 'amount', name: 'total_amount', width: '120px'},
                {data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '120px'},
                {data: 'payment_badge', name: 'payment_badge', orderable: false, searchable: false, width: '130px'},
                {data: 'action', name: 'action', orderable: false, searchable: false, width: '110px'}
            ]
        });

        $('#serviceRequestFilterForm').on('submit', function(e) {
            e.preventDefault();
            window.$serviceRequestsTable.ajax.reload();
        });

        $('#resetServiceRequestFilter').on('click', function() {
            $('#serviceRequestFilterForm')[0].reset();
            window.$serviceRequestsTable.ajax.reload();
        });

        $('#exportServiceRequestsExcel').on('click', function() {
            const params = new URLSearchParams({
                search: $('#filterSearch').val() || '',
                service_id: $('#filterService').val() || '',
                status: $('#filterStatus').val() || '',
                payment_status: $('#filterPaymentStatus').val() || '',
                region: $('#filterRegion').val() || '',
                province: $('#filterProvince').val() || '',
                regency: $('#filterRegency').val() || '',
                district: $('#filterDistrict').val() || '',
                village: $('#filterVillage').val() || '',
                survey_from: $('#filterSurveyFrom').val() || '',
                survey_to: $('#filterSurveyTo').val() || '',
            });

            window.location.href = '{{ route("admin.mobile.service_requests.export") }}' + '?' + params.toString();
        });

        $('#exportServiceRequestsPdf').on('click', function() {
            const params = new URLSearchParams({
                search: $('#filterSearch').val() || '',
                service_id: $('#filterService').val() || '',
                status: $('#filterStatus').val() || '',
                payment_status: $('#filterPaymentStatus').val() || '',
                region: $('#filterRegion').val() || '',
                province: $('#filterProvince').val() || '',
                regency: $('#filterRegency').val() || '',
                district: $('#filterDistrict').val() || '',
                village: $('#filterVillage').val() || '',
                survey_from: $('#filterSurveyFrom').val() || '',
                survey_to: $('#filterSurveyTo').val() || '',
            });

            window.location.href = '{{ route("admin.mobile.service_requests.export_pdf") }}' + '?' + params.toString();
        });
    });
</script>
@endsection
