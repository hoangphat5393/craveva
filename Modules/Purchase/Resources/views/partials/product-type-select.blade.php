@php
    use App\Enums\ProductType;

    $selectedType = old('type', isset($product) && filled($product?->type) ? $product->type : ProductType::Goods->value);
    $fieldId = $fieldId ?? 'type';
    $fieldName = $fieldName ?? 'type';
@endphp

<x-forms.select :fieldId="$fieldId" :fieldLabel="__('purchase::modules.product.type')" :fieldName="$fieldName" fieldRequired="true">
    @foreach (ProductType::casesForUi() as $productType)
        <option value="{{ $productType->value }}" @selected($selectedType === $productType->value)>
            {{ $productType->label() }}
        </option>
    @endforeach
</x-forms.select>
