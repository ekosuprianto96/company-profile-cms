@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

@php
    $sorted = $collection->where('an', 1)->latest()->get();
@endphp

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="20px"
>
    <x-slot name="content">
        <div class="w-full p-6 bg-white border rounded-lg">
            <div class="font-bold">
                <span class="block text-blue-500 mb-2">Layanan Kami</span>
                <div class="h-[6px] w-[30%] bg-blue-500 rounded-full"></div>
            </div>
            <ul class="w-full mt-3">
                @foreach($sorted as $key => $value)
                    <li class="{{ $key < (count($sorted) - 1) ? 'border-b' : '' }} py-4 text-blue-500 text-sm truncate min-w-[90%]">
                        <a href="{{ route('layanan.show', $value->slug) }}">
                            {{ $value->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-slot>
</x-frontend.templates.m-section>