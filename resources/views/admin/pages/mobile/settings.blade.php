@extends('admin.layouts.main')

@section('content')
@php
    $navItems = [
        ['id' => 'app', 'label' => 'Aplikasi', 'icon' => 'ri-smartphone-line', 'desc' => 'Nama aplikasi'],
        ['id' => 'onboarding', 'label' => 'Onboarding / Splash', 'icon' => 'ri-slideshow-3-line', 'desc' => 'Slide pembuka'],
        ['id' => 'fees', 'label' => 'Biaya & Pajak', 'icon' => 'ri-money-dollar-circle-line', 'desc' => 'Survey, konsultasi, pajak, OTP'],
        ['id' => 'payment', 'label' => 'Pembayaran', 'icon' => 'ri-bank-card-line', 'desc' => 'Gateway & transfer manual'],
        ['id' => 'invoice', 'label' => 'Invoice', 'icon' => 'ri-file-list-3-line', 'desc' => 'Template PDF'],
        ['id' => 'coverage', 'label' => 'Cakupan Survei', 'icon' => 'ri-map-pin-line', 'desc' => 'Area jangkauan survey'],
    ];
    $onboardingSource = old('onboarding_slides', $onboardingSlides ?? []);
@endphp

<style>
    .settings-nav .nav-link { color:#334155; border-radius:14px; padding:12px 14px; margin-bottom:10px; border:1px solid #eef1f4; background:#fff; transition:.15s; }
    .settings-nav .nav-link:last-child { margin-bottom:0; }
    .settings-nav .nav-link:hover { background:#eef5f4; border-color:#d7e7e4; }
    .settings-nav .nav-link i { color:#275a56; }
    .settings-nav .nav-link small { color:#94a3b8; }
    .settings-nav .nav-link.active { background:#275a56 !important; color:#fff !important; border-color:#275a56; box-shadow:0 8px 20px -10px rgba(39,90,86,.6); }
    .settings-nav .nav-link.active i, .settings-nav .nav-link.active small { color:#fff !important; opacity:.9; }
</style>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1a403d 0%, #275a56 100%); color:#fff;">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center" style="gap:16px;">
                <div>
                    <div class="mb-1 text-uppercase" style="letter-spacing:1px;font-size:12px;opacity:.8;">Mobile App Settings</div>
                    <h3 class="mb-1 text-white">Pengaturan Mobile</h3>
                    <p class="mb-0" style="max-width:720px;opacity:.9;">Kelola konfigurasi aplikasi mobile per bagian dari menu di kiri. Semua perubahan langsung dipakai aplikasi tanpa build ulang.</p>
                </div>
                <button type="submit" form="mobile-settings-form" class="btn btn-light text-primary fw-semibold">
                    <i class="ri-save-line me-1"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm border-0" style="position:sticky;top:16px;">
            <div class="card-body p-2">
                <div class="nav settings-nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                    @foreach ($navItems as $i => $item)
                        <button class="nav-link text-start {{ $i === 0 ? 'active' : '' }} d-flex align-items-start" style="gap:10px;" id="tab-{{ $item['id'] }}" data-bs-toggle="pill" data-bs-target="#pane-{{ $item['id'] }}" type="button" role="tab">
                            <i class="{{ $item['icon'] }}" style="font-size:18px;line-height:1.4;"></i>
                            <span>
                                <span class="d-block fw-semibold">{{ $item['label'] }}</span>
                                <small class="d-block opacity-75" style="font-size:11px;">{{ $item['desc'] }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="col-lg-9 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form id="mobile-settings-form" method="POST" action="{{ route('admin.mobile.settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="tab-content">

                        {{-- ====== APLIKASI ====== --}}
                        <div class="tab-pane fade show active" id="pane-app" role="tabpanel">
                            <h5 class="mb-3">Aplikasi</h5>
                            <div class="mb-3" style="max-width:520px;">
                                <label class="form-label">Nama Aplikasi</label>
                                <input type="text" maxlength="100" name="app_name" class="form-control @error('app_name') is-invalid @enderror" value="{{ old('app_name', $settings['app_name'] ?? 'Maninjau PRO') }}" placeholder="mis. Maninjau PRO">
                                <small class="text-muted">Ditampilkan di aplikasi (header login, judul daftar, dll). Tanpa build ulang.</small>
                                @error('app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- ====== ONBOARDING ====== --}}
                        <div class="tab-pane fade" id="pane-onboarding" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">Onboarding / Splash</h5>
                                    <small class="text-muted">Slide pembuka aplikasi (gambar + judul + subjudul). Ditarik dinamis oleh aplikasi.</small>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addOnboardingSlide"><i class="ri-add-line me-1"></i> Tambah Slide</button>
                            </div>
                            <div id="onboarding-list">
                                @forelse ($onboardingSource as $index => $slide)
                                    @include('admin.pages.mobile.partials.onboarding-slide-row', ['index' => $index, 'slide' => $slide])
                                @empty
                                    @include('admin.pages.mobile.partials.onboarding-slide-row', ['index' => 0, 'slide' => []])
                                @endforelse
                            </div>
                        </div>

                        {{-- ====== BIAYA & PAJAK ====== --}}
                        <div class="tab-pane fade" id="pane-fees" role="tabpanel">
                            <h5 class="mb-3">Biaya &amp; Pajak</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga Survey</label>
                                    <input type="number" min="0" name="survey_fee" class="form-control @error('survey_fee') is-invalid @enderror" value="{{ old('survey_fee', $settings['survey_fee'] ?? 150000) }}">
                                    @error('survey_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Biaya Konsultasi Event</label>
                                    <input type="number" min="0" name="event_consultation_fee" class="form-control @error('event_consultation_fee') is-invalid @enderror" value="{{ old('event_consultation_fee', $settings['event_consultation_fee'] ?? 150000) }}">
                                    @error('event_consultation_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pajak (%)</label>
                                    <input type="number" min="0" max="100" name="tax_percentage" class="form-control @error('tax_percentage') is-invalid @enderror" value="{{ old('tax_percentage', $settings['tax_percentage'] ?? 0) }}">
                                    @error('tax_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Masa Berlaku OTP (menit)</label>
                                    <input type="number" min="1" max="60" name="otp_expire_minutes" class="form-control @error('otp_expire_minutes') is-invalid @enderror" value="{{ old('otp_expire_minutes', $settings['otp_expire_minutes'] ?? 10) }}">
                                    <small class="text-muted d-block">OTP otomatis kadaluarsa setelah durasi ini.</small>
                                    @error('otp_expire_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- ====== PEMBAYARAN ====== --}}
                        <div class="tab-pane fade" id="pane-payment" role="tabpanel">
                            <h5 class="mb-3">Payment Gateway</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Aktifkan Payment Gateway</label>
                                    <select name="payment_gateway_enabled" class="form-select">
                                        <option value="1" {{ old('payment_gateway_enabled', data_get($settings, 'payment_gateway.enabled', true)) ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ !old('payment_gateway_enabled', data_get($settings, 'payment_gateway.enabled', true)) ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Provider Gateway</label>
                                    <select name="payment_gateway_provider" class="form-select">
                                        <option value="midtrans" {{ old('payment_gateway_provider', data_get($settings, 'payment_gateway.provider', 'midtrans')) === 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                                    </select>
                                </div>
                            </div>

                            <h5 class="mt-3 mb-1">Manual Transfer</h5>
                            <small class="text-muted d-block mb-3">Tambahkan beberapa rekening, aktif/nonaktifkan, edit, atau hapus.</small>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0 table-bordered" id="manual-transfer-table" style="min-width:1000px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:56px;">#</th>
                                            <th style="min-width:140px;">Nama Bank</th>
                                            <th style="min-width:180px;">Nama Rekening</th>
                                            <th style="min-width:160px;">Nomor Rekening</th>
                                            <th style="min-width:170px;">Catatan</th>
                                            <th style="width:96px;">Sort</th>
                                            <th style="width:120px;">Status</th>
                                            <th style="width:90px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="manual-transfer-list">
                                        @php $manualTransferSource = old('manual_transfers', $manualTransfers ?? []); @endphp
                                        @forelse ($manualTransferSource as $index => $transfer)
                                            @include('admin.pages.mobile.partials.manual-transfer-row', ['index' => $index, 'transfer' => $transfer])
                                        @empty
                                            @include('admin.pages.mobile.partials.manual-transfer-row', ['index' => 0, 'transfer' => []])
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addManualTransfer"><i class="ri-add-line me-1"></i> Tambah Rekening</button>
                            </div>
                        </div>

                        {{-- ====== INVOICE ====== --}}
                        <div class="tab-pane fade" id="pane-invoice" role="tabpanel">
                            <h5 class="mb-1">Template Invoice</h5>
                            <small class="text-muted d-block mb-3">Desain PDF invoice per jenis order. Kop mengikuti pengaturan aplikasi.</small>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Template Invoice Jasa / Layanan</label>
                                    <select name="invoice_template_service" class="form-select @error('invoice_template_service') is-invalid @enderror">
                                        @foreach (config('invoice.available', []) as $value => $label)
                                            <option value="{{ $value }}" {{ old('invoice_template_service', data_get($settings, 'invoice_template_service', config('invoice.templates.service'))) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('invoice_template_service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Template Invoice Order Produk</label>
                                    <select name="invoice_template_product" class="form-select @error('invoice_template_product') is-invalid @enderror">
                                        @foreach (config('invoice.available', []) as $value => $label)
                                            <option value="{{ $value }}" {{ old('invoice_template_product', data_get($settings, 'invoice_template_product', config('invoice.templates.product'))) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('invoice_template_product')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- ====== CAKUPAN SURVEI ====== --}}
                        <div class="tab-pane fade" id="pane-coverage" role="tabpanel">
                            <h5 class="mb-1">Cakupan Area Survey</h5>
                            <small class="text-muted d-block mb-3">Jika area user tidak masuk aturan ini, aplikasi mengarahkan ke WhatsApp untuk konfirmasi manual.</small>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Aktifkan Cakupan Area</label>
                                    <select name="survey_coverage_enabled" class="form-select">
                                        <option value="1" {{ old('survey_coverage_enabled', data_get($surveyCoverage, 'enabled', false)) ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ !old('survey_coverage_enabled', data_get($surveyCoverage, 'enabled', false)) ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nomor WhatsApp</label>
                                    <input type="text" name="survey_coverage_whatsapp_number" class="form-control @error('survey_coverage_whatsapp_number') is-invalid @enderror" value="{{ old('survey_coverage_whatsapp_number', data_get($surveyCoverage, 'whatsapp_number', '')) }}" placeholder="6281234567890">
                                    @error('survey_coverage_whatsapp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pesan Default</label>
                                    <input type="text" name="survey_coverage_whatsapp_message" class="form-control @error('survey_coverage_whatsapp_message') is-invalid @enderror" value="{{ old('survey_coverage_whatsapp_message', data_get($surveyCoverage, 'whatsapp_message', '')) }}" placeholder="Alamat di luar jangkauan...">
                                    @error('survey_coverage_whatsapp_message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0 table-bordered" id="survey-coverage-table" style="min-width:1000px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:56px;">#</th>
                                            <th style="min-width:180px;">Nama Area</th>
                                            <th style="min-width:170px;">Provinsi</th>
                                            <th style="min-width:170px;">Kota / Kabupaten</th>
                                            <th style="width:96px;">Sort</th>
                                            <th style="width:120px;">Status</th>
                                            <th style="width:90px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="survey-coverage-list">
                                        @php $surveyCoverageSource = old('survey_coverage_rules', data_get($surveyCoverage, 'rules', [])); @endphp
                                        @forelse ($surveyCoverageSource as $index => $rule)
                                            @include('admin.pages.mobile.partials.survey-coverage-row', ['index' => $index, 'rule' => $rule])
                                        @empty
                                            @include('admin.pages.mobile.partials.survey-coverage-row', ['index' => 0, 'rule' => []])
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addSurveyCoverageRule"><i class="ri-add-line me-1"></i> Tambah Area</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="manualTransferRowTemplate">
    @include('admin.pages.mobile.partials.manual-transfer-row', ['index' => '__INDEX__', 'transfer' => []])
</template>
<template id="surveyCoverageRowTemplate">
    @include('admin.pages.mobile.partials.survey-coverage-row', ['index' => '__INDEX__', 'rule' => []])
</template>
<template id="onboardingRowTemplate">
    @include('admin.pages.mobile.partials.onboarding-slide-row', ['index' => '__INDEX__', 'slide' => []])
</template>

<script>
    // ===== Onboarding slides (tambah/hapus/reindex + preview) =====
    (function($) {
        const $list = $('#onboarding-list');
        const $tpl = $('#onboardingRowTemplate');
        const $add = $('#addOnboardingSlide');
        if (!$list.length || !$tpl.length || !$add.length) return;

        function reindex() {
            $list.find('[data-onboarding-row]').each(function (i) {
                const $row = $(this);
                $row.find('[data-onboarding-field]').each(function () {
                    const f = $(this).data('onboarding-field');
                    $(this).attr('name', `onboarding_slides[${i}][${f}]`);
                });
                $row.find('[data-onboarding-image]').attr('name', `onboarding_images[${i}]`);
                $row.find('[data-onboarding-index]').text('Slide #' + (i + 1));
            });
        }

        $add.on('click', function () {
            const html = $tpl.html().replaceAll('__INDEX__', String($list.find('[data-onboarding-row]').length));
            $list.append($(html.trim()));
            reindex();
        });

        $list.on('click', '[data-remove-onboarding]', function () {
            $(this).closest('[data-onboarding-row]').remove();
            reindex();
        });

        $list.on('change', '[data-onboarding-image]', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            $(this).closest('[data-onboarding-row]').find('[data-onboarding-preview]')
                .html(`<img src="${url}" style="width:100%;height:100%;object-fit:cover;">`);
        });

        reindex();
    })(jQuery);
</script>

<script>
    (function($) {
        'use strict';

        const REGION_ENDPOINTS = {
            provinces: @json(route('admin.mobile.regions.provinces')),
            regencies: @json(route('admin.mobile.regions.regencies')),
        };
        const regionCache = { provinces: null, regencies: new Map() };

        const $manualList = $('#manual-transfer-list');
        const $manualTemplate = $('#manualTransferRowTemplate');
        const $addManualButton = $('#addManualTransfer');
        const $coverageList = $('#survey-coverage-list');
        const $coverageTemplate = $('#surveyCoverageRowTemplate');
        const $addCoverageButton = $('#addSurveyCoverageRule');

        if (!$manualList.length || !$manualTemplate.length || !$addManualButton.length || !$coverageList.length || !$coverageTemplate.length || !$addCoverageButton.length) {
            return;
        }

        function initSelect2($select) {
            if (!$.fn.select2 || !$select.length) return;
            if ($select.hasClass('select2-hidden-accessible')) return;
            $select.select2({ dropdownAutoWidth: true, width: '100%' });
        }

        function fetchRegionOptions(url) {
            return fetch(url)
                .then((response) => { if (!response.ok) { throw new Error('Gagal memuat data wilayah.'); } return response.json(); })
                .then((payload) => Array.isArray(payload?.data) ? payload.data.filter((item) => item && typeof item.code === 'string' && typeof item.name === 'string') : []);
        }

        async function loadProvinces() {
            if (!regionCache.provinces) { regionCache.provinces = await fetchRegionOptions(REGION_ENDPOINTS.provinces); }
            return regionCache.provinces;
        }

        async function loadRegencies(provinceCode) {
            if (!regionCache.regencies.has(provinceCode)) {
                const url = `${REGION_ENDPOINTS.regencies}?province_code=${encodeURIComponent(provinceCode)}`;
                regionCache.regencies.set(provinceCode, await fetchRegionOptions(url));
            }
            return regionCache.regencies.get(provinceCode) || [];
        }

        function setOptions($select, placeholder, options, selectedValue) {
            const currentValue = selectedValue ?? $select.val() ?? '';
            const currentText = $select.find('option:selected').text();
            $select.empty();
            $select.append(new Option(placeholder, '', true, false));
            options.forEach((item) => { $select.append(new Option(item.name, item.code, false, false)); });
            if (currentValue) {
                $select.val(currentValue);
                const $currentOption = $select.find(`option[value="${currentValue}"]`).first();
                if ($currentOption.length && currentText) { $currentOption.text(currentText); }
            }
            if ($select.hasClass('select2-hidden-accessible')) { $select.trigger('change.select2'); }
        }

        function updateHiddenLabel($row, level) {
            const $select = $row.find(`[data-survey-coverage-field="${level}_code"]`);
            const $hidden = $row.find(`[data-survey-coverage-field="${level}_name"]`);
            if (!$select.length || !$hidden.length) return;
            const selectedText = $select.find('option:selected').text() || '';
            $hidden.val(['Pilih provinsi', 'Pilih kota / kabupaten'].includes(selectedText) ? '' : selectedText);
        }

        function resetCoverageSelect($row, level) {
            const $select = $row.find(`[data-survey-coverage-field="${level}_code"]`);
            const $hidden = $row.find(`[data-survey-coverage-field="${level}_name"]`);
            if ($select.length) {
                $select.empty().append(new Option(getCoveragePlaceholder(level), '', true, false)).val('');
                if ($select.hasClass('select2-hidden-accessible')) { $select.trigger('change.select2'); }
            }
            if ($hidden.length) { $hidden.val(''); }
        }

        function getCoveragePlaceholder(level) {
            return { province: 'Pilih provinsi', regency: 'Pilih kota / kabupaten' }[level] || 'Pilih wilayah';
        }

        function bindCoverageRow(row) {
            const $row = $(row);
            const $province = $row.find('[data-survey-coverage-field="province_code"]');
            const $regency = $row.find('[data-survey-coverage-field="regency_code"]');
            [$province, $regency].forEach((select) => initSelect2(select));
            const initialProvince = $province.val();
            const initialRegency = $regency.val();

            $province.on('change', async function () {
                updateHiddenLabel($row, 'province');
                const provinceCode = $(this).val();
                resetCoverageSelect($row, 'regency');
                if (!provinceCode) return;
                const regencies = await loadRegencies(provinceCode);
                setOptions($regency, getCoveragePlaceholder('regency'), regencies, '');
            });

            $regency.on('change', function () { updateHiddenLabel($row, 'regency'); });

            void (async () => {
                const provinces = await loadProvinces();
                setOptions($province, getCoveragePlaceholder('province'), provinces, initialProvince);
                updateHiddenLabel($row, 'province');
                if (initialProvince) {
                    const regencies = await loadRegencies(initialProvince);
                    setOptions($regency, getCoveragePlaceholder('regency'), regencies, initialRegency);
                    updateHiddenLabel($row, 'regency');
                }
            })();
        }

        function countRows($container, selector) { return $container.find(selector).length; }

        function reindexRows($container, rowSelector, fieldSelector, indexSelector) {
            $container.find(rowSelector).each(function (index) {
                const $row = $(this);
                $row.find(fieldSelector).each(function () {
                    const $field = $(this);
                    const name = $field.data('manual-transfer-field') ?? $field.data('survey-coverage-field');
                    if (!name) return;
                    $field.attr('name', `${$container.attr('id') === 'manual-transfer-list' ? 'manual_transfers' : 'survey_coverage_rules'}[${index}][${name}]`);
                });
                const $index = $row.find(indexSelector);
                if ($index.length) { $index.text(`#${index + 1}`); }
            });
        }

        $addManualButton.on('click', function () {
            const html = $manualTemplate.html().replaceAll('__INDEX__', String(countRows($manualList, '[data-manual-transfer-row]')));
            $manualList.append($(html.trim()));
            reindexRows($manualList, '[data-manual-transfer-row]', '[data-manual-transfer-field]', '[data-manual-transfer-index]');
        });

        $manualList.on('click', '[data-remove-manual-transfer]', function () {
            const $row = $(this).closest('[data-manual-transfer-row]');
            if ($row.length) { $row.remove(); reindexRows($manualList, '[data-manual-transfer-row]', '[data-manual-transfer-field]', '[data-manual-transfer-index]'); }
        });

        $addCoverageButton.on('click', function () {
            const html = $coverageTemplate.html().replaceAll('__INDEX__', String(countRows($coverageList, '[data-survey-coverage-row]')));
            const $row = $(html.trim());
            $coverageList.append($row);
            bindCoverageRow($row);
            reindexRows($coverageList, '[data-survey-coverage-row]', '[data-survey-coverage-field]', '[data-survey-coverage-index]');
        });

        $coverageList.on('click', '[data-remove-survey-coverage]', function () {
            const $row = $(this).closest('[data-survey-coverage-row]');
            if ($row.length) { $row.remove(); reindexRows($coverageList, '[data-survey-coverage-row]', '[data-survey-coverage-field]', '[data-survey-coverage-index]'); }
        });

        $coverageList.find('[data-survey-coverage-row]').each(function () { bindCoverageRow(this); });

        reindexRows($manualList, '[data-manual-transfer-row]', '[data-manual-transfer-field]', '[data-manual-transfer-index]');
        reindexRows($coverageList, '[data-survey-coverage-row]', '[data-survey-coverage-field]', '[data-survey-coverage-index]');
    })(jQuery);
</script>
@endsection
