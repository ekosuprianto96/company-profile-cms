@php
    $transfer = $transfer ?? [];
    $rowNumber = is_numeric($index) ? ((int) $index + 1) : 1;
    $bankName = old("manual_transfers.$index.bank_name", data_get($transfer, 'bank_name', 'BCA'));
    $accountName = old("manual_transfers.$index.account_name", data_get($transfer, 'account_name', '-'));
    $accountNumber = old("manual_transfers.$index.account_number", data_get($transfer, 'account_number', '-'));
    $notes = old("manual_transfers.$index.notes", data_get($transfer, 'notes', ''));
    $isActive = old("manual_transfers.$index.is_active", data_get($transfer, 'is_active', true));
    $sortOrder = old("manual_transfers.$index.sort_order", data_get($transfer, 'sort_order', $rowNumber));
    $transferId = old("manual_transfers.$index.id", data_get($transfer, 'id', ''));
@endphp

<tr data-manual-transfer-row>
    <td class="text-center align-middle" style="width: 56px;">
        <span class="fw-bold" data-manual-transfer-index>#{{ $rowNumber }}</span>
        <input type="hidden" data-manual-transfer-field="id" value="{{ $transferId }}">
    </td>
    <td style="min-width: 140px;">
        <input type="text" class="form-control form-control-sm" data-manual-transfer-field="bank_name" value="{{ $bankName }}" placeholder="BCA">
    </td>
    <td style="min-width: 180px;">
        <input type="text" class="form-control form-control-sm" data-manual-transfer-field="account_name" value="{{ $accountName }}" placeholder="Admin Maninjau">
    </td>
    <td style="min-width: 160px;">
        <input type="text" class="form-control form-control-sm" data-manual-transfer-field="account_number" value="{{ $accountNumber }}" placeholder="1234567890">
    </td>
    <td style="min-width: 170px;">
        <input type="text" class="form-control form-control-sm" data-manual-transfer-field="notes" value="{{ $notes }}" placeholder="Catatan rekening">
    </td>
    <td style="width: 96px;">
        <input type="number" min="0" class="form-control form-control-sm" data-manual-transfer-field="sort_order" value="{{ $sortOrder }}">
    </td>
    <td style="width: 120px;">
        <select class="form-select form-select-sm" data-manual-transfer-field="is_active">
            <option value="1" {{ filter_var($isActive, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ !filter_var($isActive, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </td>
    <td class="text-center align-middle" style="width: 90px;">
        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-manual-transfer>
            <i class="ri-delete-bin-5-line"></i>
        </button>
    </td>
</tr>
