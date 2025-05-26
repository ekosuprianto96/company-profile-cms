<style>
    .dinamic_hover_text:hover {
        color: {{ config('settings.value.text_hover_menu_header') }} !important;
    }

    .dinamic_color_active_text {
        color: {{ config('settings.value.text_hover_menu_header') }} !important;
    }
</style>
<header class="w-full left-0 right-0 h-max overflow-hidden lg:fixed sticky top-0 z-[100]">
    {{-- mobile --}}
    <nav style="background-color: {{ config('settings.value.background_menu_header') }}" class="w-full lg:hidden px-4 py-2 h-full flex justify-between items-center">
        <div id="logo-navbar" class="w-[50%] h-full flex items-center">
            <img class="w-[60px] max-w-full" src="{{ image_url('informasi', config('settings.value.app_logo.file')) }}" alt="">
        </div>
        <div id="menu-humburger" class="" style="color: {{ config('settings.value.text_menu_header') }}">
            <div class="w-[40px] h-[40px] flex justify-center items-center rounded-lg border-2 border-slate-500">
                <i class="ri-menu-line text-[20px]"></i>
            </div>
        </div>
    </nav>

    {{-- desktop --}}
    <x-frontend.templates.container>
        <nav style="background-color: {{ config('settings.value.background_menu_header') }}" class="w-full lg:flex shadow-lg my-4 px-8 rounded-xl py-3 h-[80px] hidden justify-between items-center">
            <div id="logo-navbar" class="h-full overflow-hidden flex items-center">
                <img 
                    class="h-auto max-h-[80px] min-h-[80px] w-auto max-w-full"
                    src="{{ image_url('informasi', config('settings.value.app_logo.file')) }}"
                    alt="Logo"
                >
            </div>
            <div id="menu-desktop" class="w-[70%] h-full">
                <ul class="w-full h-full flex justify-end items-center" style="color: {{ config('settings.value.text_menu_header') }}">
                    <li class="text-sm font-semibold">
                        <a href="{{ route('home') }}" class="px-3 transition-all dinamic_hover_text {{ isActiveMenu('home', 'dinamic_color_active_text') }} py-2 w-full h-full">
                            Home
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('layanan') }}" class="px-3 transition-all dinamic_hover_text {{ isActiveMenu('layanan', 'dinamic_color_active_text') }} py-2 w-full h-full">
                            Layanan
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('tentang_kami') }}" class="px-3 transition-all dinamic_hover_text {{ isActiveMenu('tentang_kami', 'dinamic_color_active_text') }} py-2 w-full h-full">
                            Tentang Kami
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('galeri') }}" class="px-3 transition-all dinamic_hover_text {{ isActiveMenu('galeri', 'dinamic_color_active_text') }} py-2 w-full h-full">
                            Galeri Kami
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('blog') }}" class="px-3 transition-all dinamic_hover_text {{ isActiveMenu('blog', 'dinamic_color_active_text') }} py-2 w-full h-full">
                            Blog
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('kontak') }}" class="px-3 transition-all dinamic_hover_text {{ isActiveMenu('kontak', 'dinamic_color_active_text') }} py-2 w-full h-full">
                            Kontak Kami
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </x-frontend.templates.container>
</header>
