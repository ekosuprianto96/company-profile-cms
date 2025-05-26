<form action="{{ route('admin.pengguna.update', $pengguna->id ?? '-') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-5">Form Edit Pengguna</h4>
                    <div class="row">
                        <div class="col-md-9 mb-3 mb-lg-0">
                            <div class="form-group row">
                                <label for="nama" class="col-sm-3 col-form-label">Nama Lengkap</label>
                                <div class="col-sm-9">
                                  <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ $pengguna->account->nama_lengkap }}" id="nama" placeholder="Nama lengkap">
                                  @error('nama')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="tgl_lahir" class="col-sm-3 col-form-label">Tanggal Lahir</label>
                                <div class="col-sm-9">
                                  <input type="date" value="{{ $pengguna->account->tanggal_lahir }}" max="{{ \Carbon\carbon::now()->addYear(-17)->format('Y-m-d') }}" name="tgl_lahir" class="form-control @error('tgl_lahir') is-invalid @enderror" id="tgl_lahir" placeholder="Tanggal lahir">
                                  @error('tgl_lahir')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-sm-3 col-form-label">Email</label>
                                <div class="col-sm-9">
                                  <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ $pengguna->email }}" name="email" id="email" placeholder="Email">
                                  @error('email')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-sm-3 col-form-label">Password</label>
                                <div class="col-sm-9">
                                    <span class="text-danger d-block mb-2" style="font-size: 0.8em">*Kosongkan jika tidak ingin merubah password</span>
                                  <input autofocus="false" autocomplete="new-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" placeholder="Password">
                                  @error('password')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="no_telp" class="col-sm-3 col-form-label">No Telp</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control @error('no_telp') is-invalid @enderror" value="{{ $pengguna->account->no_telpon }}" name="no_telp" id="no_telp" placeholder="No Telpon">
                                  @error('no_telp')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="no_ktp" class="col-sm-3 col-form-label">No KTP</label>
                                <div class="col-sm-9">
                                  <input type="text" name="no_ktp" class="form-control @error('no_ktp') is-invalid @enderror" value="{{ $pengguna->account->no_ktp }}" id="no_ktp" placeholder="No KTP">
                                  @error('no_ktp')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="no_nip" class="col-sm-3 col-form-label">No NIP</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control @error('no_nip') is-invalid @enderror" value="{{ $pengguna->account->no_nip }}" id="no_nip" name="no_nip" placeholder="No NIP (nomor induk pegawai)">
                                  @error('no_nip')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="no_ktp" class="col-sm-3 col-form-label">Alamat</label>
                                <div class="col-sm-9">
                                  <textarea style="height: 150px" class="form-control @error('alamat') is-invalid @enderror" name="alamat" id="alamat" placeholder="Alamat">{{ $pengguna->account->alamat }}</textarea>
                                  @error('alamat')
                                      <div class="invalid-feedback">
                                          <span class="text-danger">{{ $message }}</span>
                                      </div>
                                  @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="id_role" class="col-sm-3 col-form-label">Role Pengguna</label>
                                <div class="col-sm-9">
                                    <select name="id_role" id="id_role" class="form-control @error('id_role') is-invalid @enderror">
                                        <option value="">-- Pilih Role Akses --</option>
                                        @foreach (App\Models\Role::where('an', 1)->get() as $role)
                                            <option {{ $role->id_role == $pengguna->id_role ? 'selected' : '' }} value="{{ $role->id_role }}">{{ $role->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('id_role')
                                        <div class="invalid-feedback">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 mb-lg-0">
                            <div class="border d-flex justify-content-center align-items-center w-100" style="height: 300px">

                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-end align-items-center" style="gap: 10px">
                                <a href="{{ route('admin.pengguna.index') }}" class="btn btn-danger">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>