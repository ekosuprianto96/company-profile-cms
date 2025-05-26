@extends('frontend.layouts.main', ['title' => $page->title(), 'meta' => $page->meta() ?? []])

@php($sections = $page->sections() ?? [])
@php($header = $page->sections()->where('id', 'header-title')->first() ?? null)

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

    @if(isset($header))
        {!! $header['view']->with('title', $page->title()) !!}
    @endif

    @if($page->existsSection())
        <x-frontend.templates.container>
            @foreach(($sections->whereNotIn('id', ['header-title']) ?? []) as $key => $value)
                {!! $value['view'] !!}
            @endforeach
        </x-frontend.templates.container>
    @endif
@endsection