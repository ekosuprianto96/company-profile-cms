@php
    $rule = $rule ?? [];
    $rowNumber = is_numeric($index) ? ((int) $index + 1) : 1;
    $areaName = old("survey_coverage_rules.$index.area_name", data_get($rule, 'area_name', ''));

    $provinceCode = old("survey_coverage_rules.$index.province_code", data_get($rule, 'province.code', data_get($rule, 'province', '')));
    $provinceName = old("survey_coverage_rules.$index.province_name", data_get($rule, 'province.name', data_get($rule, 'province', '')));

    $regencyCode = old("survey_coverage_rules.$index.regency_code", data_get($rule, 'regency.code', data_get($rule, 'regency', '')));
    $regencyName = old("survey_coverage_rules.$index.regency_name", data_get($rule, 'regency.name', data_get($rule, 'regency', '')));

    $isActive = old("survey_coverage_rules.$index.is_active", data_get($rule, 'is_active', true));
    $sortOrder = old("survey_coverage_rules.$index.sort_order", data_get($rule, 'sort_order', $rowNumber));
    $ruleId = old("survey_coverage_rules.$index.id", data_get($rule, 'id', ''));
@endphp

<tr data-survey-coverage-row>
    <td class="text-center align-middle" style="width: 56px;">
        <span class="fw-bold" data-survey-coverage-index>#{{ $rowNumber }}</span>
        <input type="hidden" data-survey-coverage-field="id" value="{{ $ruleId }}">
    </td>
    <td style="min-width: 180px;">
        <input type="text" class="form-control form-control-sm" data-survey-coverage-field="area_name" value="{{ $areaName }}" placeholder="Area utama">
    </td>
    <td style="min-width: 170px;">
        <select class="form-select form-select-sm js-region-select" data-region-level="province" data-survey-coverage-field="province_code">
            @if ($provinceCode && $provinceName)
                <option value="{{ $provinceCode }}" selected>{{ $provinceName }}</option>
            @endif
        </select>
        <input type="hidden" data-survey-coverage-field="province_name" value="{{ $provinceName }}">
    </td>
    <td style="min-width: 170px;">
        <select class="form-select form-select-sm js-region-select" data-region-level="regency" data-survey-coverage-field="regency_code">
            @if ($regencyCode && $regencyName)
                <option value="{{ $regencyCode }}" selected>{{ $regencyName }}</option>
            @endif
        </select>
        <input type="hidden" data-survey-coverage-field="regency_name" value="{{ $regencyName }}">
    </td>
    <td style="width: 96px;">
        <input type="number" min="0" class="form-control form-control-sm" data-survey-coverage-field="sort_order" value="{{ $sortOrder }}">
    </td>
    <td style="width: 120px;">
        <select class="form-select form-select-sm" data-survey-coverage-field="is_active">
            <option value="1" {{ filter_var($isActive, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ !filter_var($isActive, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </td>
    <td class="text-center align-middle" style="width: 90px;">
        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-survey-coverage>
            <i class="ri-delete-bin-5-line"></i>
        </button>
    </td>
</tr>
