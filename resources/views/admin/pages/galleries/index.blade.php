@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Galeri</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="#" id="tambahGambar" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Tambah Galeri</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableGaleri">
                  <thead>
                    <tr>
                      <th class="text-center">Image</th>
                      <th>Title</th>
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
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close-edit btn-close" aria-label="Close"></button>
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
              <button type="button" class="btn-close-create btn-close" aria-label="Close"></button>
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
        $table = $('#tableGaleri').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.galleries.data") }}'
            },
            columns: [
                {data: 'image', name: 'image'},
                {data: 'title', name: 'title'},
                {data: 'slug', name: 'slug', search: true},
                {data: 'created_by', name: 'created_by', search: true},
                {data: 'updated_by', name: 'updated_by', search: true},
                {data: 'status', name: 'status', search: true},
                {data: 'action', name: 'action'}
            ]
        });

        $table.on('draw', function() {
            const modalAdd = $.modalCustom({
                trigger: '#tambahGambar',
                modal: '#modalAdd',
                options: {
                    title: 'Tambah Galeri',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editGaleri',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Galeri',
                    bind: 'galeri',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-galeri=${id}]`).spinner();
                $.get('{{ route("admin.galleries.forms") }}', {
                    view: 'gallery-edit',
                    id_galeri: id
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
                    $(`[data-bind-galeri=${id}]`).spinner('hide');
                })
            });

            $('.btn-close-edit').off('click').click(function(index, value) {
                const checkFilename = $('[name=path_file_input_edit_image_upload]').val();
                if(!checkFilename) {
                    $.toast({
                        heading: 'Warning',
                        text: 'Gambar belum di upload, silahkan upload gambar terlebih dahulu.',
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                }else {
                    modalEdit.close();
                }
            });

            $('.btn-close-create').on('click', function() {
                const checkFilename = $('[name=file_name_input_image_upload]').val();
                if(checkFilename) {
                    Swal.fire({
                        title: 'Kamu Yakin?',
                        text: 'Data gambar belum disimpan, apa yakin ingin menutup modal ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Tutup',
                        cancelButtonText: 'Tidak',
                        customClass: {
                            cancelButton: 'bg-danger',
                            confirmButton: 'bg-primary'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteImage()
                            .then(response => {
                                modalAdd.close();
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
                    })
                }else {
                    modalAdd.close();
                }
            });

            modalAdd.onShow(function() {
                $(`#tambahGambar`).spinner('show');
                $.get('{{ route("admin.galleries.forms") }}', {
                    view: 'gallery-create'
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
                    $(`#tambahGambar`).spinner('hide');
                })
            });
        });
    });

    function deleteGallery(id_galeri) {
        Swal.fire({
            title: 'Kamu Yakin?',
            text: 'Apakah kamu yakin ingin menghapus galeri ini?',
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
                destroyGallery(id_galeri)
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

    function destroyGallery(id_gallery) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.galleries.destroy") }}', {
                id_gallery,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection