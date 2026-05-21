@php
    $rowIndex = $index;
    $unitId = $row['unit_id'] ?? null;
    $factor = isset($row['factor_to_base']) ? (float) $row['factor_to_base'] : 1;
    $sellingPrice = $row['selling_price'] ?? null;
    $forSale = $row === null ? true : (bool) ($row['for_sale'] ?? true);
    $baseUnitId = isset($product) && $product ? (int) $product->unit_id : 0;
@endphp
<tr data-row-index="{{ $rowIndex }}">
    <td>
        <select class="form-control select-picker unit-conversion-unit-select" name="unit_conversion_unit_id[{{ $rowIndex }}]" data-container="body" data-live-search="true" data-size="8">
            <option value="">--</option>
            @foreach ($unitTypes as $unitType)
                @if (!isset($product) || (int) $unitType->id !== $baseUnitId)
                    <option value="{{ $unitType->id }}" @selected($unitId !== null && (int) $unitType->id === (int) $unitId)>{{ ucwords($unitType->unit_type) }}</option>
                @endif
            @endforeach
        </select>
    </td>
    <td>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text f-12">=</span>
            </div>
            <input type="number" step="any" min="0.000001" class="form-control unit-conversion-factor" name="unit_conversion_factor[{{ $rowIndex }}]" value="{{ $factor }}" placeholder="1">
            <div class="input-group-append">
                <span class="input-group-text f-12 unit-conversion-base-label">—</span>
            </div>
        </div>
    </td>
    <td>
        <input type="number" step="any" min="0" class="form-control unit-conversion-selling-price" name="unit_conversion_selling_price[{{ $rowIndex }}]" value="{{ $sellingPrice !== null ? $sellingPrice : '' }}" data-custom-override="{{ $sellingPrice !== null ? '1' : '0' }}">
        <span class="badge badge-warning f-10 mt-1 unit-conversion-custom-price-badge @if ($sellingPrice === null) d-none @endif">@lang('purchase::app.productUnitCustomPrice')</span>
    </td>
    <td class="text-center align-middle">
        <input type="hidden" name="unit_conversion_for_sale[{{ $rowIndex }}]" value="0">
        <input type="checkbox" name="unit_conversion_for_sale[{{ $rowIndex }}]" value="1" @checked($forSale)>
    </td>
    <td class="text-center align-middle">
        <button type="button" class="btn btn-outline-danger btn-sm remove-unit-conversion-row" title="@lang('app.remove')">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>
