@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Layanan Mobile App</h4>
                    <p class="text-muted mb-0">Kelola layanan khusus aplikasi mobile. Data ini otomatis dipakai di Home dan Form Pengajuan mobile app.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.services.create') }}" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> Tambah Layanan Mobile
                    </a>
                    <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" id="serviceScopeTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0)" data-scope="all">
                            <i class="ri-list-check-2 me-1"></i> Semua Layanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)" data-scope="home">
                            <i class="ri-home-4-line me-1"></i> Tampil di Home
                        </a>
                    </li>
                </ul>

                <div class="row g-2 mb-3 align-items-end js-service-filters">
                    <div class="col-md-3 col-6">
                        <label class="form-label mb-1" style="font-size:.8em">Kategori</label>
                        <select id="filterCategory" class="form-control form-control-sm js-service-filter">
                            <option value="">Semua kategori</option>
                            @foreach ($categoryOptions as $opt)
                                <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label mb-1" style="font-size:.8em">Flow</label>
                        <select id="filterFlow" class="form-control form-control-sm js-service-filter">
                            <option value="">Semua</option>
                            <option value="standard">Standard</option>
                            <option value="event_project">Event Project</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-6">
                        <label class="form-label mb-1" style="font-size:.8em">Featured</label>
                        <select id="filterFeatured" class="form-control form-control-sm js-service-filter">
                            <option value="">Semua</option>
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-6">
                        <label class="form-label mb-1" style="font-size:.8em">Popular</label>
                        <select id="filterPopular" class="form-control form-control-sm js-service-filter">
                            <option value="">Semua</option>
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-6">
                        <label class="form-label mb-1" style="font-size:.8em">New</label>
                        <select id="filterNew" class="form-control form-control-sm js-service-filter">
                            <option value="">Semua</option>
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label mb-1" style="font-size:.8em">Coming Soon</label>
                        <select id="filterComingSoon" class="form-control form-control-sm js-service-filter">
                            <option value="">Semua</option>
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-6">
                        <label class="form-label mb-1" style="font-size:.8em">Status</label>
                        <select id="filterStatus" class="form-control form-control-sm js-service-filter">
                            <option value="">Semua</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-6">
                        <button type="button" id="resetServiceFilter" class="btn btn-light btn-sm w-100" title="Reset filter">
                            <i class="ri-refresh-line"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table w-100" id="tableMobileServices">
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Visual</th>
                                <th>Flags</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th>Updated By</th>
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
        window.$mobileServicesTable = $('#tableMobileServices').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.services.data") }}',
                data: function(d) {
                    d.scope = window.serviceScope || 'all';
                    d.category_id = $('#filterCategory').val();
                    d.request_flow_type = $('#filterFlow').val();
                    d.is_featured = $('#filterFeatured').val();
                    d.is_popular = $('#filterPopular').val();
                    d.is_new = $('#filterNew').val();
                    d.is_coming_soon = $('#filterComingSoon').val();
                    d.is_active = $('#filterStatus').val();
                }
            },
            columns: [
                {data: 'title', name: 'title'},
                {data: 'visual', name: 'visual', orderable: false, searchable: false},
                {data: 'flags', name: 'flags', orderable: false, searchable: false},
                {data: 'sort_order', name: 'sort_order'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'updated_by', name: 'updated_by', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        window.serviceScope = 'all';

        $('#serviceScopeTabs .nav-link').on('click', function() {
            $('#serviceScopeTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            window.serviceScope = $(this).data('scope');
            window.$mobileServicesTable.ajax.reload();
        });

        $('.js-service-filter').on('change', function() {
            window.$mobileServicesTable.ajax.reload();
        });

        $('#resetServiceFilter').on('click', function() {
            $('.js-service-filter').val('');
            window.$mobileServicesTable.ajax.reload();
        });

    });

    function deleteMobileService(id_mobile_service) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: 'Data layanan mobile ini akan dihapus permanen.',
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            showConfirmButton: true,
            showCancelButton: true,
            customClass: {
                cancelButton: 'bg-danger',
                confirmButton: 'bg-primary'
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('{{ route("admin.mobile.services.destroy") }}', {
                id_mobile_service,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                window.$mobileServicesTable.ajax.reload();

                $.toast({
                    heading: 'Sukses',
                    text: response.message,
                    showHideTransition: 'plain',
                    position: 'top-right',
                    icon: 'success'
                });
            })
            .fail(function(error) {
                const response = error.responseJSON || {};
                $.toast({
                    heading: 'Warning',
                    text: response.message || 'Terjadi kesalahan.',
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'warning'
                });
            });
        });
    }
</script>
@endsection
