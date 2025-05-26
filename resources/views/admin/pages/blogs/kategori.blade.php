@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Kategori Blog</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="#" id="tambahKategori" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Kategori</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableKategori">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Slug</th>
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
        <div class="modal-dialog">
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
        <div class="modal-dialog">
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

<script>
    $(document).ready(function() {
        $table = $('#tableKategori').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.blogs.kategori.data") }}'
            },
            columns: [
                {data: 'name', name: 'name', search: true},
                {data: 'slug', name: 'slug', search: true},
                {data: 'created_by', name: 'created_by', search: true},
                {data: 'updated_by', name: 'updated_by', search: true},
                {data: 'status', name: 'status', search: true},
                {data: 'action', name: 'action'}
            ]
        });

        $table.on('draw', function() {
            const modalAdd = $.modalCustom({
                trigger: '#tambahKategori',
                modal: '#modalAdd',
                options: {
                    title: 'Tambah Kategori',
                    backdrop: 'static',
                    keyboard: false
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editKategori',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Kategori',
                    bind: 'kategori',
                    backdrop: 'static',
                    keyboard: false
                }
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-kategori=${id}]`).spinner();
                $.get('{{ route("admin.blogs.kategori.forms") }}', {
                    view: 'kategori-blog-edit',
                    id_kategori: id
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
                    $(`[data-bind-kategori=${id}]`).spinner('hide');
                })
            });

            modalAdd.onShow(function() {
                $(`#tambahKategori`).spinner('show');
                $.get('{{ route("admin.blogs.kategori.forms") }}', {
                    view: 'kategori-blog-create'
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
                    $(`#tambahKategori`).spinner('hide');
                })
            });
        });
    });

    function deleteKategori(id_kategori) {
        Swal.fire({
            title: 'Kamu Yakin?',
            text: 'Apakah kamu yakin ingin menghapus kategori ini?',
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
                destroyKategori(id_kategori)
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

    function destroyKategori(id_kategori) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.blogs.kategori.destroy") }}', {
                id_kategori,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection