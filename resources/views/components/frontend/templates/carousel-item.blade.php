@props(['image' => ''])
<div 
    style="background-image: url('{{ $image }}')"
    class="flex bg-cover relative overflow-hidden bg-center justify-center items-center"
>
    <div class="absolute w-full h-full bg-black opacity-50 z-10"></div>
    {{ $slot }}
</div>