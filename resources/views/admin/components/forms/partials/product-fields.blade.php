@php
    $selectedServiceIds = optional($product)->services ? $product->services->pluck('id')->all() : [];
    $imgUrl = optional($product)->primary_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->primary_image) : null;
@endphp

<form id="{{ $formId }}" class="forms-sample product-form" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-8 form-group">
            <label class="form-label">Nama Produk</label>
            <input name="name" type="text" class="form-control" value="{{ optional($product)->name }}" placeholder="HAKATA Sofa 3 Dudukan">
            <div data-error="name"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">SKU <small class="text-muted">(kosong = otomatis)</small></label>
            <input name="sku" type="text" class="form-control text-uppercase" value="{{ optional($product)->sku }}" placeholder="HAKATA-XXXX">
            <div data-error="sku"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
    </div>

    {{-- Kategori master bertingkat (bersama layanan) --}}
    @include('admin.components.forms.partials.category-picker', [
        'categoryTree' => $categoryTree ?? collect(),
        'selectedCategoryId' => optional($product)->category_id,
    ])

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label">Kategori Katalog <small class="text-muted">(opsional)</small></label>
            <select name="product_category_id" class="form-control">
                <option value="">— Tanpa kategori katalog —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(optional($product)->product_category_id == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Brand</label>
            <input name="brand" type="text" class="form-control" value="{{ optional($product)->brand }}" placeholder="HAKATA">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Harga (Rp)</label>
            <input name="price" type="number" min="0" class="form-control" value="{{ optional($product)->price }}" placeholder="3499000">
            <div data-error="price"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Harga Coret (Rp)</label>
            <input name="compare_at_price" type="number" min="0" class="form-control" value="{{ optional($product)->compare_at_price }}" placeholder="opsional">
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Stok</label>
            <input name="stock" type="number" min="0" class="form-control" value="{{ optional($product)->stock ?? 0 }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Berat (gram)</label>
            <input name="weight_grams" type="number" min="0" class="form-control" value="{{ optional($product)->weight_grams ?? 0 }}" placeholder="32000">
        </div>
        <div class="col-md-8 form-group">
            <label class="form-label">Deskripsi Singkat</label>
            <input name="short_description" type="text" class="form-control" value="{{ optional($product)->short_description }}">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" rows="3" class="form-control">{{ optional($product)->description }}</textarea>
    </div>

    <div class="form-group">
        <label class="form-label">Gambar Utama</label>
        @if ($imgUrl)<div class="mb-2"><img src="{{ $imgUrl }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px;"></div>@endif
        <input name="primary_image" type="file" accept="image/*" class="form-control">
        <div data-error="primary_image"><span class="text-danger" style="font-size:.8em"></span></div>
    </div>

    <div class="col-12 mt-2 mb-2"><h5 class="mb-0">Pengaturan Produk</h5><small class="text-muted">Wajib diisi — mendukung sistem paket &amp; integrasi layanan.</small></div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Bisa Dibundel Paket?</label>
            <select name="can_be_bundled" class="form-control">
                <option value="0" @selected(!optional($product)->can_be_bundled)>Tidak</option>
                <option value="1" @selected(optional($product)->can_be_bundled)>Ya</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Cakupan Layanan</label>
            <select name="service_scope" class="form-control js-service-scope">
                <option value="all" @selected(optional($product)->service_scope !== 'specific')>Semua layanan</option>
                <option value="specific" @selected(optional($product)->service_scope === 'specific')>Layanan tertentu</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Metode Pengiriman</label>
            <select name="shipping_method" class="form-control js-shipping-method">
                <option value="internal" @selected(optional($product)->shipping_method !== 'courier')>Kurir Internal</option>
                <option value="courier" @selected(optional($product)->shipping_method === 'courier')>Jasa Kurir (pihak ke-3)</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 form-group js-service-picker">
            <label class="form-label">Layanan yang Berlaku</label>
            <select name="service_ids[]" class="form-control" multiple>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(in_array($service->id, $selectedServiceIds))>{{ $service->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 form-group js-internal-fee">
            <label class="form-label">Ongkir Internal (Rp)</label>
            <input name="internal_shipping_fee" type="number" min="0" class="form-control" value="{{ optional($product)->internal_shipping_fee }}" placeholder="opsional">
            <small class="text-muted js-courier-note d-none text-warning">Jasa kurir pihak ke-3 belum aktif (perlu integrasi API).</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label">Tampil di Home <small class="text-muted">(Unggulan)</small></label>
            <select name="is_featured" class="form-control">
                <option value="0" @selected(!optional($product)->is_featured)>Tidak</option>
                <option value="1" @selected(optional($product)->is_featured)>Ya</option>
            </select>
            <small class="text-muted">Jika "Ya", produk muncul di section "Produk Pilihan" home mobile.</small>
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-control">
                <option value="1" @selected(optional($product)->is_active ?? true)>Aktif</option>
                <option value="0" @selected($product && ! $product->is_active)>Nonaktif</option>
            </select>
        </div>
    </div>
</form>

<script>
    (function() {
        const form = document.getElementById('{{ $formId }}');
        if (!form) return;
        const $form = $(form);
        const $modal = $form.closest('.modal');

        function ensureSelect2($select, placeholder) {
            if (!$.fn.select2 || !$select.length || $select.hasClass('select2-hidden-accessible')) return;
            $select.select2({ width:'100%', placeholder, closeOnSelect:false, allowClear:true, dropdownParent: $modal.length ? $modal : $(document.body) });
        }
        const sync = function() {
            const scope = $form.find('.js-service-scope').val();
            const ship = $form.find('.js-shipping-method').val();
            const showService = scope === 'specific';
            $form.find('.js-service-picker').toggle(showService);
            if (showService) ensureSelect2($form.find('select[name="service_ids[]"]'), 'Pilih layanan…');
            $form.find('.js-internal-fee input').prop('disabled', ship === 'courier');
            $form.find('.js-courier-note').toggleClass('d-none', ship !== 'courier');
        };
        $form.on('change', '.js-service-scope, .js-shipping-method', sync);
        sync();
    })();
</script>
