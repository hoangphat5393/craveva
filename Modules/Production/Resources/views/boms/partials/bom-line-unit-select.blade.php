@php
    $rowIndex = $rowIndex ?? 0;
    $selectedUnitId = isset($selectedUnitId) ? (string) $selectedUnitId : '';
    $unitsForProduct = $unitsForProduct ?? [];
@endphp
<select name="items[{{ $rowIndex }}][unit_id]" class="form-control height-35 f-14 w-100 bom-line-unit-select">
    @if ($unitsForProduct === [])
        <option value="">—</option>
    @else
        @foreach ($unitsForProduct as $unit)
            <option value="{{ $unit['unit_id'] }}" @selected($selectedUnitId !== '' && $selectedUnitId === (string) $unit['unit_id'])>
                {{ $unit['label'] }}
            </option>
        @endforeach
    @endif
</select>
