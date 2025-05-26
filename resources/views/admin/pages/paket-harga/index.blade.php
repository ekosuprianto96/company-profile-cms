@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Paket</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="{{ route('admin.informasi.index') }}" class="btn btn-sm btn-danger"><i class="ri-arrow-left-line"></i> Kembali</a>
                            <a href="{{ route('admin.packages.create') }}" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Paket</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tablePaket">
                  <thead>
                    <tr>
                      <th class="text-center">Nama</th>
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
        $table = $('#tablePaket').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.packages.data") }}'
            },
            columns: [
                {data: 'name', name: 'name', searchable: true},
                {data: 'action', name: 'action'}
            ]
        });
    });

    function deletePackage(id) {
        Swal.fire({
            title: 'Kamu Yakin?',
            text: 'Apakah kamu yakin ingin menghapus paket ini?',
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
                destoryPackage(id)
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

    function destoryPackage(id) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.packages.destroy") }}', {
                id,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection