@props([
    'collection' => [],
    'widget' => null,
    'forms' => [],
    'height_section' => '80px'
])

@php($showIn = collect($forms->show_in_layanan['value'])->pluck('id')->toArray())

@if(in_array(($widget->id ?? 0), $showIn ?? []))
<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="content">
        <h5 class="font-bold mb-2 text-xl text-[var(--primary-color)]">{{  $forms->title['value'] ?? '-' }}</h5>
        <hr>
        <div class="w-full mt-4" id="caraousel-container">
            @foreach ($collection->latest()->get() as $value)
                @php($images = $value->images())
                <div style="height: 280px;width: 250px" class="pr-3 py-3">
                    <a href="{{ route('layanan.show.widget', ['slug' => $widget->slug, 'widget' => $value->slug]) }}" class="w-full block hover:shadow transition-all h-full overflow-hidden border rounded-lg">
                        <div class="w-full h-[70%] bg-slate-200">
                            <img title="{{  $value->title }}" class="w-full h-full object-cover" src="{{ image_url('rekomendasi-kavling', $images[0] ?? '') }}" alt="{{ $value->title }}">
                        </div>
                        <div class="w-full text-md font-semibold px-2 py-3 h-[30%]">
                            <h6 title="{{  $value->title }}" class="text-[var(--primary-color)] line-clamp-2">{{  $value->title }}</h6>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </x-slot>
</x-frontend.templates.m-section>

<script>
    $(document).ready(function () {
        $('#caraousel-container').slick({
            dots: true,
            infinite: false,
            speed: 300,
            slidesToShow: 4,
            slidesToScroll: 4,
            variableWidth: true,
            responsive: [
                {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: true,
                    dots: true
                }
                },
                {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
                },
                {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
                }
            ]
        });
    });
</script>
@endif