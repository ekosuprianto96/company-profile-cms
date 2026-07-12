@php
    $selectedServiceIds = optional($voucher)->targetItems ? $voucher->targetItems->where('target_type', 'service')->pluck('target_id')->all() : [];
    $selectedUserIds = optional($voucher)->targetUsers ? $voucher->targetUsers->pluck('id')->all() : [];
    $fmt = fn ($dt) => $dt ? \Illuminate\Support\Carbon::parse($dt)->format('Y-m-d\TH:i') : '';
@endphp

<form id="{{ $formId }}" class="forms-sample voucher-form">
    @csrf
    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Kode Voucher</label>
            <input name="code" type="text" class="form-control text-uppercase" value="{{ optional($voucher)->code }}" placeholder="HEMAT10">
            <div data-error="code"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
        <div class="col-md-8 form-group">
            <label class="form-label">Nama Voucher</label>
            <input name="name" type="text" class="form-control" value="{{ optional($voucher)->name }}" placeholder="Diskon Survey Hemat">
            <div data-error="name"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Deskripsi (opsional)</label>
        <textarea name="description" rows="2" class="form-control" placeholder="Syarat & ketentuan singkat">{{ optional($voucher)->description }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Jenis Order</label>
            <select name="order_type" class="form-control js-order-type">
                <option value="service" @selected(optional($voucher)->order_type === 'service')>Jasa / Layanan</option>
                <option value="product" @selected(optional($voucher)->order_type === 'product')>Produk</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Tipe Diskon</label>
            <select name="discount_type" class="form-control js-discount-type">
                <option value="percentage" @selected(optional($voucher)->discount_type === 'percentage')>Persentase (%)</option>
                <option value="fixed" @selected(optional($voucher)->discount_type === 'fixed')>Potongan Langsung (Rp)</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Nilai Diskon</label>
            <input name="discount_value" type="number" min="1" class="form-control" value="{{ optional($voucher)->discount_value }}" placeholder="10">
            <small class="text-muted js-discount-hint"></small>
            <div data-error="discount_value"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group js-max-discount">
            <label class="form-label">Maksimal Potongan (Rp)</label>
            <input name="max_discount_amount" type="number" min="0" class="form-control" value="{{ optional($voucher)->max_discount_amount }}" placeholder="10000">
            <small class="text-muted">Untuk tipe persentase. Kosongkan = tanpa batas.</small>
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Min. Belanja (Rp)</label>
            <input name="min_purchase_amount" type="number" min="0" class="form-control" value="{{ optional($voucher)->min_purchase_amount ?? 0 }}" placeholder="0">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Kuota Total</label>
            <input name="usage_limit" type="number" min="1" class="form-control" value="{{ optional($voucher)->usage_limit }}" placeholder="Kosong = tak terbatas">
        </div>
        <div class="col-md-4 form-group">
            <label class="form-label">Maks. Klaim / User</label>
            <input name="usage_limit_per_user" type="number" min="1" class="form-control" value="{{ optional($voucher)->usage_limit_per_user ?? 1 }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label">Mulai Berlaku (opsional)</label>
            <input name="starts_at" type="datetime-local" class="form-control" value="{{ $fmt(optional($voucher)->starts_at) }}">
        </div>
        <div class="col-md-6 form-group">
            <label class="form-label">Kedaluwarsa (opsional)</label>
            <input name="expires_at" type="datetime-local" class="form-control" value="{{ $fmt(optional($voucher)->expires_at) }}">
            <div data-error="expires_at"><span class="text-danger" style="font-size:.8em"></span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Cakupan Item</label>
            <select name="item_scope" class="form-control js-item-scope">
                <option value="all" @selected(optional($voucher)->item_scope !== 'specific')>Semua item</option>
                <option value="specific" @selected(optional($voucher)->item_scope === 'specific')>Item tertentu</option>
            </select>
        </div>
        <div class="col-md-8 form-group js-service-picker">
            <label class="form-label">Layanan yang Berlaku</label>
            <select name="target_service_ids[]" class="form-control" multiple size="4">
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(in_array($service->id, $selectedServiceIds))>{{ $service->title }}</option>
                @endforeach
            </select>
            <small class="text-muted js-product-note d-none text-warning">Katalog produk belum tersedia — pemilihan item produk menyusul.</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="form-label">Target User</label>
            <select name="user_scope" class="form-control js-user-scope">
                <option value="all" @selected(optional($voucher)->user_scope !== 'specific')>Semua user</option>
                <option value="specific" @selected(optional($voucher)->user_scope === 'specific')>User tertentu</option>
            </select>
        </div>
        <div class="col-md-8 form-group js-user-picker">
            <label class="form-label">Pilih User</label>
            <select name="target_user_ids[]" class="form-control" multiple size="5">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(in_array($user->id, $selectedUserIds))>{{ $user->name }} — {{ $user->phone ?? $user->email }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-control">
            <option value="1" @selected(optional($voucher)->is_active ?? true)>Aktif</option>
            <option value="0" @selected($voucher && ! $voucher->is_active)>Nonaktif</option>
        </select>
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
            $select.select2({
                width: '100%',
                placeholder: placeholder,
                closeOnSelect: false,
                allowClear: true,
                dropdownParent: $modal.length ? $modal : $(document.body),
            });
        }

        const sync = function() {
            const orderType = $form.find('.js-order-type').val();
            const discountType = $form.find('.js-discount-type').val();
            const itemScope = $form.find('.js-item-scope').val();
            const userScope = $form.find('.js-user-scope').val();

            $form.find('.js-max-discount').toggle(discountType === 'percentage');
            $form.find('.js-discount-hint').text(discountType === 'percentage' ? 'dalam persen, mis. 10 = 10%' : 'dalam rupiah');

            const showService = itemScope === 'specific';
            $form.find('.js-service-picker').toggle(showService);
            $form.find('.js-product-note').toggleClass('d-none', orderType !== 'product');
            $form.find('.js-service-picker select').prop('disabled', orderType === 'product');
            if (showService) ensureSelect2($form.find('select[name="target_service_ids[]"]'), 'Pilih layanan…');

            const showUser = userScope === 'specific';
            $form.find('.js-user-picker').toggle(showUser);
            if (showUser) ensureSelect2($form.find('select[name="target_user_ids[]"]'), 'Cari & pilih user…');
        };

        $form.on('change', '.js-order-type, .js-discount-type, .js-item-scope, .js-user-scope', sync);
        sync();
    })();
</script>
