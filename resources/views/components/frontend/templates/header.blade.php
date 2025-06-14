<style>
    .dinamic_hover_text:hover {
        color: {{ config('settings.value.text_hover_menu_header') }} !important;
    }

    .dinamic_color_active_text {
        color: {{ config('settings.value.text_hover_menu_header') }} !important;
    }

    #backdrop_menu_mobile.active {
        transform: translateX(0px) !important;
    }
</style>
<header class="w-full left-0 right-0 h-max overflow-hidden lg:fixed sticky top-0 z-[100]">
    {{-- mobile --}}
    <nav style="background-color: {{ config('settings.value.background_menu_header') }}" class="w-full lg:hidden px-4 py-2 h-full flex justify-between items-center">
        <div id="logo-navbar" class="w-[50%] h-full flex items-center">
            <img class="w-[70px] max-w-full" src="{{ image_url('informasi', config('settings.value.app_logo.file')) }}" alt="">
        </div>
        <div id="menu-humburger" class="hover:cursor-pointer" style="color: {{ config('settings.value.text_menu_header') }}">
            <div class="w-[40px] h-[40px] flex justify-center items-center rounded-lg border-2 border-white">
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

    {{-- menu mobile --}}
    <div id="backdrop_menu_mobile" class="w-screen h-screen bg-black/50 transition-all fixed top-0 left-0 bottom-0 right-0 z-[100] translate-x-[-100%]">
        <div class="h-full w-[80%] p-4 relative" style="background-color: {{ config('settings.value.background_menu_header') }}">
            <div class="mb-3 flex items-center justify-between">
                <a href="{{ route('home') }}" class="">
                    <img class="w-[70px] max-w-full" src="{{ image_url('informasi', config('settings.value.app_logo.file')) }}" alt="">
                </a>
                <button class="text-[25px]" type="button" id="close_menu_mobile" title="Close" style="color: {{ config('settings.value.text_menu_header') }}">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <hr>
            <div class="mt-4">
                <ul class="w-full h-full" style="color: {{ config('settings.value.text_menu_header') }}">
                    <li class="text-sm font-semibold">
                        <a href="{{ route('home') }}" class="transition-all dinamic_hover_text {{ isActiveMenu('home', 'dinamic_color_active_text') }} py-3 block w-full h-full">
                            Home
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('layanan') }}" class="transition-all dinamic_hover_text {{ isActiveMenu('layanan', 'dinamic_color_active_text') }} py-3 block w-full h-full">
                            Layanan
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('tentang_kami') }}" class="transition-all dinamic_hover_text {{ isActiveMenu('tentang_kami', 'dinamic_color_active_text') }} py-3 block w-full h-full">
                            Tentang Kami
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('galeri') }}" class="transition-all dinamic_hover_text {{ isActiveMenu('galeri', 'dinamic_color_active_text') }} py-3 block w-full h-full">
                            Galeri Kami
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('blog') }}" class="transition-all dinamic_hover_text {{ isActiveMenu('blog', 'dinamic_color_active_text') }} py-3 block w-full h-full">
                            Blog
                        </a>
                    </li>
                    <li class="text-sm font-semibold">
                        <a href="{{ route('kontak') }}" class="transition-all dinamic_hover_text {{ isActiveMenu('kontak', 'dinamic_color_active_text') }} py-3 block w-full h-full">
                            Kontak Kami
                        </a>
                    </li>
                </ul>
            </div>
            <div class="absolute text-xs bottom-3" style="color: {{ config('settings.value.text_menu_header') }}">
                <span>&copy; {{ date('Y') }} {{ config('settings.value.app_name') }}</span>
            </div>
        </div>
    </div>
</header>

<script>
    $(document).ready(function() {
        handleMenuMobile();
    });

    function handleMenuMobile() {
        $('#menu-humburger').click(function() {
            $('#backdrop_menu_mobile').addClass('active');
        });

        $('#close_menu_mobile').click(function() {
            $('#backdrop_menu_mobile').removeClass('active');
        });
    }
</script>
