@props([
    'collection' => [],
    'forms' => null,
    'height_section' => '120px'
])

<x-frontend.templates.m-section
    :top="$height_section"
    mobileTop="40px"
>
    <x-slot name="title">
        <div class="w-full text-center flex flex-col overflow-hidden justify-center items-center">
            <h1 class="lg:text-3xl text-2xl lg:w-[40%] mb-4 font-bold text-blue-500">{{ $forms->title['value'] ?? '-' }}</h1>
            @if(!empty($forms->sub_title['value']))
                <p class="lg:w-[40%] w-[80%] lg:text-md text-sm text-center text-slate-600">{{ $forms->sub_title['value'] }}</p>
            @endif
        </div>
    </x-slot>
    <x-slot name="content">
        <div class="w-full mt-8">
            <div class="grid grid-cols-12 lg:gap-6 gap-4">
                @php
                    $servicesPackages = $collection->getAllPackage();
                @endphp
                @foreach($servicesPackages as $key => $value)
                    <div class="lg:col-span-4 col-span-12">
                        <div class="w-full relative overflow-hidden bg-white min-h-[550px] h-max p-6 py-8 pb-16 border rounded-lg">
                            <div class="text-[0.8em] text-slate-600">
                                <h4 class="font-semibold mb-2 text-blue-500 text-2xl">{{ Str::upper($value['name']) }}</h4>
                                <p class="w-[70%]">{{ $value['description'] }}</p>
                            </div>
                            
                            @if((bool) $value['show_price'])
                                <div class="w-full flex mt-5 justify-center items-center">
                                    <span class="rounded-full font-bold text-2xl text-blue-500 bg-slate-100 p-4 w-full text-center">Rp. {{ number_format($value['price'], 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="{{ (bool) $value['show_price'] ? 'mt-6' : 'mt-14' }} px-3 w-full">
                                <ul class="list-outside list-disc w-full text-slate-600">
                                    @foreach($value['features'] as $key => $feature)
                                        <li class="text-[0.9em] mb-5">{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="text-lg absolute left-6 right-6 rounded-full bg-slate-100 px-6 py-2 bottom-6 text-blue-500">
                                <a href="" class="font-bold flex justify-between items-center">
                                    <span class="me-3">Pilih Paket</span>
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                            
                            @if((bool) $value['is_recommended'])
                                <div class="absolute -rotate-90 rounded-full w-max h-max py-2 bg-blue-500 top-8 -right-[40px]">
                                    <span class="p-4 pr-8 text-white">Recomended</span>
                                </div>   
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>         
        </div>
    </x-slot>
</x-frontend.templates.m-section>