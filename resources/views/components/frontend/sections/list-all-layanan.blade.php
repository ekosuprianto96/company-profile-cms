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
                <h1 class="text-3xl w-[40%] text-center mb-4 font-bold text-blue-500">{{ $forms->title['value'] ?? '-' }}</h1>
                @if(!empty($forms->sub_title['value']))
                    <p class="w-[40%] text-md text-center text-slate-600">{{ $forms->sub_title['value'] }}</p>
                @endif
            </div>
        </x-slot>
    @endif
    <x-slot name="content">
        <div class="w-full mt-8">
            <div class="grid grid-cols-12 gap-6">
                @foreach ($collection->where('an', 1)->latest()->get() as $value)
                    <a href="{{ route('layanan.show', $value['slug']) }}" class="h-max hover:scale-105 transition-all hover:shadow min-h-[230px] lg:col-span-4 col-span-12 bg-white rounded-lg border">
                        <div class="flex flex-col p-6 justify-start h-full items-start">
                            <div class="w-[60px] mb-3 h-[60px] flex justify-center items-center lg:text-3xl text-2xl text-blue-500 border-2 border-blue-300 rounded-lg">
                                <i class="{{ $value['icon'] }}"></i>
                            </div>
                            <div>
                                <h1 class="lg:text-xl text-md text-blue-500 mb-2 font-bold">{{ $value['title'] ?? '-' }}</h1>
                                <div class="lg:text-sm text-xs text-slate-600">{!! cutTextByWords(strip_tags(str_replace('.', ' ', $value['content'])) ?? '-', 20, '...') !!}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>