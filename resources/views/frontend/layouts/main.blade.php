<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="title" content="{{ $meta->title ?? '-' }}">
    <meta name="description" content="{{ $meta->description ?? '-' }}">
    <meta name="keywords" content="{{ $meta->keywords ?? '-' }}">
    <meta name="image" content="{{ $meta->image ?? '-' }}">
    <meta name="og:image" content="{{ $meta->image ?? '-' }}">

    {{-- favicon --}}
    <link rel="icon" href="{{ image_url('informasi', config('settings.value.favicon.file')) }}" type="image/x-icon">

    {!! SEOMeta::generate(true) !!}
    {!! OpenGraph::generate(true) !!}
    {!! Twitter::generate(true) !!}
    {!! JsonLd::generate(true) !!}

    @stack('script-custom-header')
    @stack('meta')
    @stack('styles-custom')
    @stack('custom-meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/all.min.css') }}"/>
    
    <script src="{{ assetFrontend('js', 'jQuery.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/slick-caraousel/slick.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/slick-caraousel/slick-theme.css') }}"/>
    <script type="text/javascript" src="{{ asset('assets/frontend/slick-caraousel/slick.min.js') }}"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .slick-next.slick-arrow::before {
            color: var(--primary-color) !important;
        }

        .slick-prev.slick-arrow::before {
            color: var(--primary-color) !important;
        }

        .prose .page-break {
            @apply my-8 border-t border-gray-300;
            /* Untuk cetak, pakai page-break juga */
            page-break-after: always;
            display: block;
            height: 1px;
        }

        .slick-slide {
            display: block !important;
        }
    </style>
    <x-frontend.templates.main-style />
</head>
<body style="position: relative" class="w-full scroll-smooth dinamic_main_background overflow-x-hidden">
    @include('sweetalert::alert')
    {{-- header --}}
    <x-frontend.templates.header />

    {{-- content --}}
    @yield('content')

    {{-- footer --}}
    <x-frontend.templates.footer />
</body>
</html>