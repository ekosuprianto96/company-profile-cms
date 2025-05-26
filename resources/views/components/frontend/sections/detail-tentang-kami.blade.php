@props([
    'collection' => [],
    'height_section' => '80px'
])

<style>
    #tentang__kami p {
        text-align: justify;
        text-justify: inter-word;
        font-size: 0.9em;
        hyphens: auto;
        -webkit-hyphens: auto;
        -ms-hyphens: auto;
        -moz-hyphens: auto;
        margin-bottom: 1.5rem;
    }
</style>

@php($collection = $collection->where('key', 'tentang-kami')->first())
@php($informasi = json_decode($collection->value ?? '{}'))

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="content">
        <div class="w-full lg:mt-8 mt-4 flex justify-center flex-col lg:items-end items-center lg:text-end text-center">
            <h1 class="lg:text-3xl text-xl mb-4 lg:w-[50%] w-[80%] font-bold text-blue-500">{{ $informasi->title ?? '-' }}</h1>
            <div class="h-[8px] w-[30%] bg-blue-500 rounded-full"></div>
        </div>
        <div class="grid items-start grid-cols-12 lg:gap-6 lg:mt-14 mt-10">
            <div class="w-full {{ ($informasi->style ?? 'horizonntal-column') == 'vertical-column' ? 'lg:col-span-5 col-span-12' : 'col-span-12'}}">
                <div class="w-full h-[400px] overflow-hidden rounded-lg">
                    @if(!empty($informasi->thumbnail->file ?? ''))
                        <img class="object-cover w-full h-full" src="{{ image_url('informasi', $informasi->thumbnail->file ?? '') }}" alt="{{ $informasi->title ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="w-full {{ ($informasi->style ?? 'horizonntal-column') == 'vertical-column' ? 'lg:col-span-7 col-span-12' : 'col-span-12'}}">
                <div class="prose w-full -mx-auto mt-4 lg:mt-0 max-w-none text-slate-600 prose-headings:text-blue-500 prose-p:text-slate-600" id="tentang__kami">
                    {!! $informasi->content ?? '-' !!}
                </div>
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>