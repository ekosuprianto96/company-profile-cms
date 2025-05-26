@extends('frontend.layouts.main', ['title' => $page->title(), 'meta' => $page->meta() ?? []])
@php($sections = $page->sections() ?? [])

@section('content')

    <x-frontend.sections.header-title :forms="(object)['title' => ['value' => $page->title()]]" />

    @if($page->existsSection())
        <x-frontend.templates.container>
            <div class="grid grid-cols-12 gap-4">
                <div class="lg:col-span-12 col-span-12">
                    @foreach(($sections->where('id', 'detail-gallery') ?? []) as $key => $value)
                        {!! $value['view']->with('height_section', '80px') !!}
                    @endforeach
                </div>
            </div>
        </x-frontend.templates.container>
    @endif
@endsection