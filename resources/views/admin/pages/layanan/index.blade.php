@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Layanan</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="#" id="tambahLayanan" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Layanan</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableLayanan">
                  <thead>
                    <tr>
                      <th class="text-center">Title</th>
                      <th>Slug</th>
                      <th>Icon</th>
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

    <div class="modal fade" id="modalUpdate" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content>
            </div>
          </div>
        </div>
    </div>
    <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content>
            </div>
          </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
<script>
    $(document).ready(function() {
        $table = $('#tableLayanan').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.services.data") }}'
            },
            columns: [
                {data: 'title', name: 'title', search: true},
                {data: 'slug', name: 'slug', search: true},
                {data: 'icon', name: 'icon'},
                {data: 'created_by', name: 'created_by', search: true},
                {data: 'updated_by', name: 'updated_by', search: true},
                {data: 'status', name: 'status', search: true},
                {data: 'action', name: 'action'}
            ]
        });

        $table.on('draw', function() {
            const modalAdd = $.modalCustom({
                trigger: '#tambahLayanan',
                modal: '#modalAdd',
                options: {
                    title: 'Tambah Layanan',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editService',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Layanan',
                    bind: 'service',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-service=${id}]`).spinner();
                $.get('{{ route("admin.services.forms") }}', {
                    view: 'service-edit',
                    id_service: id
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
                    $(`[data-bind-service=${id}]`).spinner('hide');
                })
            });

            modalAdd.onShow(function() {
                $(`#tambahLayanan`).spinner('show');
                $.get('{{ route("admin.services.forms") }}', {
                    view: 'service-create'
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
                    $(`#tambahLayanan`).spinner('hide');
                })
            });
        });
    });

    function deleteService(id_service) {
        Swal.fire({
            title: 'Kamu Yakin?',
            text: 'Apakah kamu yakin ingin menghapus layanan ini?',
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
                destroyService(id_service)
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

    function destroyService(id_service) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.services.destroy") }}', {
                id_service,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection