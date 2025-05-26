@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Pesan Yang Dikirm</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="{{ route('admin.email.create_message') }}" id="create__message" class="btn btn-sm btn-primary"><i class="ri-mail-send-line me-2"></i> Buat Pesan</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableMessage">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>Email</th>
                      <th>From Email</th>
                      <th>Subject</th>
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
        $table = $('#tableMessage').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.email.data_email_sending") }}'
            },
            columns: [
                {data: 'name', name: 'name', search: true},
                {data: 'to_email', name: 'to_email', search: true},
                {data: 'from_email', name: 'from_email', search: true},
                {data: 'subject', name: 'subject'},
                {data: 'status', name: 'status', search: true},
                {data: 'action', name: 'action'}
            ]
        });
    });

    function deleteMessage(id) {
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
                destroyMessage(id)
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

    function destroyMessage(id) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.email.destroy_message") }}', {
                id,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection