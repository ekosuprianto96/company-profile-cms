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

@php
    $values = collect(array_values(array_map(fn($item) => $item['id'], $forms->gallery['value'] ?? [])));
    $galleries = $collection->where('an', 1)->latest()->whereIn('id', $values)->get();
    $sorted = $galleries->sortBy(function($item) use($values) {
        return $values->search(fn($v) => $v == $item->id);
    })->toArray();
@endphp

<x-frontend.templates.m-section
    :top="$height_section"
>
    <x-slot name="title">
        <div class="w-full flex flex-col overflow-hidden justify-center items-center">
            <h1 class="lg:text-3xl text-2xl mb-4 font-bold text-[var(--primary-color)]">{{ $forms->title['value'] ?? '-' }}</h1>
            @if(!empty($forms->sub_title['value']))
                <p class="lg:w-[40%] w-[80%] lg:text-md text-sm text-center text-[var(--secondary-color)]">{{ $forms->sub_title['value'] }}</p>
            @endif
        </div>
    </x-slot>
    <x-slot name="content">
        <div class="w-full mt-8">
            <div class="grid grid-cols-12 lg:gap-6 gap-4 auto-rows-[300px] grid-flow-dense">
                @php
                    $groupCounter = 5;
                @endphp

                @foreach (array_values($sorted ?? []) as $key => $gallery)
                    @php
                        $gallery = (object) $gallery;
                        $key = $key + 1;
                        $colSpan = ($key % 4 == 0 && $key === ($groupCounter - 1)) ? 'lg:col-span-7 col-span-12' : ($key % 5 == 0 && $key !== 0 ? 'lg:col-span-5 col-span-6' : 'lg:col-span-4 col-span-6');

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
                            decoding="async"
                            loading="lazy"
                            fetchpriority="high"
                        >
                        <div class="absolute w-full bottom-4 px-4">
                            <h4 class="text-[var(--light-color)] font-semibold lg:text-md text-sm">{{ $gallery->title }}</h4>
                        </div>
                    </a>
                @endforeach
            </div>  
            <div class="mt-6 w-full flex justify-center items-center">
                <a href="{{ route('galeri') }}" class="w-full lg:w-max">
                    <x-frontend.atoms.m-button 
                        variant="primary"
                        size="md"
                        className="w-full lg:w-max"
                    >
                        <span class="mx-4 lg:text-lg text-sm block">
                            Lihat Semua
                        </span>
                    </x-frontend.atoms.m-button>
                </a>
            </div>          
        </div>
    </x-slot>
</x-frontend.templates.m-section>