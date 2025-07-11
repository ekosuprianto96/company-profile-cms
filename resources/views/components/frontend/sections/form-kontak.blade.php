@props([
    'collection' => null,
    'forms' => null,
    'height_section' => '80px'
])

@php($collection = app(\App\Services\PackageService::class)->getAllPackage())
{{-- @php($informasi = json_decode($collection->value ?? '{}')) --}}

<style>
    /* #kontak__kami p {
        text-align: justify;
        text-justify: inter-word;
        font-size: 1em;
        line-height: 20px;
        hyphens: auto;
        -webkit-hyphens: auto;
        -ms-hyphens: auto;
        -moz-hyphens: auto;
    } */
</style>
{{-- @dd($collection->getAllPackage()) --}}
<x-frontend.templates.m-section :top="$height_section">
    <x-slot name="content">
        <div class="w-full mt-8">
            <!-- Navigation Tabs -->
            <div class="flex justify-center items-center">
                <ul class="relative overflow-hidden rounded-full p-3 gap-3 flex justify-between items-center bg-[var(--primary-color)] lg:min-w-[40%] w-full lg:max-w-[60%] shadow-lg">
                    <li id="tab-permintaan" class=" bg-[var(--light-color)] text-[var(--primary-color)] relative z-[50] w-[50%] font-semibold text-sm rounded-full p-3 text-center transition-all duration-300 hover:bg-[var(--light-color)] hover:text-[var(--primary-color)]">
                        <button class="w-full h-full">{{ $forms->button_text_1['value'] ?? 'Buat Permintaan' }}</button>
                    </li>
                    <li id="tab-kontak" class="text-[var(--light-color)] relative z-[50] w-[50%] font-semibold text-sm rounded-full p-3 text-center transition-all duration-300 hover:bg-[var(--light-color)] hover:text-[var(--primary-color)]">
                        <button class="w-full h-full">{{ $forms->button_text_2['value'] ?? 'Hubungi Kami' }}</button>
                    </li>
                    <img class="z-[30] absolute -rotate-12 -left-4 lg:-left-4 lg:w-[800px] w-[600px] lg:-bottom-32 -bottom-16" src="{{ asset('assets/frontend/images/el1.svg') }}" alt="">
                </ul>
            </div>

            <!-- Contact Form Section with Overlay -->
            <div class="relative mt-12 rounded-xl overflow-hidden">
                <!-- Background image and overlay -->
                <img class="border z-[30] absolute -left-6 lg:-bottom-8 -bottom-3 lg:min-w-[1400px] min-w-[1500px]" src="{{ asset('assets/frontend/images/el1.svg') }}" alt="">
                <div class="absolute inset-0 bg-cover bg-center">
                    <div class="absolute inset-0 bg-[var(--primary-color)]"></div>
                    {{-- <!-- SVG Overlay Pemanis -->
                    <svg class="absolute bottom-0 left-0 right-0 w-full h-24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
                        <path fill="#ffffff" fill-opacity="0.40" d="M0,64L80,80C160,96,320,128,480,133.3C640,139,800,117,960,112C1120,107,1280,117,1360,122.7L1440,128L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>
                    </svg> --}}
                </div>

                <!-- Form Content -->
                <div id="tab__kontak__form" class="relative z-50 lg:px-12 px-4 lg:py-16 py-4">
                    <form id="send__email" method="POST" action="{{ route('kontak.send_email') }}" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Nama</label>
                                <input required name="name" type="text" class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan nama Anda">
                            </div>
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Email</label>
                                <input required name="email" type="email" class="w-full h-[50px] rounded-l text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan email Anda">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Alamat</label>
                                <input required name="address" type="text" class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan alamat Anda">
                            </div>
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">No Telepon</label>
                                <input required name="phone" type="text" class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan nomor telepon">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Subjek</label>
                            <input required name="subject" type="text" class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan subjek">
                        </div>
                        <div>
                            <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Pesan</label>
                            <textarea required name="message" class="w-full h-[150px] rounded-lg p-4 text-sm text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Tulis pesan Anda..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="bg-[var(--primary-color)] text-[var(--primary-color)] font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-[var(--primary-color)] hover:text-[var(--light-color)] transition-all duration-300">
                                Kirim Pesan
                            </button>
                        </div>
                    </form>
                    <form id="send__inquiry" method="POST" action="{{ route('kontak.send_inquiry') }}" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Nama</label>
                                <input type="text" required name="name" class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan nama Anda">
                            </div>
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Email</label>
                                <input type="email" required name="email" class="w-full h-[50px] rounded-l text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan email Anda">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Alamat</label>
                                <input type="text" required name="address" class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan alamat Anda">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Paket</label>
                                <select name="paket" required class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm">
                                    @foreach($collection as $key => $value)
                                        <option value="{{ $value['name'] }}">{{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[var(--light-color)] font-medium text-sm mb-2">Luas Tanah</label>
                                <input required name="luas_tanah" type="number" class="w-full h-[50px] rounded-lg text-sm px-6 text-[var(--secondary-color)] focus:outline-none border-2 border-transparent focus:border-[var(--light-blue-color)] transition-all duration-300 shadow-sm" placeholder="Masukkan luas tanah">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="bg-[var(--light-color)] text-[var(--primary-color)] font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-[var(--primary-color)] hover:text-[var(--light-color)] transition-all duration-300">
                                Kirim Permintaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                handleTabForm();
            });
        
            function handleTabForm() {
                const $tabKontak = $('#tab-kontak');
                const $tabPermintaan = $('#tab-permintaan');
        
                const $formEmail = $('#send__email');
                const $formInquiry = $('#send__inquiry');
        
                // Awal: Tampilkan form inquiry, sembunyikan form email
                $formEmail.hide();
                $formInquiry.show();
        
                // Handler Klik Tab Kontak Kami
                $tabKontak.on('click', function () {
                    $tabKontak
                        .addClass('bg-[var(--light-color)] text-[var(--primary-color)]')
                        .removeClass('text-[var(--light-color)] hover:bg-[--primary-color]');
        
                    $tabPermintaan
                        .addClass('text-[var(--light-color)] hover:bg-[--primary-color]')
                        .removeClass('bg-[var(--light-color)] text-[--primary-color]');
        
                    $formEmail.show();
                    $formInquiry.hide();
                });
        
                // Handler Klik Tab Buat Permintaan
                $tabPermintaan.on('click', function () {
                    $tabPermintaan
                        .addClass('bg-[var(--light-color)] text-[var(--primary-color)]')
                        .removeClass('text-[var(--light-color)] hover:bg-[var(--primary-color)]');
        
                    $tabKontak
                        .addClass('text-[var(--light-color)] hover:bg-[var(--primary-color)]')
                        .removeClass('bg-[var(--light-color)] text-[var(--primary-color)]');
        
                    $formEmail.hide();
                    $formInquiry.show();
                });
            }
        </script>
    </x-slot>    
</x-frontend.templates.m-section>
