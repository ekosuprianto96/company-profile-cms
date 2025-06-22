@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

<style>
    .item-galery:hover > img {
        filter: blur(3px) contrast(0.5);
        transition: 0.3s ease-in-out;
    }
</style>

<x-frontend.templates.m-section
    :top="$height_section"
>
    @if(($forms->show_section_title['value'] ?? 0) == 1)
        <x-slot name="title">
            <div class="w-full flex flex-col overflow-hidden justify-center items-center">
                <h1 class="lg:text-3xl text-2xl mb-4 font-bold text-[var(--primary-color)]">{{ $forms->title['value'] ?? '-' }}</h1>
                @if(!empty($forms->sub_title['value']))
                    <p class="lg:w-[40%] w-[80%] lg:text-md text-sm text-center text-[var(--secondary-color)]">{{ $forms->sub_title['value'] }}</p>
                @endif
            </div>
        </x-slot>
    @endif
    <x-slot name="content">
        <div class="w-full lg:mt-8 mt-4">
            <div class="grid grid-cols-12 gap-6 auto-rows-[300px] grid-flow-dense">
                @php
                    $groupCounter = 5;
                @endphp

                @foreach ($collection->where('an', 1)->latest()->limit($forms->max_show['value'] ?? 100)->get() as $key => $gallery)
                    @php
                        $key = $key + 1;
                        $colSpan = ($key % 4 == 0 && $key === ($groupCounter - 1)) ? 'lg:col-span-7 col-span-6' : ($key % 5 == 0 && ($groupCounter - 1) ? 'lg:col-span-5 col-span-12' : 'lg:col-span-4 col-span-6');

                        if($collection->count() % 5 === 1 && $key > 5) {
                            $colSpan = 'col-span-12';
                        }

                        if($key > $groupCounter) {
                            $groupCounter += 5;
                        }
                    @endphp
                    <a href="{{ route('galeri.show', $gallery->slug) }}" class="{{ $colSpan }} hover:scale-105 transition-all hover:shadow item-galery block relative rounded-lg overflow-hidden border">
                        <img 
                            class="w-full h-full object-cover"
                            src="{{ asset('assets/images/galleries/'.$gallery->image) }}" 
                            alt="{{ $gallery->title }}"
                        >
                        <div class="absolute w-full bottom-4 px-4">
                            <h4 class="text-[var(--light-color)] font-semibold text-md">{{ $gallery->title }}</h4>
                        </div>
                    </a>
                @endforeach
            </div>        
        </div>
    </x-slot>
</x-frontend.templates.m-section>