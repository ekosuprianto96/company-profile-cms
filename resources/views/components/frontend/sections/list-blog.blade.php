@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

@php
    $values = collect(array_values(array_map(fn($item) => $item['id'], $forms->article['value'] ?? [])));
    $services = $collection->where('an', 1)->latest()->whereIn('id', $values)->get();
    $sorted = $services->sortBy(function($item) use($values) {
        return $values->search(fn($v) => $v == $item->id);
    });
@endphp

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="title">
        <div class="w-full flex flex-col overflow-hidden justify-center items-center">
            <h1 class="lg:text-3xl text-2xl mb-4 font-bold text-blue-500">{{ $forms->title['value'] ?? '-' }}</h1>
            @if(!empty($forms->sub_title['value']))
                <p class="lg:w-[40%] w-[80%] lg:text-md text-sm text-center text-slate-600">{{ $forms->sub_title['value'] }}</p>
            @endif
        </div>
    </x-slot>
    <x-slot name="content">
        <div class="w-full lg:mt-8 mt-4">
            <div class="grid grid-cols-12 gap-6 auto-rows-[400px] grid-flow-dense">
                @foreach($sorted as $key => $value)
                    <article class="lg:col-span-4 col-span-12" itemscope itemtype="https://schema.org/Article">
                        <div class="w-full text-slate-600 h-[400px] border bg-white p-3 overflow-hidden rounded-lg">
                            <div class="h-[60%] w-full bg-slate-100 rounded-lg overflow-hidden">
                                <img 
                                    decoding="async"
                                    loading="lazy"
                                    fetchpriority="high"
                                    class="object-cover w-full h-full" 
                                    src="{{ image_url('blogs', $value->thumbnail) }}" 
                                    alt="{{ $value->title }}" 
                                    itemprop="image"
                                >
                            </div>
                            <div class="h-[40%] w-full">
                                <div class="flex my-2 text-sm justify-between items-center">
                                    <a rel="author" itemprop="author" href="" class="flex gap-2 items-center">
                                        <i class="ri-user-line"></i>
                                        <span>{{ $value->createdBy->account->nama_lengkap ?? '-' }}</span>
                                    </a>
                                    <a href="" class="flex gap-2 items-center">
                                        <i class="ri-calendar-line"></i>
                                        <time 
                                            itemprop="datePublished" 
                                            datetime="{{ $value->created_at->timezone('Asia/Jakarta')->format('Y-m-d') }}"
                                        >
                                            {{ $value->created_at->timezone('Asia/Jakarta')->diffForHumans() }}
                                        </time>
                                    </a>
                                </div>
                                <a href="{{ route('blog.show', $value->slug) }}" title="{{ $value->title }}" itemprop="url" rel="bookmark">
                                    <h2 itemprop="headline" class="font-semibold my-4 lg:text-[20px] text-[16px] text-blue-500">
                                        {{ cutTextByWords($value->title ?? '-', 7) }}
                                    </h2>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>