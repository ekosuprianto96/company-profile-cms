@extends('admin.layouts.main')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title  card-title-dash">Daftar Informasi</h4>
                    <div>
                        <div class="card card-rounded">
                            <div class="card-body card-rounded">
                                @foreach($informasi as $key => $value)
                                    <div class="list position-relative mb-4 align-items-center border-bottom py-2">
                                        <div class="wrapper w-100">
                                            <p class="mb-2 fw-medium">{{ $value->name }} </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-calendar text-muted me-1"></i>
                                                    <p class="mb-0 text-small text-muted">Terakhir Diupdate : {{ \Carbon\carbon::parse($value->updated_at, 'Asia/Jakarta')->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="right: 10px;top: 0" class="position-absolute d-flex align-items-center justify-content-end">
                                            <div class="d-flex align-items-center" style="font-size: 0.8em">
                                                @if($value->type === 'modal')
                                                    <a 
                                                        href="javascript:void(0)" 
                                                        class="btn btn__edit btn-sm btn-success"
                                                        data-bind-informasi="{{ $value->id }}"
                                                        data-bind-key="{{ $value->key }}"
                                                    >
                                                        <i class="ri-file-edit-line me-2"></i> Edit
                                                    </a>
                                                @elseif($value->type === 'redirect')
                                                    <a 
                                                        href="{{ url($value->action) }}" 
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        <i class="ri-file-edit-line me-2"></i> Edit
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUpdate" tabindex="-1">
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

    <script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/ckeditor5-classic-free-full-feature@35.4.1/build/ckeditor.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            const modalEdit = $.modalCustom({
                trigger: '.btn__edit',
                modal: '#modalUpdate',
                options: {
                    title: 'Edit Informasi',
                    bind: 'informasi',
                    focus: false,
                    show: false
                }
            });

            modalEdit.onShow(function(id) {
                $(`[data-bind-informasi=${id}]`).spinner();
                $.get('{{ route("admin.informasi.forms") }}', {
                    view: 'informasi-edit',
                    id,
                    key: $(`[data-bind-informasi=${id}]`).data('bind-key')
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
                    $(`[data-bind-informasi=${id}]`).spinner('hide');
                })
            });
        })
    </script>
@endsection
