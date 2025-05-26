@props([
    'top' => '0px',
    'bottom' => '0px',
    'mobileTop' => '40px',
    'mobileBottom' => '0px',
    'uniqueId' => uniqid(),
])

<style>
    section.m-section-{{ $uniqueId }} {
        margin-top: {{ $top }};
        margin-bottom: {{ $bottom }};
    }

    @media screen and (max-width: 768px) {
        section.m-section-{{ $uniqueId }} {
            margin-top: {{ $mobileTop }};
            margin-bottom: {{ $mobileBottom }};
        }
    }
</style>

<section 
    {{ $attributes->merge(['class' => 'w-full h-max m-section-'.$uniqueId.'']) }}
>
    @isset($title)
        {{ $title }}
    @endisset
    @isset($content)
        {{ $content }}
    @endisset
</section>