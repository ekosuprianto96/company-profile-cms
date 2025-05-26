@php
    $user = Auth::user();
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="index.html">
                <img src="{{ image_url('informasi', config('settings.value.app_logo.file')) }}" alt="logo" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="index.html">
                <img src="{{ image_url('informasi', config('settings.value.app_logo.file')) }}" alt="logo" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text" style="font-size: 1.6em">{{ greetingUser() }} <span class="text-black ms-2 fw-bold">{{ $user->name }}</span></h1>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" title="View Website" href="{{ url('/') }}" target="_blank">
                    <button class="btn- btn-outline-light rounded">
                        <i class="ri-global-line text-black me-2"></i>
                        <span class="text-black">Lihat Website</span>
                    </button>
                </a>
            </li>
            <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                <a href="{{ route('admin.profile.index') }}" class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(!empty($user->account->image ?? ''))
                        <img class="img-xs rounded-circle" src="{{ image_url('avatars', $user->account->image) }}" alt="Profile image"> 
                    @else
                        <img class="img-xs rounded-circle" src="{{ asset('assets/admin/assets/images/faces/face8.jpg') }}" alt="Profile image">
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        @if(!empty($user->account->image ?? ''))
                            <img class="img-xs rounded-circle" src="{{ image_url('avatars', $user->account->image) }}" alt="Profile image"> 
                        @else
                            <img class="img-xs rounded-circle" src="{{ asset('assets/admin/assets/images/faces/face8.jpg') }}" alt="Profile image">
                        @endif
                        <div class="d-flex w-100 justify-content-center align-items-center flex-column">
                            <p title="{{ $user->account?->nama_lengkap ?? '...' }}" class="mb-1 mt-3 fw-semibold text-truncate" style="max-width: 70%">{{ $user->account?->nama_lengkap ?? '...' }}</p>
                            <p title="{{ $user->email ?? '...' }}" class="fw-light text-muted mb-0 text-truncate" style="max-width: 70%">{{ $user->email ?? '...' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.profile.index') }}" class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile</a>
            
                    <form action="{{ route('admin.auth.logout') }}" method="POST" class="d-block m-0">
                        @csrf
                        <button type="submit" class="dropdown-item w-100 text-start">
                            <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i> Sign Out
                        </button>
                    </form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>