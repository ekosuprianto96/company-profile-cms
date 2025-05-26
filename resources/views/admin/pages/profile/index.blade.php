@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-2">
                    <div class="w-100">
                        <div class="border overflow-hidden rounded d-flex justify-contetn-center align-items-center" style="width: 100%;height: max-content">
                            @if(!empty($pengguna->account->image ?? ''))
                                <img class="w-100" src="{{ image_url('avatars', $pengguna->account->image ?? '') }}" alt="Avatar" id="avatar">
                            @else 
                                <img src="{{ asset('assets/admin/assets/images/faces/face8.jpg') }}" class="w-100" alt="Avatar" id="avatar">
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 w-100">
                        <div class="position-relative w-100">
                            <input type="file" id="input_image" name="image" accept=".jpg,.jpeg,.png,.svg,.webp" class="position-absolute" style="opacity: 0;top: 0;right: 0;bottom: 0;left: 0;cursor: pointer;">
                            <button class="btn btn-primary w-100"><i class="ri-file-image-line"></i> Upload Gambar</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    <form action="{{ route('admin.profile.update', ['id' => $pengguna->id]) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="" class="form-label">Nama</label>
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ $pengguna->account->nama_lengkap }}">
                                    @error('nama')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="" class="form-label">No Telpon</label>
                                    <input type="text" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror" value="{{ $pengguna->account->no_telpon }}">
                                    @error('no_telp')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="" class="form-label">No NIP (No Induk Pegawai)</label>
                                    <input type="text" name="no_nip" class="form-control @error('no_nip') is-invalid @enderror" value="{{ $pengguna->account->no_nip }}">
                                    @error('no_nip')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="" class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $pengguna->email }}">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="" class="form-label">No KTP</label>
                                    <input type="text" name="no_ktp" class="form-control @error('no_ktp') is-invalid @enderror" value="{{ $pengguna->account->no_ktp }}">
                                    @error('no_ktp')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="" class="form-label">Tgl Lahir</label>
                                    <input type="date" name="tgl_lahir" class="form-control @error('tgl_lahir') is-invalid @enderror" value="{{ $pengguna->account->tanggal_lahir }}">
                                    @error('tgl_lahir')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="" class="form-label">Password</label>
                                    <span style="font-size: 0.8em" class="text-danger d-block mb-2">Kosongkan jika tidak ingin ubah password</span>
                                    <input type="password" autocomplete="new-password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password">
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="" class="form-label">Alamat</label>
                                    <textarea name="alamat" style="height: 100px" class="form-control @error('alamat') is-invalid @enderror">{{ $pengguna->account->alamat }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12 text-end">
                                <button class="btn btn-success"><i class="ri-save-line"></i> Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function($) {
        'use strict';

        $(document).ready(function() {
            $('#input_image').change(function(e) {
                const file = e.target.files[0];
                if(file) {
                    const urlPreview = URL.createObjectURL(file);
                    $('#avatar').attr('src', urlPreview);

                    const data = new FormData();
                    data.append('_token', '{{ csrf_token() }}');
                    data.append('avatar', file);
                    data.append('id', '{{ $pengguna->id }}');

                    uploadAvatar(data)
                    .then(function(response) {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Avatar berhasil diupload.',
                            showHideTransition: 'slide',
                            position: 'top-right',
                            icon: 'success'
                        });
                    })
                    .catch(function(error) {
                        $.toast({
                            heading: 'Gagal',
                            text: 'Avatar gagal diupload.',
                            showHideTransition: 'slide',
                            icon: 'error'
                        });
                    });
                }

                return;
            });
        })

        function uploadAvatar(data = {}) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '{{ route("admin.profile.upload-avatar") }}',
                    type: 'POST',
                    async: true,
                    data: data,
                    contentType: false,
                    processData: false
                }).done(resolve)
                .fail(reject);
            });
        }
    })(jQuery);
</script>
@endsection