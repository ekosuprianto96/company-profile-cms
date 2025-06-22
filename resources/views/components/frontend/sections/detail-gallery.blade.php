@props([
    'collection' => [],
    'height_section' => '80px'
])

<style>
    #post_gallery p {
        text-align: justify;
        text-justify: inter-word;
        font-size: 0.9em;
        hyphens: auto;
        -webkit-hyphens: auto;
        -ms-hyphens: auto;
        -moz-hyphens: auto;
    }
</style>

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="content">
        <div class="grid items-start grid-cols-12 gap-6 mt-14">
            <div class="w-full lg:col-span-6 col-span-12">
                <div class="w-full overflow-hidden rounded-lg">
                    @if(!empty($collection->image ?? ''))
                        <img class="object-cover w-full max-h-[700px]" src="{{ image_url('galleries', $collection->image ?? '') }}" alt="{{ $collection->title ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="w-full lg:col-span-6 col-span-12">
                <div class="prose w-full -mx-auto max-w-none text-slate-600 prose-a:text-[var(--primary-color)] prose-headings:text-[var(--primary-color)] prose-p:text-[var(--secondary-color)]" id="post_gallery">
                    <h1>{{ $collection->title ?? '-' }}</h1>
                    {!! $collection->content ?? '-' !!}
                </div>
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>