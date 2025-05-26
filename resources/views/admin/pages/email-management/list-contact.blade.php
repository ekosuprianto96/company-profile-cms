@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                    <div class="d-flex w-100 justify-content-between">
                        <h4 class="card-title">Daftar Kontak Customer</h4>
                        <div class="d-flex align-items-center justify-content-end w-50" style="gap: 10px">
                            <a href="{{ route('admin.email.contact.export') }}" class="btn btn-sm btn-success"><i class="ri-file-excel-2-line me-2"></i> Export Contact</a>
                            <a href="javascript:void(0)" id="import__contact" class="btn btn-sm btn-warning"><i class="ri-download-line me-2"></i> Import Contact</a>
                            <a href="{{ route('admin.email.create_message') }}" id="add__contact" class="btn btn-sm btn-primary"><i class="ri-add-line me-2"></i> Tambah Kontak</a>
                        </div>
                    </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table w-100" id="tableContact">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Alamat</th>
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
              <button type="button" data-bs-dismiss="modal" class="btn-close" aria-label="Close"></button>
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
              <button type="button" data-bs-dismiss="modal" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content>
            </div>
          </div>
        </div>
    </div>
    <div class="modal fade" id="modalImport" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" data-bs-dismiss="modal" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content>
            </div>
          </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $table = $('#tableContact').DataTable({
            processing: true,
            pageLength: 50,
            serverSide: true,
            paginate: true,
            ajax: {
                method: 'get',
                url: '{{ route("admin.email.contact.data") }}'
            },
            columns: [
                {data: 'name', name: 'name', search: true},
                {data: 'email', name: 'email', search: true},
                {data: 'phone', name: 'phone'},
                {data: 'address', name: 'address'},
                {data: 'action', name: 'action'}
            ]
        });

        $('#export__contact').on('click', function() {
            exportContact();
        });

        $(document).on('contact-created', function(e, data) {
            $table.ajax.reload();
        });

        $table.on('draw', function() {
            const modalImport = $.modalCustom({
                trigger: '#import__contact',
                modal: '#modalImport',
                options: {
                    title: 'Import Contact',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            const modalAdd = $.modalCustom({
                trigger: '#add__contact',
                modal: '#modalAdd',
                options: {
                    title: 'Tambah Contact',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            const modalEdit = $.modalCustom({
                trigger: '.editContact',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Contact',
                    bind: 'contact',
                    backdrop: 'static',
                    keyboard: false,
                    focus: false,
                    show: false
                }
            });

            modalImport.onShow(function() {
                $(`#import__contact`).spinner('show');
                $.get('{{ route("admin.email.contact.forms") }}', {
                    view: 'contact-import'
                })
                .done(function(response) {
                    setTimeout(() => {
                        modalImport.render(response);
                    }, 1000);
                })
                .fail(function(error) {
                    const response = error.responseJSON;
                    modalImport.render(`<span class="alert my-3 d-block alert-danger">${response.message}</span>`);
                    $.toast({
                        heading: 'Warning',
                        text: response.message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                })
                .always(function() {
                    $(`#import__contact`).spinner('hide');
                });
            });

            modalAdd.onShow(function() {
                $(`#add__contact`).spinner('show');
                $.get('{{ route("admin.email.contact.forms") }}', {
                    view: 'contact-create'
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
                    $(`#add__contact`).spinner('hide');
                })
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-contact=${id}]`).spinner();
                $.get('{{ route("admin.email.contact.forms") }}', {
                    view: 'contact-edit',
                    contact_id: id
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
                    $(`[data-bind-contact=${id}]`).spinner('hide');
                });
            });
        });
    });

    function exportContact() {
        $.get('{{ route("admin.email.contact.export") }}')
        .done(function(response) {
            window.location.href = response;
        })
        .fail(function(error) {
            const response = error.responseJSON;
            $.toast({
                heading: 'Warning',
                text: response.message,
                showHideTransition: 'slide',
                position: 'top-right',
                icon: 'warning'
            })
        });
    }

    function deleteContact(id) {
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
                destroyContact(id)
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

    function destroyContact(id) {
        return new Promise((resolve, reject) => {
            $.post('{{ route("admin.email.contact.destroy") }}', {
                id,
                _token: '{{ csrf_token() }}'
            })
            .done(resolve)
            .fail(reject)
        })
    }
</script>
@endsection