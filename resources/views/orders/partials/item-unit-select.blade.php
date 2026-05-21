@php
    $lineUnits = $sellableUnits ?? [];
    $selectedUnitId = (int) ($selectedUnitId ?? 0);
    $multipleUnits = count($lineUnits) > 1;
@endphp

@if ($multipleUnits)
    <select class="form-control f-12 border-0 text-dark-grey float-right order-line-unit-select select-picker" name="unit_id[]" data-product-id="{{ $productId ?? '' }}" data-size="5" data-width="fit">
        @foreach ($lineUnits as $unitOption)
            <option value="{{ $unitOption['unit_id'] }}" data-unit-price="{{ $unitOption['unit_price'] ?? '' }}" @selected((int) $unitOption['unit_id'] === $selectedUnitId)>
                {{ $unitOption['label'] }}
            </option>
        @endforeach
    </select>
@elseif (count($lineUnits) === 1)
    <span class="text-dark-grey float-right border-0 f-12">{{ $lineUnits[0]['label'] }}</span>
    <input type="hidden" name="unit_id[]" value="{{ $lineUnits[0]['unit_id'] }}">
@else
    <span class="text-dark-grey float-right border-0 f-12">{{ $fallbackUnitLabel ?? '—' }}</span>
    @if ($selectedUnitId > 0)
        <input type="hidden" name="unit_id[]" value="{{ $selectedUnitId }}">
    @endif
@endif
