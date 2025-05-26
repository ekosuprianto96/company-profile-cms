@props([
    'variant' => 'primary',
    'size' => 'md',
    'rounded' => 'md',
    'style' => 'none',
    'className' => ''
])

@php
    $classes = mStyles('button')->rounded($rounded)->variant($variant)->size($size)->get();
@endphp

<button
    {{ $attributes->merge(['class' => $classes.' '.$className]) }}
>
    {{ $slot }}
</button>