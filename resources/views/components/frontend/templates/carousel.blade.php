<style>
.slick-dots {
    position: absolute;
    bottom: 10px;
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
    width: 100%;
}

.slick-dots li {
    margin: 0 6px;
}

.slick-dots li button {
    font-size: 0; /* Hapus angka bawaan */
    width: 12px;
    height: 8px;
    border-radius: 30px;
    background: rgb(202, 202, 202);
    border: none;
    cursor: pointer;
    opacity: 0.6;
    transition: background 0.3s;
}

.slick-dots li.slick-active button {
    background: rgb(255, 255, 255); /* Warna dot aktif */
    width: 18px;
    height: 8px;
}
</style>

@props([
    'speed' => 500
])

<div id="caraousel-container" class="w-screen overflow-hidden flex h-full">
    {{ $slot }}
</div>

<script>
    $('#caraousel-container').slick({
        dots: true,
        infinite: true,
        speed: '{{ (int) $speed }}',
        slidesToShow: 1,
        adaptiveHeight: true,
        arrows: false,
        autoplay: true,
        customPaging: function (slider, i) {
            return '<button></button>';
        },
        draggable: true
    });
</script>