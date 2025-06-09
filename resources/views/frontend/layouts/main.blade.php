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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    
    <script src="{{ assetFrontend('js', 'jQuery.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .prose .page-break {
            @apply my-8 border-t border-gray-300;
            /* Untuk cetak, pakai page-break juga */
            page-break-after: always;
            display: block;
            height: 1px;
        }
    </style>
</head>
<body style="position: relative" class="w-full scroll-smooth bg-gradient-to-b from-slate-50 to-slate-200 overflow-x-hidden">
    @include('sweetalert::alert')
    {{-- header --}}
    <x-frontend.templates.header />

    {{-- content --}}
    @yield('content')

    {{-- footer --}}
    <x-frontend.templates.footer />
</body>
</html>