@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                <div>
                    <h4 class="card-title mb-1">Edit Produk</h4>
                    <p class="text-muted mb-0">{{ $product->name }}</p>
                </div>
                <a href="{{ route('admin.mobile.products') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar Produk</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @include('admin.components.forms.partials.product-fields', ['product' => $product, 'categories' => $categories, 'services' => $services, 'categoryTree' => $categoryTree, 'formId' => 'productForm'])
                <div class="d-flex justify-content-end mt-3" style="gap:10px;">
                    <a href="{{ route('admin.mobile.products') }}" class="btn btn-light">Batal</a>
                    <button type="button" id="saveProduct" class="btn btn-primary"><i class="ri-save-3-line me-1"></i> Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#productForm').on('keyup change', 'input, select, textarea', function () {
            const f = ($(this).attr('name') || '').replace('[]', '');
            $(this).removeClass('is-invalid');
            $(`[data-error="${f}"]`).find('span').text('');
        });
        $('#saveProduct').click(function () {
            const $btn = $(this).prop('disabled', true);
            const fd = new FormData(document.getElementById('productForm'));
            $.ajax({ url: '{{ route("admin.mobile.products.update", $product->id) }}', method: 'POST', data: fd, processData: false, contentType: false })
                .done(function (r) {
                    $.toast({ heading: 'Sukses!', text: r.message, showHideTransition: 'slide', position: 'top-right', icon: 'success' });
                    setTimeout(() => window.location = '{{ route("admin.mobile.products") }}', 600);
                })
                .fail(function (e) {
                    const r = e.responseJSON || {};
                    if (r.errors) { $.parseErros(r.errors); }
                    $.toast({ heading: 'Warning', text: r.message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' });
                    $btn.prop('disabled', false);
                });
        });
    });
</script>
@endsection
