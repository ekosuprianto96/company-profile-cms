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
                        <h4 class="card-title">Daftar Roles</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="#" id="tambahRole" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Role</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableRole">
                  <thead>
                    <tr>
                      <th style="width: 20px">#</th>
                      <th>Nama</th>
                      <th>Total Menu Akses</th>
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
    <div class="modal fade" id="modalSetting" tabindex="-1">
        <div class="modal-dialog modal-lg">
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
        $table = $('#tableRole').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.roles.data") }}'
            },
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: ''
                },
                {data: 'nama', name: 'nama', search: true},
                {data: 'total_akses', name: 'total_akses', search: true},
                {data: 'action', name: 'action'}
            ]
        });

        $table.on('click', 'td.dt-control', function (e) {
            let tr = e.target.closest('tr');
            let row = $table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
            } else {
                // Open this row
                const {menus} = row.data();
                const { permissions = null } = row.data();
                if(menus) {
                    row.child(formatDataRow('Menu', menus)).show();
                }

                if(permissions) {
                    row.child(formatDataRow('Permission', permissions)).show();
                }
            }
        });

        $table.on('draw', function() {
            const modalAdd = $.modalCustom({
                trigger: '#tambahRole',
                modal: '#modalAdd',
                options: {
                    title: 'Tambah Role'
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editRole',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Role',
                    bind: 'role'
                }
            });

            const modalSetting = $.modalCustom({
                trigger: '.settingRole',
                modal: '#modalSetting',
                options: {
                    title: 'Setting Permission',
                    bind: 'role'
                }
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-role=${id}]`).spinner();
                $.get('{{ route("admin.roles.forms") }}', {
                    view: 'roles-edit',
                    id_role: id
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
                    $(`[data-bind-role=${id}]`).spinner('hide');
                })
            });

            modalSetting.onShow(function(id) {
                $(`[data-bind-role=${id}]`).spinner();
                $.get('{{ route("admin.roles.forms") }}', {
                    view: 'role-setting',
                    id_role: id
                })
                .done(function(response) {
                    modalSetting.render(response);
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
                    $(`[data-bind-role=${id}]`).spinner('hide');
                })
            });

            modalEdit.onClose(() => {
                // code
            });

            modalAdd.onShow(function() {
                $(`#tambahRole`).spinner('show');
                $.get('{{ route("admin.roles.forms") }}', {
                    view: 'roles-create'
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
                    $(`#tambahRole`).spinner('hide');
                })
            });
        });
    });

    function formatDataRow(title = 'Menu', menus = []) {
        let temmplate = '<dl class="d-flex flex-wrap align-items-center" style="gap: 10px;widt: 100%"> '+'<dt>'+ title +':</dt>';
        if(menus.length > 0) {
            menus.forEach(menu => {
                temmplate += `<span class="badge badge-sm badge-danger">${menu.nama}</span>`;
            });
            temmplate += '</dl>';
        }
        return temmplate;
    }

    function deleteRole(id_role) {
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
                destroyRole(id_role)
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

    function destroyRole(id_role) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.roles.destroy") }}', {
                id_role,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection