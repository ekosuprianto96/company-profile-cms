@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex w-100 justify-content-between">
                                    <h4 class="card-title">Kirim Pesan Email Ke Beberapa Pengguna</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex w-100 align-items-center justify-content-end w-50" style="gap: 10px">
                                    <a href="{{ url()->previous() ?? route('admin.email.index') }}" id="create__message" class="btn btn-sm btn-danger"><i class="ri-arrow-left-line me-2"></i> Kembali</a>
                                    <a href="javascript:void(0)" id="add__contact" class="btn btn-sm btn-primary"><i class="ri-add-line me-2"></i> Tambah Kontak</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <form class="row" method="POST" action="{{ route('admin.email.send_bulk_message') }}">
                            @csrf
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title">To Email <span class="text-danger" style="font-size: 0.8em">(Pilih beberapa contact)</span></label>
                                    <select name="emails[]" id="emails" class="form-control @error('emails') is-invalid @enderror" style="width: 100%" multiple="multiple">
                                        <option value="">-- Pilih Email --</option>
                                        @foreach (\App\Models\CustomerContact::latest()->get() as $user)
                                            <option value="{{ $user->email }}">{{ $user->email }}</option>
                                        @endforeach
                                    </select>

                                    @error('emails')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From Email : </label>
                                    <input type="text" class="form-control" readonly value="{{ config('mail.from.address') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subject : </label>
                                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror">
                                    @error('subject')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title">Message : </label>
                                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" id="message" placeholder="Masukkan pesan disini..." style="height: 200px"></textarea>
                                    @error('message')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary"><i class="ri-mail-send-line me-2"></i> Kirim</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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

<script>

    $(document).ready(function() {
        $('#emails').select2({
            width: '100%',
            placeholder: '-- Pilih Kontak --'
        });

        $(document).on('contact-created', function(e, { data = {} }) {
            addOption({...data});
        });

        const modalAdd = $.modalCustom({
            trigger: '#add__contact',
            modal: '#modalAdd',
            options: {
                title: 'Tambah Kontak',
                backdrop: 'static',
                keyboard: false,
                focus: false,
                show: false
            }
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

        function addOption(data) {
            var option = new Option(data.text, data.id, true, true);
            $('#emails').append(option).trigger('change');
        }
    })
</script>
@endsection