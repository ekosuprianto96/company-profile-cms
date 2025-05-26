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
            <div class="w-full col-span-12">
                <div class="h-max overflow-hidden rounded-lg">
                    @if(!empty($collection->image ?? ''))
                        <img class="object-cover w-full h-full" src="{{ image_url('galleries', $collection->image ?? '') }}" alt="{{ $collection->title ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="w-full col-span-12">
                <div class="prose w-full -mx-auto max-w-none text-slate-600 prose-a:text-blue-500 prose-headings:text-blue-500 prose-p:text-slate-600" id="post_gallery">
                    {!! $collection->content ?? '-' !!}
                </div>
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>