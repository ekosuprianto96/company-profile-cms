<div>
    <h5 class="font-bold mb-2 text-xl text-[var(--primary-color)]">Rekomdasi Kavling Yang Dijual</h5>
    <hr>
    <div class="w-full mt-4" id="caraousel-container">
        <div style="height: 280px;width: 250px" class="pr-3 py-3">
            <div class="w-full h-full overflow-hidden border rounded-lg">
                <div class="w-full h-[70%] bg-slate-200">
                    
                </div>
                <div class="w-full text-md font-semibold px-2 py-3 h-[30%]">
                    <h6 class="text-[var(--primary-color)] line-clamp-2">Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, harum.</h6>
                </div>
            </div>
        </div>
        <div style="height: 280px;width: 250px" class="pr-3 py-3">
            <div class="w-full h-full overflow-hidden border rounded-lg">
                <div class="w-full h-[70%] bg-slate-200">
                    
                </div>
                <div class="w-full text-md font-semibold px-2 py-3 h-[30%]">
                    <h6 class="text-[var(--primary-color)] line-clamp-2">Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, harum.</h6>
                </div>
            </div>
        </div>
        <div style="height: 280px;width: 250px" class="pr-3 py-3">
            <div class="w-full h-full overflow-hidden border rounded-lg">
                <div class="w-full h-[70%] bg-slate-200">
                    
                </div>
                <div class="w-full text-md font-semibold px-2 py-3 h-[30%]">
                    <h6 class="text-[var(--primary-color)] line-clamp-2">Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, harum.</h6>
                </div>
            </div>
        </div>
        <div style="height: 280px;width: 250px" class="pr-3 py-3">
            <div class="w-full h-full overflow-hidden border rounded-lg">
                <div class="w-full h-[70%] bg-slate-200">
                    
                </div>
                <div class="w-full text-md font-semibold px-2 py-3 h-[30%]">
                    <h6 class="text-[var(--primary-color)] line-clamp-2">Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, harum.</h6>
                </div>
            </div>
        </div>
        <div style="height: 280px;width: 250px" class="pr-3 py-3">
            <div class="w-full h-full overflow-hidden border rounded-lg">
                <div class="w-full h-[70%] bg-slate-200">
                    
                </div>
                <div class="w-full text-md font-semibold px-2 py-3 h-[30%]">
                    <h6 class="text-[var(--primary-color)] line-clamp-2">Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, harum.</h6>
                </div>
            </div>
        </div>
        <div style="height: 280px;width: 250px" class="pr-3 py-3">
            <div class="w-full h-full overflow-hidden border rounded-lg">
                <div class="w-full h-[70%] bg-slate-200">
                    
                </div>
                <div class="w-full text-md font-semibold px-2 py-3 h-[30%]">
                    <h6 class="text-[var(--primary-color)] line-clamp-2">Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, harum.</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#caraousel-container').slick({
            dots: true,
            infinite: false,
            speed: 300,
            slidesToShow: 4,
            slidesToScroll: 4,
            variableWidth: true,
            responsive: [
                {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: true,
                    dots: true
                }
                },
                {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
                },
                {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
                }
            ]
        });
    });
</script>