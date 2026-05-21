@php
    $rowIndex = $rowIndex ?? 0;
    $componentProductId = $componentProductId ?? '';
    $componentProductName = $componentProductName ?? '—';
    $lineUnitId = isset($lineUnitId) ? (string) $lineUnitId : '';
    $qty = $qty ?? '';
    $waste = $waste ?? 0;
    $unitsForRow = $unitsForRow ?? [];
    $lineUnitCost = $lineUnitCost ?? '—';
    $lineExtendedCost = $lineExtendedCost ?? '—';
@endphp
<tr class="bom-line-row" data-row-index="{{ $rowIndex }}" data-product-id="{{ $componentProductId }}" data-unit-id="{{ $lineUnitId }}">
    <td class="align-middle">
        <span class="f-14 text-dark-grey bom-line-product-name">{{ $componentProductName }}</span>
        <input type="hidden" name="items[{{ $rowIndex }}][component_product_id]" class="bom-line-component-id" value="{{ $componentProductId }}">
    </td>
    <td class="align-middle">
        @include('production::boms.partials.bom-line-unit-select', [
            'rowIndex' => $rowIndex,
            'selectedUnitId' => $lineUnitId,
            'unitsForProduct' => $unitsForRow,
        ])
    </td>
    <td>
        <input type="number" step="0.0001" min="0.0001" name="items[{{ $rowIndex }}][quantity]" class="form-control height-35 f-14 bom-line-quantity" value="{{ $qty }}">
    </td>
    <td>
        <input type="number" step="0.01" min="0" max="100" name="items[{{ $rowIndex }}][waste_percent]" class="form-control height-35 f-14 bom-line-waste" value="{{ $waste }}">
    </td>
    <td class="bom-line-unit-cost f-14 text-dark-grey align-middle text-right">{{ $lineUnitCost }}</td>
    <td class="bom-line-extended-cost f-14 text-dark-grey align-middle text-right">{{ $lineExtendedCost }}</td>
    <td class="text-right align-middle">
        <button type="button" class="btn btn-outline-danger btn-sm bom-remove-row" title="@lang('app.delete')">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>
