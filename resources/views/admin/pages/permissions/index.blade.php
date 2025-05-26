@extends('admin.layouts.main')
@section('content')
<style>
    td.dt-control {
        width: 30px;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Permission</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="#" id="tambahPermission" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Permission</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tablePermission">
                  <thead>
                    <tr>
                      <th>Display</th>
                      <th>Name</th>
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

    <div class="modal fade" id="modalUpdate" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content>
            </div>
          </div>
        </div>
    </div>
    <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content>
            </div>
          </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $table = $('#tablePermission').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.permissions.data") }}'
            },
            columns: [
                {data: 'display_name', name: 'display_name', search: true},
                {data: 'name', name: 'name', search: true},
                {data: 'action', name: 'action'}
            ]
        });

        $table.on('draw', function() {
            const modalAdd = $.modalCustom({
                trigger: '#tambahPermission',
                modal: '#modalAdd',
                options: {
                    title: 'Tambah Permission'
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editPermission',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Permission',
                    bind: 'permission'
                }
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-permission=${id}]`).spinner();
                $.get('{{ route("admin.permissions.forms") }}', {
                    view: 'permission-edit',
                    id_permission: id
                })
                .done(function(response) {
                    modalEdit.render(response);
                })
                .fail(function(error) {
                    const response = error.responseJSON;
                    modalEdit.render(`<span class="alert my-3 d-block alert-danger">${response.message}</span>`);
                    $.toast({
                        heading: 'Warning',
                        text: response.message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    })
                })
                .always(function() {
                    $(`[data-bind-permission=${id}]`).spinner('hide');
                })
            });

            modalEdit.onClose(() => {
                // code
            });

            modalAdd.onShow(function() {
                $(`#tambahPermission`).spinner('show');
                $.get('{{ route("admin.permissions.forms") }}', {
                    view: 'permission-create'
                })
                .done(function(response) {
                    setTimeout(() => {
                        modalAdd.render(response);
                    }, 1000);
                })
                .fail(function(error) {
                    const response = error.responseJSON;
                    modalAdd.render(`<span class="alert my-3 d-block alert-danger">${response.message}</span>`);
                    $.toast({
                        heading: 'Warning',
                        text: response.message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                })
                .always(function() {
                    $(`#tambahPermission`).spinner('hide');
                })
            });
        });
    });

    function deletePermission(id_permission) {
        Swal.fire({
            title: 'Kamu Yakin?',
            text: 'Apakah kamu yakin ingin menghapus Role ini?',
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
                destroyPermission(id_permission)
                .then(response => {
                    const { message, status } = response;
                    if(status) {
                        $.toast({
                            heading: 'Sukses',
                            text: message,
                            showHideTransition: 'plain',
                            position: 'top-right',
                            icon: 'success'
                        });
                        $table.ajax.reload();
                    }
                }).catch(err => {
                    const { status, message } = err;
                    if(message) {
                        $.toast({
                            heading: 'Warning',
                            text: message,
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

    function destroyPermission(id_permission) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.permissions.destroy") }}', {
                id_permission,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection