@props([
    'collection' => [],
    "forms" => null,
    'height_section' => '120px'
])

<x-frontend.templates.m-section
    :top="$height_section"
>
    @if($forms->show_section_title['value'] ?? 0 == 1)
        <x-slot name="title">
            <div class="w-full flex flex-col overflow-hidden justify-center items-center">
                <h1 class="text-3xl w-[40%] text-center mb-4 font-bold text-[var(--primary-color)]">{{ $forms->title['value'] ?? '-' }}</h1>
                @if(!empty($forms->sub_title['value']))
                    <p class="w-[40%] text-md text-center text-[var(--secondary-color)]">{{ $forms->sub_title['value'] }}</p>
                @endif
            </div>
        </x-slot>
    @endif
    <x-slot name="content">
        <div class="w-full mt-8">
            <div class="grid grid-cols-12 gap-6">
                @foreach ($collection->where('an', 1)->latest()->get() as $value)
                    <a href="{{ route('layanan.show', $value['slug']) }}" class="h-max hover:scale-105 transition-all hover:shadow min-h-[230px] lg:col-span-4 col-span-12 bg-[var(--light-color)] rounded-lg border">
                        <div class="flex flex-col p-6 justify-start h-full items-start">
                            <div class="w-[60px] mb-3 h-[60px] p-2 overflow-hidden flex justify-center items-center lg:text-3xl text-2xl text-[var(--primary-color)] border-2 border-[var(--light-blue-color)] rounded-lg">
                                @if($value['type'] == 'icon')
                                    <i class="{{ $value['icon'] }}"></i>
                                @else
                                    <img class="rounded-lg" src="{{ image_url('services', $value['url_image'] ?? '') }}" width="50px" alt="">
                                @endif
                            </div>
                            <div>
                                <h1 class="lg:text-xl text-md text-[var(--primary-color)] mb-2 font-bold">{{ $value['title'] ?? '-' }}</h1>
                                <div class="lg:text-sm text-xs text-[var(--secondary-color)]">{!! cutTextByWords(strip_tags(str_replace('.', ' ', $value['content'])) ?? '-', 20, '...') !!}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>