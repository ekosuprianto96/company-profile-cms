@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="20px"
>
    <x-slot name="content">
        <div class="w-full border rounded-lg h-[50px] relative">
            <i class="absolute text-blue-500 right-4 top-1/2 -translate-y-1/2 ri-search-line"></i>
            <input type="text" placeholder="{{ $forms->placeholder['value'] ?? '' }}" class="w-full h-full rounded-lg text-sm px-6 text-gray-700 focus:outline-none border-2 border-transparent focus:border-blue-400 transition-all duration-300">
        </div>
    </x-slot>
</x-frontend.templates.m-section>