@extends('admin.layouts.main')

@section('content')
<style>
    tbody tr td {
        text-align: center;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Social Media</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="{{ route('admin.informasi.index') }}" class="btn btn-sm btn-danger"><i class="ri-arrow-left-line"></i> Kembali</a>
                            <a href="{{ route('admin.social_media.create') }}" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableSocailMedia">
                  <thead>
                    <tr>
                      <th class="text-center">Nama</th>
                      <th class="text-center">Icon</th>
                      <th class="text-center">Link</th>
                      <th class="text-center">Status</th>
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
        $table = $('#tableSocailMedia').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.social_media.data") }}'
            },
            columns: [
                {data: 'name', name: 'name', searchable: true},
                {data: 'icon', name: 'icon'},
                {data: 'link', name: 'link'},
                {data: 'an', name: 'an'},
                {data: 'action', name: 'action'}
            ]
        });
    });

    function deleteSocialMedia(id) {
        Swal.fire({
            title: 'Kamu Yakin?',
            text: 'Apakah kamu yakin ingin menghapus ini?',
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Tidak',
            showConfirmButton: true,
            showCancelButton: true,
            customClass: {
                cancelButton: 'bg-danger',
                confirmButton: 'bg-primary'
            }
        }).then(result => {
            if(result.isConfirmed) {
                destroySocialMedia(id)
                .then(response => {
                    const { message } = response;
                    $.toast({
                        heading: 'Sukses',
                        text: message,
                        showHideTransition: 'plain',
                        position: 'top-right',
                        icon: 'success'
                    });
                    $table.ajax.reload();
                }).catch(err => {
                    const { status, message = null } = err?.responseJSON || {};
                    if(message) {
                        $.toast({
                            heading: 'Warning',
                            text: message || err?.message || err,
                            showHideTransition: 'slide',
                            position: 'top-right',
                            icon: 'warning'
                        });
                    }
                })
                return;
            }


        })
    }

    function destroySocialMedia(id) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.social_media.destroy") }}', {
                id,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection