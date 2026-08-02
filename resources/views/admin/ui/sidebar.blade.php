<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="ri-home-line menu-icon"></i>
        <span class="menu-title">Beranda</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.guide.preview') }}" target="_blank">
        <i class="ri-book-open-line menu-icon"></i>
        <span class="menu-title">Panduan Admin</span>
      </a>
    </li>
    <li class="nav-item nav-category">App</li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#mobile_app_module" aria-expanded="false" aria-controls="mobile_app_module">
        <i class="ri-smartphone-line menu-icon"></i>
        <span class="menu-title">Mobile App</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="mobile_app_module">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.index') }}">Overview</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.users') }}">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.otp_logs') }}">OTP Logs</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.service_requests.index') }}">Service Requests</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.services') }}">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.home_layout') }}">Home Layout</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.notifications') }}">Notifications</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.notification_templates') }}">Template Notifikasi</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile.live_chat') }}">Live Chat</a></li>
        </ul>
      </div>
    </li>
    @if(optional(auth()->user())->isSuperAdmin())
    <li class="nav-item nav-category">Sistem</li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#system_module" aria-expanded="false" aria-controls="system_module">
        <i class="ri-server-line menu-icon"></i>
        <span class="menu-title">Monitoring Sistem</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="system_module">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.system.jobs') }}">Job &amp; Antrean</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.system.schedule') }}">Cron Schedule</a></li>
        </ul>
      </div>
    </li>
    @endif
    @foreach ($groupModules as $key => $modules)
      @php
        $group = '';
      @endphp
      @php
        $ran_key = rand(100, 999);
      @endphp
      @foreach ($modules as $name => $module)
        @if($name === 'unvaible')
          @foreach ($module as $menu)
            <li class="nav-item">
              <a class="nav-link" href="{{ url($menu->url) }}">
                <i class="menu-icon {{ $menu->icon }}"></i>
                <span class="menu-title">{{ $menu->nama }}</span>
              </a>
            </li>
          @endforeach
        @else
          @if($group !== $key)
            <li class="nav-item nav-category">{{ $key }}</li>
          @endif
          @php
            $group = $key;
          @endphp
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#{{ str_replace(' ', '_', strtolower($name.'_'.$key.$ran_key)) }}" aria-expanded="false" aria-controls="{{ str_replace(' ', '_', strtolower($name.'_'.$key.$ran_key)) }}">
              <i class="{{ $module[0]->module->icon }} menu-icon"></i>
              <span class="menu-title">{{ $name }}</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="{{ str_replace(' ', '_', strtolower($name.'_'.$key.$ran_key)) }}">
              <ul class="nav flex-column sub-menu">
                @foreach ($module as $menu)
                  <li class="nav-item"> <a class="nav-link" href="{{ url($menu->url) }}">{{ $menu->nama }}</a></li>
                @endforeach
              </ul>
            </div>
          </li>
        @endif
      @endforeach
    @endforeach
  </ul>
</nav>
