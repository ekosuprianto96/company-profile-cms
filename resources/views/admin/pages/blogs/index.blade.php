@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Blog</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="{{ route('admin.blogs.create') }}" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Post Blog</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableBlog">
                  <thead>
                    <tr>
                      <th>Thumbnail</th>
                      <th>Title</th>
                      <th>Kategori</th>
                      <th>Slug</th>
                      <th>Total View</th>
                      <th>Created By</th>
                      <th>Updated By</th>
                      <th>Status</th>
                      <th class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {{-- data rows --}}
                  </tbody>
                </table>
              </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $table = $('#tableBlog').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.blogs.data") }}'
            },
            columns: [
                {data: 'thumbnail', name: 'thumbnail'},
                {data: 'title', name: 'title', search: true},
                {data: 'kategori', name: 'kategori', search: true},
                {data: 'slug', name: 'slug', search: true},
                {data: 'total_view', name: 'total_view'},
                {data: 'created_by', name: 'created_by', search: true},
                {data: 'updated_by', name: 'updated_by', search: true},
                {data: 'status', name: 'status', search: true},
                {data: 'action', name: 'action'}
            ]
        });
    });
</script>
@endsection