@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #102a43 0%, #1f6f8b 100%); color: #fff;">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <div class="mb-2 text-uppercase" style="letter-spacing: 1px; font-size: 12px; opacity: .8;">Mobile App Settings</div>
                    <h3 class="mb-2 text-white">Pengaturan Mobile</h3>
                    <p class="mb-0" style="max-width: 760px;">
                        Atur biaya survey, pajak, cakupan area survey, payment gateway, dan manual transfer dari dashboard ini supaya mobile app selalu mengikuti konfigurasi terbaru backend.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form id="mobile-settings-form" method="POST" action="{{ route('admin.mobile.settings.update') }}" class="row">
                    @csrf

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Harga Survey</label>
                        <input type="number" min="0" name="survey_fee" class="form-control @error('survey_fee') is-invalid @enderror" value="{{ old('survey_fee', $settings['survey_fee'] ?? 150000) }}">
                        @error('survey_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Biaya Konsultasi Event</label>
                        <input type="number" min="0" name="event_consultation_fee" class="form-control @error('event_consultation_fee') is-invalid @enderror" value="{{ old('event_consultation_fee', $settings['event_consultation_fee'] ?? 150000) }}">
                        @error('event_consultation_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pajak (%)</label>
                        <input type="number" min="0" max="100" name="tax_percentage" class="form-control @error('tax_percentage') is-invalid @enderror" value="{{ old('tax_percentage', $settings['tax_percentage'] ?? 0) }}">
                        @error('tax_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-2 mb-3">
                        <h5 class="mb-0">Payment Gateway</h5>
                        <small class="text-muted">Sementara fokus ke Midtrans sebagai gateway utama.</small>
                    </div>

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

                    <div class="col-12 mt-2 mb-3">
                        <h5 class="mb-0">Cakupan Area Survey</h5>
                        <small class="text-muted">Jika area user tidak masuk aturan ini, aplikasi akan mengarahkan ke WhatsApp untuk konfirmasi manual.</small>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
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
                                        @error('survey_coverage_whatsapp_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Pesan Default</label>
                                        <input type="text" name="survey_coverage_whatsapp_message" class="form-control @error('survey_coverage_whatsapp_message') is-invalid @enderror" value="{{ old('survey_coverage_whatsapp_message', data_get($surveyCoverage, 'whatsapp_message', '')) }}" placeholder="Alamat / wilayah yang Anda input untuk Survey di luar jangkauan kami. Silakan konsultasi dengan Tim Teknis kami untuk menyepakati proses Survey ke alamat yang sudah Anda input.">
                                        @error('survey_coverage_whatsapp_message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0 table-bordered" id="survey-coverage-table" style="min-width: 1200px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 56px;">#</th>
                                                <th style="min-width: 180px;">Nama Area</th>
                                                <th style="min-width: 170px;">Provinsi</th>
                                                <th style="min-width: 170px;">Kota / Kabupaten</th>
                                                <th style="width: 96px;">Sort</th>
                                                <th style="width: 120px;">Status</th>
                                                <th style="width: 90px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="survey-coverage-list">
                                            @php
                                                $surveyCoverageSource = old('survey_coverage_rules', data_get($surveyCoverage, 'rules', []));
                                            @endphp

                                            @forelse ($surveyCoverageSource as $index => $rule)
                                                @include('admin.pages.mobile.partials.survey-coverage-row', [
                                                    'index' => $index,
                                                    'rule' => $rule,
                                                ])
                                            @empty
                                                @include('admin.pages.mobile.partials.survey-coverage-row', [
                                                    'index' => 0,
                                                    'rule' => [],
                                                ])
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addSurveyCoverageRule">
                                        <i class="ri-add-line me-1"></i> Tambah Area
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2 mb-3">
                        <h5 class="mb-0">Manual Transfer</h5>
                        <small class="text-muted">Tambahkan beberapa rekening, aktif/nonaktifkan, edit, atau hapus sesuai kebutuhan.</small>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0 table-bordered" id="manual-transfer-table" style="min-width: 1180px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 56px;">#</th>
                                                <th style="min-width: 140px;">Nama Bank</th>
                                                <th style="min-width: 180px;">Nama Rekening</th>
                                                <th style="min-width: 160px;">Nomor Rekening</th>
                                                <th style="min-width: 170px;">Catatan</th>
                                                <th style="width: 96px;">Sort</th>
                                                <th style="width: 120px;">Status</th>
                                                <th style="width: 90px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="manual-transfer-list">
                                            @php
                                                $manualTransferSource = old('manual_transfers', $manualTransfers ?? []);
                                            @endphp

                                            @forelse ($manualTransferSource as $index => $transfer)
                                                @include('admin.pages.mobile.partials.manual-transfer-row', [
                                                    'index' => $index,
                                                    'transfer' => $transfer,
                                                ])
                                            @empty
                                                @include('admin.pages.mobile.partials.manual-transfer-row', [
                                                    'index' => 0,
                                                    'transfer' => [],
                                                ])
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addManualTransfer">
                                        <i class="ri-add-line me-1"></i> Tambah Rekening
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center" style="gap: 16px;">
                <div>
                    <h5 class="mb-1">Ringkasan Aktif</h5>
                    <p class="mb-0 text-muted">Nilai yang sedang dipakai oleh aplikasi mobile saat ini.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2">Survey: Rp{{ number_format((int) ($settings['survey_fee'] ?? 150000), 0, ',', '.') }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Konsultasi Event: Rp{{ number_format((int) ($settings['event_consultation_fee'] ?? 150000), 0, ',', '.') }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Pajak: {{ (int) ($settings['tax_percentage'] ?? 0) }}%</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Gateway: {{ data_get($settings, 'payment_gateway.provider', 'midtrans') }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Cakupan Area: {{ data_get($surveyCoverage, 'enabled', false) ? 'Aktif' : 'Nonaktif' }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Manual Transfer: {{ collect($manualTransfers ?? [])->where('is_active', true)->count() }}/{{ count($manualTransfers ?? []) }}</span>
                </div>
                <div class="ms-auto">
                    <button type="submit" form="mobile-settings-form" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan Pengaturan
                    </button>
                </div>
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

<script>
    (function($) {
        'use strict';

        const REGION_ENDPOINTS = {
            provinces: @json(route('admin.mobile.regions.provinces')),
            regencies: @json(route('admin.mobile.regions.regencies')),
        };
        const regionCache = {
            provinces: null,
            regencies: new Map(),
        };

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
            if (!$.fn.select2 || !$select.length) {
                return;
            }

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                dropdownAutoWidth: true,
                width: '100%',
            });
        }

        function fetchRegionOptions(url) {
            return fetch(url)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Gagal memuat data wilayah.');
                    }

                    return response.json();
                })
                .then((payload) => Array.isArray(payload?.data) ? payload.data.filter((item) => item && typeof item.code === 'string' && typeof item.name === 'string') : []);
        }

        async function loadProvinces() {
            if (!regionCache.provinces) {
                regionCache.provinces = await fetchRegionOptions(REGION_ENDPOINTS.provinces);
            }

            return regionCache.provinces;
        }

        async function loadRegencies(provinceCode) {
            if (!regionCache.regencies.has(provinceCode)) {
                const url = `${REGION_ENDPOINTS.regencies}?province_code=${encodeURIComponent(provinceCode)}`;
                regionCache.regencies.set(provinceCode, await fetchRegionOptions(url));
            }

            return regionCache.regencies.get(provinceCode) || [];
        }

        async function loadDistricts(regencyCode) {
            if (!regionCache.districts.has(regencyCode)) {
                const url = `${REGION_ENDPOINTS.districts}?regency_code=${encodeURIComponent(regencyCode)}`;
                regionCache.districts.set(regencyCode, await fetchRegionOptions(url));
            }

            return regionCache.districts.get(regencyCode) || [];
        }

        async function loadVillages(districtCode) {
            if (!regionCache.villages.has(districtCode)) {
                const url = `${REGION_ENDPOINTS.villages}?district_code=${encodeURIComponent(districtCode)}`;
                regionCache.villages.set(districtCode, await fetchRegionOptions(url));
            }

            return regionCache.villages.get(districtCode) || [];
        }

        function setOptions($select, placeholder, options, selectedValue) {
            const currentValue = selectedValue ?? $select.val() ?? '';
            const currentText = $select.find('option:selected').text();

            $select.empty();
            $select.append(new Option(placeholder, '', true, false));

            options.forEach((item) => {
                $select.append(new Option(item.name, item.code, false, false));
            });

            if (currentValue) {
                $select.val(currentValue);

                const $currentOption = $select.find(`option[value="${currentValue}"]`).first();
                if ($currentOption.length && currentText) {
                    $currentOption.text(currentText);
                }
            }

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change.select2');
            }
        }

        function updateHiddenLabel($row, level) {
            const $select = $row.find(`[data-survey-coverage-field="${level}_code"]`);
            const $hidden = $row.find(`[data-survey-coverage-field="${level}_name"]`);

            if (!$select.length || !$hidden.length) {
                return;
            }

            const selectedText = $select.find('option:selected').text() || '';
            $hidden.val(selectedText === 'Pilih provinsi' || selectedText === 'Pilih kota / kabupaten' || selectedText === 'Pilih kecamatan' || selectedText === 'Pilih kelurahan / desa' ? '' : selectedText);
        }

        function resetCoverageSelect($row, level) {
            const $select = $row.find(`[data-survey-coverage-field="${level}_code"]`);
            const $hidden = $row.find(`[data-survey-coverage-field="${level}_name"]`);

            if ($select.length) {
                $select.empty().append(new Option(getCoveragePlaceholder(level), '', true, false)).val('');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('change.select2');
                }
            }

            if ($hidden.length) {
                $hidden.val('');
            }
        }

        function getCoveragePlaceholder(level) {
            return {
                province: 'Pilih provinsi',
                regency: 'Pilih kota / kabupaten',
            }[level] || 'Pilih wilayah';
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

                if (!provinceCode) {
                    return;
                }

                const regencies = await loadRegencies(provinceCode);
                setOptions($regency, getCoveragePlaceholder('regency'), regencies, '');
            });

            $regency.on('change', async function () {
                updateHiddenLabel($row, 'regency');
                const regencyCode = $(this).val();
                if (!regencyCode) {
                    return;
                }
            });

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

        function countRows($container, selector) {
            return $container.find(selector).length;
        }

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
                if ($index.length) {
                    $index.text(`#${index + 1}`);
                }
            });
        }

        $addManualButton.on('click', function () {
            const html = $manualTemplate.html().replaceAll('__INDEX__', String(countRows($manualList, '[data-manual-transfer-row]')));
            const $row = $(html.trim());
            $manualList.append($row);
            reindexRows($manualList, '[data-manual-transfer-row]', '[data-manual-transfer-field]', '[data-manual-transfer-index]');
        });

        $manualList.on('click', '[data-remove-manual-transfer]', function () {
            const $row = $(this).closest('[data-manual-transfer-row]');
            if ($row.length) {
                $row.remove();
                reindexRows($manualList, '[data-manual-transfer-row]', '[data-manual-transfer-field]', '[data-manual-transfer-index]');
            }
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
            if ($row.length) {
                $row.remove();
                reindexRows($coverageList, '[data-survey-coverage-row]', '[data-survey-coverage-field]', '[data-survey-coverage-index]');
            }
        });

        $coverageList.find('[data-survey-coverage-row]').each(function () {
            bindCoverageRow(this);
        });

        reindexRows($manualList, '[data-manual-transfer-row]', '[data-manual-transfer-field]', '[data-manual-transfer-index]');
        reindexRows($coverageList, '[data-survey-coverage-row]', '[data-survey-coverage-field]', '[data-survey-coverage-index]');
    })(jQuery);
</script>
@endsection
