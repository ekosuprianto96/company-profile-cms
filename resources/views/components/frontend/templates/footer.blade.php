<footer class="w-full mt-6 bottom-0 py-6" style="background-color: {{ config('footer_settings.value.backgroundColor') }};color: {{ config('footer_settings.value.textColor') }}">
    <x-frontend.templates.container>
        <div class="w-full grid lg:grid-cols-4 grid-cols-1 gap-4 min-h-[200px]">
            <div class="col-span-1 h-full">
                <div class="w-full">
                    <img style="width: {{ config('footer_settings.value.logo_width_footer') }}px" src="{{ image_url('informasi', config('settings.value.app_logo_footer.file')) }}" alt="logo">
                </div>
                <div class=" mt-3 pr-3">
                    <p class="text-xs">{{ config('footer_settings.value.tagline') }}</p>
                </div>
            </div>
            <div class="col-span-1 h-full">
                <div class="w-full">
                    <h1 class="font-semibold text-lg">Quick Links</h1>
                </div>
                <div class="text-sm mt-3">
                    <ul class="text-xs">
                        <li class="p-2">
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="p-2">
                            <a href="{{ route('layanan') }}">Layanan</a>
                        </li>
                        <li class="p-2">
                            <a href="{{ route('tentang_kami') }}">Tentang Kami</a>
                        </li>
                        <li class="p-2">
                            <a href="{{ route('galeri') }}">Galeri Kami</a>
                        </li>
                        <li class="p-2">
                            <a href="{{ route('blog') }}">Blog</a>
                        </li>
                        <li class="p-2">
                            <a href="{{ route('kontak') }}">Kontak Kami</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-span-1 h-full">
                <div class="w-full">
                    <h1 class=" font-semibold text-lg">Ikuti Kami</h1>
                </div>
                <div class="text-sm mt-3">
                    <ul class="text-sm">
                        @foreach(config('social_media') as $key => $value)
                            <li class="py-3">
                                <a href="{{ $value['link'] }}" target="{{ $value['action_target'] }}">
                                    <i class="{{ $value['icon'] }}"></i> {{ $value['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-span-1 h-full">
                <div class="w-full">
                    <h1 class=" font-semibold text-lg">Alamat Kami</h1>
                </div>
                <div class=" text-sm mt-3">
                    <div style="width: 100%"><iframe width="100%" height="{{ config('footer_settings.value.map.height') }}" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height={{ config('footer_settings.value.map.height') }}&amp;hl=en&amp;q={{ urlencode(config('footer_settings.value.map.alamat')) }}({{ urlencode(config('settings.value.app_name')) }})&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"><a href="https://www.mapsdirections.info/calcular-la-población-en-un-mapa">Calcular Población en el Mapa</a></iframe></div>
                </div>
            </div>
        </div>
        <div class="w-full text-center mt-6 py-3">
            <spa class="text-xs">&copy; {{ date('Y') }} {{ config('settings.value.app_name') }}</spa>
        </div>
    </x-frontend.templates.container>
</footer>