@php
    use Modules\Production\Support\ProductionBomFirstPolicy;
    use Modules\Production\Support\ProductionProductSelectLabel;

    $bomFirst = ProductionBomFirstPolicy::enabled();
    $bomRequired = ProductionBomFirstPolicy::requireBomOnOrder();
    $fgDisabledByPolicy = $bomFirst && ProductionBomFirstPolicy::bomFirstDisableFgSelect();
    $defaultOutputProductId = $defaultOutputProductId ?? null;
    $defaultBomId = $defaultBomId ?? null;
    $bomPlaceholderSelected = $defaultBomId === null || $defaultBomId === '';
@endphp

@if ($bomFirst)
    <div class="row">
        <div class="col-md-6">
            <x-forms.select fieldId="production_bom_id" :search="true" :fieldLabel="__('production::app.bom')" fieldName="production_bom_id" :fieldRequired="$bomRequired">
                <option value="" @selected($bomPlaceholderSelected)>@lang('production::app.bomSelectPlaceholder')</option>
                @foreach ($boms as $bom)
                    <option value="{{ $bom->id }}" data-output-product-id="{{ $bom->output_product_id }}" @selected((string) $defaultBomId === (string) $bom->id)>
                        {{ $bom->labelForSelect() }}
                    </option>
                @endforeach
            </x-forms.select>
            <p class="f-12 text-muted mt-12 mb-0">
                @lang('production::app.bomManageFromSettingsHint')
                <a href="{{ route('production.boms.index') }}">@lang('production::app.menuBillOfMaterials')</a>
            </p>
        </div>
        <div class="col-md-6">
            <x-forms.select fieldId="output_product_id" :search="true" :fieldLabel="__('production::app.manufacturedProduct')" fieldName="output_product_id" fieldRequired="true">
                <option value="">—</option>
                @foreach ($finishedGoods as $p)
                    @php
                        $fgSelectLabel = ProductionProductSelectLabel::forProduct($p);
                        $fgSku = trim((string) ($p->sku ?? ''));
                    @endphp
                    <option value="{{ $p->id }}" data-content="{{ $fgSelectLabel }}" @if ($fgSku !== '') data-tokens="{{ $fgSku }}" @endif @selected((string) $defaultOutputProductId === (string) $p->id)>{{ $fgSelectLabel }}</option>
                @endforeach
            </x-forms.select>
            <p class="f-12 text-dark-grey mb-0 mt-2">@lang('production::app.manufacturedProductFromBom')</p>
            @if ($bomRequired)
                <p class="f-12 text-muted mb-0 mt-1">@lang('production::app.bomSelectFirstHint')</p>
            @endif
        </div>
    </div>
@else
    <div class="row">
        <div class="col-md-6">
            <x-forms.select fieldId="output_product_id" :search="true" :fieldLabel="__('production::app.manufacturedProduct')" fieldName="output_product_id" fieldRequired="true">
                <option value="">—</option>
                @foreach ($finishedGoods as $p)
                    @php
                        $fgSelectLabel = ProductionProductSelectLabel::forProduct($p);
                        $fgSku = trim((string) ($p->sku ?? ''));
                    @endphp
                    <option value="{{ $p->id }}" data-content="{{ $fgSelectLabel }}" @if ($fgSku !== '') data-tokens="{{ $fgSku }}" @endif @selected((string) $defaultOutputProductId === (string) $p->id)>{{ $fgSelectLabel }}</option>
                @endforeach
            </x-forms.select>
        </div>
        <div class="col-md-6">
            <x-forms.select fieldId="production_bom_id" :search="true" :fieldLabel="__('production::app.bom') . ' (' . __('app.optional') . ')'" fieldName="production_bom_id" :fieldRequired="false">
                <option value="">—</option>
                @foreach ($boms as $bom)
                    <option value="{{ $bom->id }}" data-output-product-id="{{ $bom->output_product_id }}" @selected((string) $defaultBomId === (string) $bom->id)>
                        {{ $bom->labelForSelect() }}
                    </option>
                @endforeach
            </x-forms.select>
        </div>
    </div>
    <p class="f-12 text-muted mt-12 mb-0">
        @lang('production::app.bomManageFromSettingsHint')
        <a href="{{ route('production.boms.index') }}">@lang('production::app.menuBillOfMaterials')</a>
    </p>
@endif
