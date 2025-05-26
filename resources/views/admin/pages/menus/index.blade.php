@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Menu</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="#" id="tambahMenu" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Menu</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableMenus">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>Module</th>
                      <th>Icon</th>
                      <th>URL</th>
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
        $table = $('#tableMenus').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.menus.data") }}'
            },
            columns: [
                {data: 'nama', name: 'nama', search: true},
                {data: 'module', name: 'module'},
                {data: 'icon', name: 'icon', search: true},
                {data: 'url', name: 'url', search: true},
                {data: 'status', name: 'status', search: true},
                {data: 'action', name: 'action'}
            ]
        });

        $table.on('draw', function() {
            const modalAdd = $.modalCustom({
                trigger: '#tambahMenu',
                modal: '#modalAdd',
                options: {
                    title: 'Tambah Menu'
                }
            });

            const modalSetting = $.modalCustom({
                trigger: '.settingMenu',
                modal: '#modalSetting',
                options: {
                    title: 'Setting Role Menu',
                    bind: 'setting'
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editMenu',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Menu',
                    bind: 'menu'
                }
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-menu=${id}]`).spinner();
                $.get('{{ route("admin.menus.forms") }}', {
                    view: 'menus-edit',
                    id_menu: id
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
                    $(`[data-bind-menu=${id}]`).spinner('hide');
                })
            });

            modalSetting.onShow(function(id) {
                $(`[data-bind-setting=${id}]`).spinner();
                $.get('{{ route("admin.menus.forms") }}', {
                    view: 'menus-setting',
                    id_menu: id
                })
                .done(function(response) {
                    modalSetting.render(response);
                })
                .fail(function(error) {
                    const response = error.responseJSON;
                    modalSetting.render(`<span class="alert my-3 d-block alert-danger">${response.message}</span>`);
                    $.toast({
                        heading: 'Warning',
                        text: response.message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    })
                })
                .always(function() {
                    $(`[data-bind-setting=${id}]`).spinner('hide');
                })
            });

            modalEdit.onClose(() => {
                // code
            });

            modalAdd.onShow(function() {
                $(`#tambahMenu`).spinner('show');
                $.get('{{ route("admin.menus.forms") }}', {
                    view: 'menus-create'
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
                    $(`#tambahMenu`).spinner('hide');
                })
            });
        });
    });

    function deleteMenu(id_menu) {
        Swal.fire({
            title: 'Kamu Yakin?',
            text: 'Apakah kamu yakin ingin menghapus menu ini?',
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
                destroyMenu(id_menu)
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

    function destroyMenu(id_menu) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.menus.destroy") }}', {
                id_menu,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection