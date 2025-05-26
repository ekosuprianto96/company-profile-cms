@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Pengguna</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="{{ route('admin.pengguna.create') }}" id="tambahPengguna" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Pengguna</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="talePengguna">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>Role</th>
                      <th>Tgl Lahir</th>
                      <th>Email</th>
                      <th>No Telp</th>
                      <th>No KTP</th>
                      <th>No NIP</th>
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
        $table = $('#talePengguna').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.pengguna.data") }}'
            },
            columns: [
                {data: 'nama_lengkap', name: 'nama_lengkap', search: true},
                {data: 'role', name: 'role', search: true},
                {data: 'tgl_lahir', name: 'tgl_lahir', search: true},
                {data: 'email', name: 'email', search: true},
                {data: 'no_telp', name: 'no_telp', search: true},
                {data: 'no_ktp', name: 'no_ktp'},
                {data: 'nip', name: 'nip', search: true},
                {data: 'action', name: 'action'}
            ]
        });
    });

    function deleteUser(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Anda tidak akan bisa mengembalikan data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if(result.isConfirmed) {
                destroyPengguna(id)
                .then(response => {
                    $table.ajax.reload();

                    const { message } = response;
                    $.toast({
                        heading: 'Sukses',
                        text: message,
                        showHideTransition: 'plain',
                        position: 'top-right',
                        icon: 'success'
                    });
                })
                .catch(error => {
                    const {  message } = error.responseJSON || {};
                    $.toast({
                        heading: 'Warning',
                        text: message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                });
            }
        });
    }

    function destroyPengguna(id) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.pengguna.destroy") }}', {
                id,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection