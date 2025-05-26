@props([
    'collection' => null,
    'forms' => null,
    'height_section' => '80px'
])

@php($collection = $collection->where('key', 'tentang-kami')->first())
@php($informasi = json_decode($collection->value ?? '{}'))
@php($counters = parseCounterSection($forms->counter_section['value'] ?? ''))

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="content">
        <div class="w-full lg:mt-8">
            <div class="grid grid-cols-12 gap-6">
                <div class="lg:col-span-5 col-span-12">
                    <div class="w-full overflow-hidden lg:h-[500px] bg-slate-400 rounded-lg">
                        @if(!empty($informasi->thumbnail->file ?? ''))
                            <img class="object-cover w-full h-full" src="{{ image_url('informasi', $informasi->thumbnail->file ?? '') }}" alt="{{ $informasi->title ?? '' }}">
                        @endif
                    </div>
                </div>
                <div class="lg:col-span-7 col-span-12 text-slate-600" id="tentang__kami">
                    <div class="w-max mb-4">
                        <h1 class="mb-3 lg:text-3xl text-2xl text-blue-500 font-bold">{{ $informasi->title ?? '-' }}</h1>
                        <div class="h-[8px] w-[50%] bg-blue-500 rounded-full"></div>
                    </div>
                    <div class="prose prose-a:text-blue-500 prose-sm">
                        {!! cutTextByWords($informasi->content ?? '-', 70, '...') ?? '-' !!}
                    </div>
                    <div class="mt-6">
                        <div class="flex justify-start items-center">
                            @foreach ($counters as $counter)
                                <div class="flex text-sm justify-center min-w-[130px] items-center text-center flex-col border-r-2 px-6 py-1">
                                    <span class="mb-2 text-md font-semibold text-blue-500">+{{ $counter['value'] ?? 0 }}</span>
                                    <span class="text-light text-sm">{{ $counter['label'] ?? '-' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-8">
                        <a target="{{ $forms->action_target['value'] ?? '_self' }}" href="{{ route("tentang_kami") }}">
                            <x-frontend.atoms.m-button 
                                variant="primary"
                                size="md"
                                className="w-full lg:w-max"
                            >
                                <span class="mx-3 text-md block">
                                    @if(isset($forms->icon['value']) && !empty($forms->icon['value']))
                                        <i class="{{ $forms->icon['value'] }} me-2"></i>
                                    @endif
                                    {{ $forms->text_button['value'] ?? ($forms->text_button['default'] ?? 'Text Button') }}
                                </span>
                            </x-frontend.atoms.m-button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>