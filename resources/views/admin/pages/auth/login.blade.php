@extends('admin.layouts.auth')

@section('content')
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo">
                <img src="{{ asset('assets/admin/assets/images/logo.svg') }}" alt="logo">
              </div>
              <h4>Login Akun Admin</h4>
              <div class="row">
                <div class="col-md-12 my-3">
                    @if($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $error }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endforeach
                    @endif
                </div>
              </div>
              <form class="pt-3" action="{{ route('admin.auth.check') }}" method="POST">
                @csrf
                <div class="form-group">
                    <input 
                        type="email" 
                        value="{{ old('email') }}" 
                        name="email"
                        class="form-control @error('email') is-invalid @enderror form-control-lg" 
                        id="exampleInputEmail1" 
                        placeholder="Email..."
                        required
                    />
                    @error('email')
                        <div class="invalid-fedback mt-2">
                            <span class="text-danger">{{ $message }}</span>
                        </div>
                    @enderror
                </div>
                <div class="form-group">
                    <input 
                        name="password"
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror form-control-lg" 
                        id="exampleInputPassword1" 
                        placeholder="Password...."
                        required
                    />
                    @error('password')
                        <div class="invalid-fedback mt-2">
                            <span class="text-danger">{{ $message }}</span>
                        </div>
                    @enderror
                </div>
                <div class="mt-3 d-grid gap-2">
                  <button type="submit" class="btn btn-block btn-primary btn-lg fw-medium auth-form-btn">SIGN IN</button>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <div class="form-check">
                    <label class="form-check-label text-muted">
                      <input type="checkbox" class="form-check-input"> Remember me </label>
                  </div>
                  <a href="#" class="auth-link text-black">Forgot password?</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
@endsection