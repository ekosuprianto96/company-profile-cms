@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

<x-frontend.templates.m-section
    :top="'15px'"
    mobileTop="20px"
>
    <x-slot name="content">
        <a href="{{ $forms->link_redirect['value'] ?? '#' }}" class="w-full">
            <div style="color: {{ $forms->action_text_color['value'] ?? '#000000' }};background-color: {{ $forms->background_color['value'] ?? '#ffffff' }}" class="w-full p-6 rounded-lg text-center flex justify-center items-center flex-col">
                <span class="font-bold">{{ $forms->action_text['value'] ?? '' }}</span>
            </div>
        </a>
    </x-slot>
</x-frontend.templates.m-section>