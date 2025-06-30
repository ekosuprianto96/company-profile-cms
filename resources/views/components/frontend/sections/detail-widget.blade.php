@props([
    'collection' => [],
    'widget' => null,
    'forms' => [],
    'height_section' => '80px'
])

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="content">
        <div class="grid items-start grid-cols-12 gap-6 mt-14">
            <div class="w-full col-span-12">
                {{-- Single Slider --}}
                <div id="multiSlider" class="w-full">
                    @foreach($collection->images() as $image)
                        <div style="width: 370px;" class="px-2 overflow-hidden">
                            <img class="object-cover w-full rounded-lg max-h-[350px]" src="{{ image_url('rekomendasi-kavling', $image) }}" alt="">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="w-full col-span-12">
                <div class="prose w-full -mx-auto max-w-none text-[var(--secondary-color)] prose-a:text-blue-500 prose-headings:text-[var(--primary-color)] prose-p:text-[var(--secondary-color)]" id="post_layanan">
                    {!! $collection->content ?? '-' !!}
                </div>
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>

<script>
    $(document).ready(function(){
        // $('.single-slider').slick({
        //     slidesToShow: 1,
        //     slidesToScroll: 1,
        //     arrows: true,
        //     dots: true,
        //     infinite: true,
        //     autoplay: true,
        //     autoplaySpeed: 3000
        // });

        $('#multiSlider').slick({
            dots: true,
            infinite: false,
            speed: 300,
            slidesToShow: 3,
            slidesToScroll: 3,
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