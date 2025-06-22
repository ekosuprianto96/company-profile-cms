@props([
    'collection' => null,
    'forms' => null,
    'height_section' => '80px'
])

<style>
    
</style>

<x-frontend.templates.m-section
    :top="$height_section"
>
    <x-slot name="content">
        <div class="w-full mt-8">
            <div class="grid grid-cols-12 gap-6">
                <div class="lg:col-span-6 col-span-12">
                    <div class="w-full overflow-hidden h-[500px] rounded-lg">
                        @if(!empty($forms->thumbnail['value'] ?? ''))
                            <img class="object-cover w-full h-full" src="{{ image_url($forms->thumbnail['path'], $forms->thumbnail['value'] ?? '') }}" alt="{{ $forms->title['value'] ?? '' }}">
                        @endif
                    </div>
                </div>
                <div class="lg:col-span-6 col-span-12 text-[var(--secondary-color)]" id="kontak__kami">
                    <div class="w-max mb-4">
                        <h1 class="mb-3 lg:text-3xl text-xl text-[var(--primary-color)] font-bold">{{ $forms->title['value'] ?? '-' }}</h1>
                        <div class="h-[8px] w-[50%] bg-[var(--primary-color)] rounded-full"></div>
                    </div>
                    <div class="prose prose-a:text-[var(--primary-color)] prose-p:text-[var(--secondary-color)] prose-headings:text-[var(--primary-color)]">
                        {!! cutTextByWords($forms->content['value'] ?? '-', 70, '...') ?? '-' !!}
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>