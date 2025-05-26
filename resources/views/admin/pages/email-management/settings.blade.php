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
                                    <h4 class="card-title">Pengaturan Email</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <form class="row" method="POST" action="{{ route('admin.email.settings_email_update') }}">
                            @csrf
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Default : <span class="text-danger" style="font-size: 0.8em">(Jangan mengubah email jika tidak ada konfigurasi email yang valid)</span></label>
                                    <select name="from_email" id="from_email" class="form-control @error('from_email') is-invalid @enderror" style="width: 100%">
                                        <option value="">-- Pilih Email --</option>
                                        @php
                                            $informasi = \App\Models\Informasi::where('key', 'kontak')->first();
                                            $decodeInformasi = json_decode($informasi->value ?? '{}');
                                            $emails = $decodeInformasi->emails ?? [];
                                        @endphp

                                        @foreach ($emails as $mail)
                                            <option {{ $mail == config('mail.from.address') ? 'selected' : '' }} value="{{ $mail }}">{{ $mail }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_email')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From Name : <span class="text-danger" style="font-size: 0.8em">(Ini sudah otomatis mengambil dari informasi umum aplikasi, klik <a href="{{ route('admin.informasi.index') }}">disini</a> untuk mengubah)</span></label>
                                    <input type="text" readonly class="form-control" value="{{ config('mail.from.name') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Host : </label>
                                    <input type="text" name="host" class="form-control @error('host') is-invalid @enderror" value="{{ config('mail.mailers.smtp.host') }}">
                                    @error('host')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Port : </label>
                                    <input type="number" name="port" class="form-control @error('port') is-invalid @enderror" value="{{ config('mail.mailers.smtp.port') }}">
                                    @error('port')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Username : </label>
                                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ config('mail.mailers.smtp.username') }}">
                                    @error('username')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password : </label>
                                    <input type="text" name="password" class="form-control @error('password') is-invalid @enderror" value="{{ config('mail.mailers.smtp.password') }}">
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-2"></i> Simpan</button>
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
            placeholder: '-- Pilih Email --'
        });
    });
</script>
@endsection