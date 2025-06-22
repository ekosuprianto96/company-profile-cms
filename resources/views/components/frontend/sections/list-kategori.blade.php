@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

@php
    $values = collect(array_values(array_map(fn($item) => $item['id'], $forms->kategori['value'] ?? [])));
    $services = $collection->where('an', 1)->latest()->whereIn('slug', $values)->get();
    $sorted = $services->sortBy(function($item) use($values) {
        return $values->search(fn($v) => $v == $item->id);
    });
@endphp

<x-frontend.templates.m-section
    :top="'15px'"
    mobileTop="20px"
>
    <x-slot name="content">
        <div class="w-full p-6 bg-[var(--light-color)] border rounded-lg">
            <div class="font-bold">
                <span class="block text-[var(--primary-color)] mb-2">Kategori</span>
                <div class="h-[6px] w-[30%] bg-[var(--primary-color)] rounded-full"></div>
            </div>
            <ul class="w-full mt-3">
                <li class="border-b py-4 text-[var(--primary-color)] text-sm truncate min-w-[90%]">
                    <a href="{{ route('blog') }}">
                        Semua Kategori
                    </a>
                </li>
                @foreach($sorted as $key => $value)
                    <li class="{{ $key < (count($sorted) - 1) ? 'border-b' : '' }} py-4 text-[var(--primary-color)] text-sm truncate min-w-[90%]">
                        <a href="{{ route('blog', ['kategori' => $value->slug]) }}">
                            {{ $value->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-slot>
</x-frontend.templates.m-section>