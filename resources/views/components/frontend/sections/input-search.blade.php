@props([
    'collection' => [],
    'forms' => null,
    'value' => '',
    'height_section' => '120px'
])

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="20px"
>
    <x-slot name="content">
        <form action="{{ route('blog') }}" method="GET" class="w-full border rounded-lg h-[50px] relative">
            <button class="absolute text-[var(--primary-color)] right-4 top-1/2 -translate-y-1/2">
                <i class="ri-search-line"></i>
            </button>
            <input type="text" value="{{ $value }}" name="search" placeholder="{{ $forms->placeholder['value'] ?? '' }}" class="w-full h-full rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300">
        </form>
    </x-slot>
</x-frontend.templates.m-section>