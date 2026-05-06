@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Product>|array $finishedGoods */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Product>|array $componentProducts */
    /** @var \Modules\Production\Entities\ProductionBom|null $bom */

    $lines = old('items');
    if (!is_array($lines)) {
        $lines = [];
    }
    if ($lines === [] && isset($bom) && $bom->relationLoaded('items')) {
        $lines = $bom->items
            ->map(
                static fn($it) => [
                    'component_product_id' => $it->component_product_id,
                    'quantity' => $it->quantity,
                ],
            )
            ->all();
    }
    $rowCount = max(count($lines), 12);
@endphp

<div class="form-group my-3">
    <x-forms.select fieldId="output_product_id" :fieldLabel="__('production::app.fgProduct')" fieldName="output_product_id" fieldRequired="true">
        <option value="">—</option>
        @foreach ($finishedGoods as $p)
            <option value="{{ $p->id }}" @selected((int) old('output_product_id', isset($bom) ? $bom->output_product_id : 0) === (int) $p->id)>{{ $p->name }}</option>
        @endforeach
    </x-forms.select>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <x-forms.label fieldId="version" :fieldLabel="__('production::app.bomVersion')" fieldRequired="true" />
        <input type="text" name="version" id="version" class="form-control height-35 f-14" maxlength="32" required value="{{ old('version', isset($bom) ? $bom->version : '') }}">
    </div>
    <div class="form-group col-md-3">
        <x-forms.label fieldId="code" :fieldLabel="__('production::app.bomCode')" fieldRequired="false" />
        <input type="text" name="code" id="code" class="form-control height-35 f-14" maxlength="64" value="{{ old('code', isset($bom) ? $bom->code ?? '' : '') }}">
    </div>
    <div class="form-group col-md-3">
        <x-forms.label fieldId="effective_from" :fieldLabel="__('production::app.bomEffectiveFrom')" fieldRequired="false" />
        <input type="date" name="effective_from" id="effective_from" class="form-control height-35 f-14" value="{{ old('effective_from', isset($bom) && $bom->effective_from ? $bom->effective_from->format('Y-m-d') : '') }}">
    </div>
    <div class="form-group col-md-3">
        <x-forms.label fieldId="effective_to" :fieldLabel="__('production::app.bomEffectiveTo')" fieldRequired="false" />
        <input type="date" name="effective_to" id="effective_to" class="form-control height-35 f-14" value="{{ old('effective_to', isset($bom) && $bom->effective_to ? $bom->effective_to->format('Y-m-d') : '') }}">
    </div>
</div>

<div class="form-group my-2">
    <input type="hidden" name="is_default" value="0" />
    <x-forms.checkbox :checked="(bool) old('is_default', isset($bom) ? $bom->is_default : false)" :fieldLabel="__('production::app.bomDefault')" fieldName="is_default" fieldId="is_default" fieldValue="1" />
</div>

<div class="form-group my-3">
    <x-forms.label fieldId="notes" :fieldLabel="__('production::app.bomNotes')" fieldRequired="false" />
    <textarea name="notes" id="notes" class="form-control f-14" rows="2" maxlength="2000">{{ old('notes', isset($bom) ? $bom->notes ?? '' : '') }}</textarea>
</div>

<h6 class="f-14 text-dark-grey font-weight-bold mb-2">@lang('production::app.bomLines')</h6>
<div class="table-responsive bg-white rounded border">
    <table class="table table-sm mb-0">
        <thead>
            <tr class="f-14 text-dark-grey">
                <th>@lang('production::app.componentProduct')</th>
                <th style="width: 160px;">@lang('production::app.bomComponentQty')</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $rowCount; $i++)
                @php
                    $line = $lines[$i] ?? [];
                    $cid = old("items.$i.component_product_id", $line['component_product_id'] ?? '');
                    $qty = old("items.$i.quantity", $line['quantity'] ?? '');
                @endphp
                <tr>
                    <td>
                        <select name="items[{{ $i }}][component_product_id]" class="form-control select-picker f-14" data-container="body" data-size="8">
                            <option value="">—</option>
                            @foreach ($componentProducts as $p)
                                <option value="{{ $p->id }}" @selected((string) $cid === (string) $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.0001" min="0.0001" name="items[{{ $i }}][quantity]" class="form-control height-35 f-14" value="{{ $qty }}">
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
