@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

@php
    $values = collect(array_values(array_map(fn($item) => $item['id'], $forms->layanan['value'] ?? [])));
    $services = $collection->where('an', 1)->latest()->whereIn('id', $values)->get();
    $sorted = $services->sortBy(function($item) use($values) {
        return $values->search(fn($v) => $v == $item->id);
    });
@endphp

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="title">
        <div class="w-full flex flex-col overflow-hidden justify-center items-center">
            <h1 class="lg:text-3xl text-2xl mb-4 font-bold text-blue-500">{{ $forms->title['value'] ?? '-' }}</h1>
            @if(!empty($forms->sub_title['value']))
                <p class="lg:w-[40%] w-[80%] lg:text-md text-sm text-center text-slate-600">{{ $forms->sub_title['value'] }}</p>
            @endif
        </div>
    </x-slot>
    <x-slot name="content">
        <div class="w-full lg:mt-8 mt-4">
            <div class="grid grid-cols-12 gap-6">
                @foreach ($sorted as $value)
                    <a href="{{ route('layanan.show', $value['slug']) }}" class="h-max hover:scale-105 transition-all hover:shadow lg:min-h-[230px] min-h-[200px] lg:col-span-4 col-span-12 bg-white rounded-lg border">
                        <div class="flex flex-col lg:p-6 p-4 justify-start h-full items-start">
                            <div class="lg:w-[60px] lg:h-[60px] mb-3 w-[50px] h-[50px] flex justify-center items-center lg:text-3xl text-xl text-blue-500 border-2 border-blue-300 rounded-lg">
                                <i class="{{ $value['icon'] }}"></i>
                            </div>
                            <div>
                                <h1 class="lg:text-xl text-md text-blue-500 mb-2 font-bold">{{ $value['title'] ?? '-' }}</h1>
                                <div class="text-sm text-slate-600">{!! cutTextByWords(strip_tags(str_replace('.', ' ', $value['content'])) ?? '-', 20, '...') !!}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>