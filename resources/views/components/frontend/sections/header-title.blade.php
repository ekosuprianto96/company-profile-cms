@props([
    'collection' => null,
    "forms" => null,
    'title' => 'Header Title',
])

<div class="min-w-screen overflow-hidden flex justify-center items-center lg:min-h-[350px] lg:max-h-[400px] min-h-[150px] py-4 lg:py-0 relative">
    <img class="absolute top-0 bottom-0 right-0 left-0 object-cover w-full" src="{{ !empty($forms->background['value']) ? image_url($forms->background['path'], $forms->background['value']) : asset('assets/frontend/images/bg-header-title.jpg') }}" alt="{{ $forms->title['value'] ?? $title }}">
    <div class="absolute w-full h-full bg-black opacity-60 z-10"></div>
    <div class="relative lg:w-[40%] w-[90%] text-center lg:mt-[5%] z-30 flex justify-center items-center flex-col">
        <h1 class="font-bold text-[var(--light-color)] lg:text-4xl text-2xl">{{ $forms->title['value'] ?? $title }}</h1>
        @if(!empty($forms->sub_title['value']))
            <p class="lg:text-md text-sm text-center mt-4 text-[var(--light-color)]">{{ $forms->sub_title['value'] }}</p>
        @endif
    </div>
</div>