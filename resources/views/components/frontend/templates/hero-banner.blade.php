@props([
    'banners' => [],
    'forms' => null
])

{{-- caraousel --}}
<div class="min-w-screen overflow-hidden h-[400px] lg:h-[800px] bg-slate-200 relative">
    <x-frontend.templates.carousel
        speed="800"
    >
        @foreach ($banners as $banner)
            <x-frontend.templates.carousel-item
                :image="asset('assets/images/banners/'.$banner->image_url)"
            >
                <div 
                    class="w-full lg:w-[70%] relative z-30 h-[400px] lg:h-[800px] flex flex-col py-6 px-4 lg:px-72 text-start lg:text-start justify-center lg:justify-center lg:items-start"
                >
                    <h1 class="my-3 lg:my-4 text-white font-bold text-2xl lg:text-5xl">{{ $banner->title }}</h1>
                    <p class="text-sm text-slate-50 lg:text-lg">{{ $banner->sub_title }}</p>
                    <div class="mt-4 lg:mt-6">
                        <a target="{{ $forms->action_target['value'] ?? '_self' }}" href="{{ $forms->redirect_url['value'] ?? 'javascript:void(0)' }}">
                            <x-frontend.atoms.m-button 
                                variant="primary"
                                size="md"
                            >
                                <span class="mx-4 lg:text-lg block">
                                    @if(isset($forms->icon['value']) && !empty($forms->icon['value']))
                                        <i class="{{ $forms->icon['value'] }} me-2"></i>
                                    @endif
                                    {{ $forms->button['value'] ?? ($forms->button['default'] ?? 'Mulai Sekarang') }}
                                </span>
                            </x-frontend.atoms.m-button>
                        </a>
                    </div>
                </div>
            </x-frontend.templates.carousel-item>
        @endforeach
    </x-frontend.templates.carousel>
</div>