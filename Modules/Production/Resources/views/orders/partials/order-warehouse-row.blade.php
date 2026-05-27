@php
    $selectedRmWarehouseId = old('rm_warehouse_id', $selectedRmWarehouseId ?? null);
    $selectedFgWarehouseId = old('fg_warehouse_id', $selectedFgWarehouseId ?? null);
@endphp

<div class="row">
    <div class="col-md-6">
        <x-forms.select fieldId="rm_warehouse_id" :fieldLabel="__('production::app.rawMaterialWarehouse')" fieldName="rm_warehouse_id" fieldRequired="true">
            @foreach ($warehouses as $w)
                <option value="{{ $w->id }}" @selected((string) $selectedRmWarehouseId === (string) $w->id)>{{ $w->name }}</option>
            @endforeach
        </x-forms.select>
    </div>
    <div class="col-md-6">
        <x-forms.select fieldId="fg_warehouse_id" :fieldLabel="__('production::app.manufacturedProductWarehouse')" fieldName="fg_warehouse_id" fieldRequired="true">
            @foreach ($warehouses as $w)
                <option value="{{ $w->id }}" @selected((string) $selectedFgWarehouseId === (string) $w->id)>{{ $w->name }}</option>
            @endforeach
        </x-forms.select>
    </div>
</div>
