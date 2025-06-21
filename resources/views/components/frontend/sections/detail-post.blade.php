@props([
    'collection' => [],
    'height_section' => '80px'
])

<style>
    #post_blog p {
        text-align: justify;
        text-justify: inter-word;
        font-size: 0.9em;
        hyphens: auto;
        -webkit-hyphens: auto;
        -ms-hyphens: auto;
        -moz-hyphens: auto;
    }
    #social-links ul {
        display: flex !important;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
    }

    #social-links ul li {
        padding: 10px;
        font-size: 2em;
        color: rgb(29, 113, 223);
    }

    @media screen and (max-width: 768px) {
        #social-links ul li {
            font-size: 1.5em;
        }
    }
</style>

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="content">
        <div class="grid items-start grid-cols-12 gap-6 mt-14">
            <div class="w-full col-span-12">
                <div class="w-full h-max overflow-hidden rounded-lg">
                    @if(!empty($collection->thumbnail ?? ''))
                        <img class="object-cover w-full h-full" src="{{ image_url('blogs', $collection->thumbnail ?? '') }}" alt="{{ $collection->title ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="w-full col-span-12">
                <div class="mb-4">
                    <span class="bg-blue-500 text-white text-sm px-3 py-1 rounded-lg">{{ $collection->kategori->name ?? '-' }}</span>
                </div>
                <div class="lg:prose prose-sm w-full -mx-auto max-w-none text-slate-600 prose-a:text-blue-500 prose-headings:text-blue-500 prose-p:text-slate-600" id="post_blog">
                    {!! $collection->content ?? '-' !!}
                </div>
            </div>
            <div class="w-full col-span-12">
                <h5 class="py-4 border-b-2 w-full text-blue-500 border-b-slate-400">Share :</h5>
                {!! 
                    \Share::page(
                        url()->current(),
                        $collection->title ?? '',
                    )
                    ->facebook()
                    ->twitter()
                    ->linkedin()
                    ->telegram()
                    ->whatsapp()        
                    ->reddit(); 
                !!}
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>