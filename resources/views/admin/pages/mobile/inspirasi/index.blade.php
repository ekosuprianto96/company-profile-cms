@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Inspirasi Mobile</h4>
                    <p class="text-muted mb-0">Kelola konten inspirasi, tips, dan ide visual yang akan tampil di screen Inspire aplikasi mobile.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="{{ route('admin.mobile.inspirasi.create') }}" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> Tambah Inspire
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
                <div class="table-responsive">
                    <table class="table w-100" id="tableInspirePosts">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Featured</th>
                                <th>Status</th>
                                <th>Meta</th>
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
        window.$inspirePostsTable = $('#tableInspirePosts').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.inspirasi.data") }}'
            },
            columns: [
                {data: 'thumbnail', name: 'thumbnail', orderable: false, searchable: false},
                {data: 'title', name: 'title', search: true},
                {data: 'category', name: 'category', search: true},
                {data: 'featured', name: 'featured', orderable: false, searchable: false},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'meta', name: 'meta', orderable: false, searchable: false},
                {data: 'updated_by', name: 'updated_by', orderable: false, searchable: true},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });

    function deleteInspirePost(slug) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: 'Konten inspire ini akan dihapus permanen.',
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

            $.post('{{ route("admin.mobile.inspirasi.destroy") }}', {
                slug,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                window.$inspirePostsTable.ajax.reload();
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
