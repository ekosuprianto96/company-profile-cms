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
                    <div class="w-full overflow-hidden lg:h-[500px] rounded-lg">
                        @if(!empty($informasi->thumbnail->file ?? ''))
                            <img  
                                decoding="async"
                                loading="lazy"
                                fetchpriority="high"
                                class="object-cover w-full h-full" 
                                src="{{ image_url('informasi', $informasi->thumbnail->file ?? '') }}" 
                                alt="{{ $informasi->title ?? '' }}"
                            >
                        @endif
                    </div>
                </div>
                <div class="lg:col-span-7 col-span-12 text-[var(--secondary-color)]" id="tentang__kami">
                    <div class="w-max mb-4">
                        <h1 class="mb-3 dinamic_text_size-h1 text-2xl text-[var(--primary-color)]">{{ $informasi->title ?? '-' }}</h1>
                        <div class="h-[8px] w-[50%] bg-[var(--primary-color)] rounded-full"></div>
                    </div>
                    <div class="prose prose-a:[var(--primary-color)] prose-p:[var(--secondary-color)]">
                        {!! cutTextByWords($informasi->content ?? '-', 70, '...') ?? '-' !!}
                    </div>
                    <div class="mt-6">
                        <div class="flex lg:justify-start justify-center text-center items-center">
                            @foreach ($counters as $key => $counter)
                                <div class="flex text-sm justify-center lg:min-w-[130px] min-w-[120px] items-center text-center flex-col {{ ($key + 1) < count($counters) ? 'border-r-2' : '' }} lg:px-6 px-3 py-1">
                                    <span class="mb-2 text-md font-semibold text-[var(--primary-color)]">+{{ $counter['value'] ?? 0 }}</span>
                                    <span class="text-[var(--secondary-color)] text-sm">{{ $counter['label'] ?? '-' }}</span>
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