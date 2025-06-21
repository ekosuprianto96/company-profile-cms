@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px',
    'kategori_slug' => null,
    'search' => null
])

@if(!empty($kategori_slug))
    @php
        $collection = $collection->whereHas('kategori', function($query) use($kategori_slug) {
            $query->where('slug', $kategori_slug);
        });
    @endphp
@endif

@if(!empty($search))
    @php
        $collection = $collection
        ->where('title', 'like', '%' . $search . '%')
        ->orWhere('slug', 'like', '%' . $search . '%')
        ->orWhereHas('kategori', function($query) use($search) {
            $query->where('kategori_blog.name', 'like', '%' . $search . '%');
        });
    @endphp
@endif

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="20px"
>
    @if(($forms->show_section_title['value'] ?? 0) == 1)
        <x-slot name="title">
            <div class="w-full flex flex-col overflow-hidden justify-center items-center">
                <h1 class="text-3xl mb-4 font-bold text-blue-500">{{ $forms->title['value'] ?? '-' }}</h1>
                @if(!empty($forms->sub_title['value']))
                    <p class="w-[40%] text-md text-center text-slate-600">{{ $forms->sub_title['value'] }}</p>
                @endif
            </div>
        </x-slot>
    @endif
    <x-slot name="content">
        <div class="w-full lg:mt-8">
            <div class="grid lg:grid-cols-2 grid-cols-1 aut-rows-[400px] lg:gap-4 gap-3 grid-flow-dense">
                @if($collection->count() > 0)
                    @foreach($collection->where('an', 1)->latest()->limit($forms->max_show['value'] ?? 500)->get() as $key => $value)
                        <article itemscope itemtype="https://schema.org/Article">
                            <div class="w-full text-slate-600 h-[400px] border bg-white p-3 overflow-hidden rounded-lg">
                                <div class="h-[60%] relative w-full bg-slate-100 rounded-lg overflow-hidden">
                                    <img 
                                        decoding="async"
                                        loading="lazy"
                                        fetchpriority="high"
                                        class="object-cover w-full h-full" 
                                        src="{{ image_url('blogs', $value->thumbnail) }}" 
                                        alt="{{ $value->title }}" 
                                        itemprop="image"
                                    >
                                    <span class="absolute top-2 left-2 bg-blue-500 text-white text-sm px-3 py-1 rounded-lg">{{ $value->kategori->name ?? '-' }}</span>
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
                @else
                    <x-frontend.templates.empty :forms="(object)['title' => ['value' => 'Yah Maaf!, Postingan yang anda cari tidak ditemukan.']]"></x-frontend.templates.empty>
                @endif
            </div>
        </div>
    </x-slot>
</x-frontend.templates.m-section>