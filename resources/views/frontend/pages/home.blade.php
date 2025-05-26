@extends('frontend.layouts.main', ['title' => $page->title(), 'meta' => $page->meta() ?? []])

@php($sections = $page->sections() ?? [])
@php($banner= $sections->where('id', 'hero-banner')->first() ?? null)

@push('script-custom-header')
    @if(count($page->scripts()) > 0)
        @foreach($page->scripts() as $key => $value)
            {!! $value !!}
        @endforeach
    @endif
@endpush

@push('custom-meta')
    @if(count($page->customMeta()) > 0)
        @foreach($page->customMeta() as $key => $value)
            {!! $value !!}
        @endforeach
    @endif
@endpush

@push('styles-custom')
    @if(count($page->styles()) > 0)
        @foreach($page->styles() as $key => $value)
            {!! $value !!}
        @endforeach
    @endif
@endpush

@section('content')
    @if($page->existsSection())

        {!! $banner['view'] ?? '' !!}

        <x-frontend.templates.container>
            @foreach(($sections->whereNotIn('id', ['hero-banner']) ?? []) as $key => $value)
                {!! $value['view'] !!}
            @endforeach
        </x-frontend.templates.container>
    @endif

@endsection