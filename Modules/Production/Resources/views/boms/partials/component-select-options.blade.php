@php
    use App\Enums\ProductType;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Product>|iterable $componentProducts */
    $componentProductsByType = $componentProductsByType ?? collect($componentProducts ?? [])->groupBy('type');
    $selectedComponentId = isset($selectedComponentId) ? (string) $selectedComponentId : '';
    $bomComponentUnitByProductId = $bomComponentUnitByProductId ?? collect();

    $bomProductLabelWithUnit = static function ($product, \Illuminate\Support\Collection $unitByProductId): string {
        $u = (string) ($unitByProductId->get((string) $product->id) ?? ($unitByProductId->get((int) $product->id) ?? '—'));
        if ($u !== '' && $u !== '—') {
            return $product->name . ' (' . $u . ')';
        }

        return $product->name;
    };
@endphp
<option value="">—</option>
@foreach (ProductType::bomComponentCases() as $productType)
    @php
        $productsInGroup = $componentProductsByType->get($productType->value, collect());
    @endphp
    @if ($productsInGroup->isNotEmpty())
        <optgroup label="{{ $productType->label() }}">
            @foreach ($productsInGroup as $p)
                <option value="{{ $p->id }}" @selected($selectedComponentId !== '' && $selectedComponentId === (string) $p->id)>
                    {{ $bomProductLabelWithUnit($p, $bomComponentUnitByProductId) }}
                </option>
            @endforeach
        </optgroup>
    @endif
@endforeach
