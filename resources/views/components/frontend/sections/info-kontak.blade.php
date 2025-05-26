@props([
    'collection' => null,
    'forms' => null,
    'height_section' => '80px'
])

<style>
    #kontak__kami p {
        text-align: justify;
        text-justify: inter-word;
        font-size: 1em;
        line-height: 20px;
        hyphens: auto;
        -webkit-hyphens: auto;
        -ms-hyphens: auto;
        -moz-hyphens: auto;
    }
</style>

<x-frontend.templates.m-section
    :top="$height_section"
>
    <x-slot name="content">
        <div class="w-full mt-8">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-6">
                    <div class="w-full overflow-hidden h-[500px] bg-slate-400 rounded-lg">
                        @if(!empty($forms->thumbnail['value'] ?? ''))
                            <img class="object-cover w-full h-full" src="{{ image_url($forms->thumbnail['path'], $forms->thumbnail['value'] ?? '') }}" alt="{{ $forms->title['value'] ?? '' }}">
                        @endif
                    </div>
                </div>
                <div class="col-span-6 text-slate-600" id="kontak__kami">
                    <div class="w-max mb-4">
                        <h1 class="mb-3 text-3xl text-blue-500 font-bold">{{ $forms->title['value'] ?? '-' }}</h1>
                        <div class="h-[8px] w-[50%] bg-blue-500 rounded-full"></div>
                    </div>
                    <div class="prose prose-a:text-blue-500 prose-slate prose-sm">
                        {!! cutTextByWords($forms->content['value'] ?? '-', 70, '...') ?? '-' !!}
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>