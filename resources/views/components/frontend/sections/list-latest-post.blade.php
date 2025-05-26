@props([
    'collection' => [],
    'forms' => null,
    'not_in' => [],
    'height_section' => '120px'
])

@php
    $posts = $collection->where('an', 1)->latest()->whereNotIn('slug', $not_in)->limit($forms->max_show['value'] ?? 6)->get();
@endphp

<x-frontend.templates.m-section :top="'15px'" mobileTop="20px">
    <x-slot name="content">
        <div class="w-full p-6 bg-white border rounded-lg shadow-sm">
            <div class="font-bold">
                <span class="block text-blue-500 mb-2">{{ $forms->title['value'] ?? 'Postingan Terbaru' }}</span>
                <div class="h-[6px] w-[55%] bg-blue-500 rounded-full"></div>
            </div>
            <ul class="w-full mt-4 space-y-2">
                @foreach($posts as $key => $post)
                    <li class="py-2 border-b last:border-0 w-full text-blue-500 text-sm truncate">
                        <article class="w-full">
                            <div class="flex justify-start gap-4 items-start">
                                <div class="w-[20%] border h-[40px] rounded-lg overflow-hidden mb-2">
                                    <img class="object-cover w-full h-full" src="{{ image_url('blogs', $post->thumbnail) }}" title="{{ $post->title }}" alt="{{ $post->title }}">
                                </div>
                                <div class="w-[80%]">
                                    <a href="{{ route('blog.show', $post->slug) }}" 
                                        class="font-semibold truncate block max-w-full hover:underline" 
                                        title="Baca {{ $post->title }} - {{ $post->created_at->format('d M Y') }}" 
                                        aria-label="Link ke postingan {{ $post->title }}">
                                        {{ $post->title }}
                                    </a>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span><i class="ri-calendar-line"></i> {{ $post->created_at->format('d M Y') }}</span>
                                        <!-- Optional category -->
                                        @if($post->category)
                                            <span class="ml-2">{{ $post->category->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-slot>
</x-frontend.templates.m-section>
