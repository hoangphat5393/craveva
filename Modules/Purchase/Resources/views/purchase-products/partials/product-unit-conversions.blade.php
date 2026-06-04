@php
    use App\Enums\ProductType;

    $uomEnabled = ($productUnitConversionsEnabled ?? false) && isset($unit_types);
    $uomRows = $productUnitConversionRows ?? [];
    $currencyCode = company()->currency->currency_code;
    $unitOptionsJson = isset($unit_types) ? $unit_types->map(fn($u) => ['id' => $u->id, 'label' => ucwords($u->unit_type)])->values() : collect();
    $selectedProductType = old('type', isset($product) && filled($product?->type) ? $product->type : ProductType::Goods->value);
    $showUomSection = ProductType::supportsAlternateUnitConversions($selectedProductType);
    $uomUsesCostColumn = ProductType::uomPriceColumnUsesCost($selectedProductType);
@endphp

@if ($uomEnabled)
    <div class="col-12 purchase-product-form-section @unless ($showUomSection) d-none @endunless" id="product-unit-conversions-section" data-unit-options="{{ $unitOptionsJson->toJson() }}" data-blocked-msg-sell="{{ e(__('purchase::app.productUnitAddRowBlocked')) }}" data-blocked-msg-cost="{{ e(__('purchase::app.productUnitAddRowBlockedCost')) }}"
        data-hint-sell="{{ e(__('purchase::app.productUnitAddRowHint')) }}" data-hint-cost="{{ e(__('purchase::app.productUnitAddRowHintCost')) }}" data-help-sell="{{ e(__('purchase::app.productUnitConversionsHelp')) }}" data-help-cost="{{ e(__('purchase::app.productUnitConversionsHelpCost')) }}" data-label-sell="{{ e(__('purchase::app.sellingPrice')) }}"
        data-label-cost="{{ e(__('purchase::app.costPrice')) }}" data-custom-label-sell="{{ e(__('purchase::app.productUnitCustomPrice')) }}" data-custom-label-cost="{{ e(__('purchase::app.productUnitCustomCost')) }}">
        @include('purchase::purchase-products.partials.product-form-section-heading', ['title' => __('purchase::app.productFormSectionUnits')])
        <p class="text-muted f-12 mb-3" id="product-unit-conversions-help">@lang($uomUsesCostColumn ? 'purchase::app.productUnitConversionsHelpCost' : 'purchase::app.productUnitConversionsHelp')</p>

        <div class="table-responsive">
            <table class="table table-bordered" id="product-unit-conversions-table">
                <thead class="bg-additional-grey">
                    <tr>
                        <th>@lang('purchase::app.productUnitColumnUnit')</th>
                        <th>@lang('purchase::app.productUnitColumnFactor')</th>
                        <th><span id="product-uom-price-column-label">@lang($uomUsesCostColumn ? 'purchase::app.costPrice' : 'purchase::app.sellingPrice')</span> ({{ $currencyCode }})</th>
                        <th class="text-center product-uom-for-sale-column @if ($uomUsesCostColumn) d-none @endif">@lang('purchase::app.productUnitColumnForSale')</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody id="product-unit-conversions-body">
                    @foreach ($uomRows as $index => $row)
                        @include('purchase::purchase-products.partials.product-unit-conversion-row', [
                            'index' => $index,
                            'row' => $row,
                            'unitTypes' => $unit_types,
                            'currencyCode' => $currencyCode,
                            'product' => $product ?? null,
                            'uomUsesCostColumn' => $uomUsesCostColumn,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="button" class="btn btn-sm btn-outline-primary border-grey mt-2" id="add-product-unit-conversion" disabled>
            @lang('purchase::app.productUnitAddRow')
        </button>
        <small class="text-muted d-block mt-1" id="product-unit-conversions-hint">@lang('purchase::app.productUnitAddRowHint')</small>

        {{-- <tr> must live under <table><tbody>; a bare <tr> inside <div> is stripped by the browser. --}}
        <div id="product-unit-conversion-row-template" class="d-none" aria-hidden="true">
            <table class="d-none">
                <tbody>
                    @include('purchase::purchase-products.partials.product-unit-conversion-row', [
                        'index' => '__INDEX__',
                        'row' => null,
                        'unitTypes' => $unit_types,
                        'currencyCode' => $currencyCode,
                        'product' => $product ?? null,
                        'uomUsesCostColumn' => $uomUsesCostColumn,
                    ])
                </tbody>
            </table>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function() {
                    function purchaseProductUomForm() {
                        return $('#save-product-data-form, #save-product-form').filter(function() {
                            return $(this).find('#product-unit-conversions-section').length;
                        }).first();
                    }

                    function bootPurchaseProductUnitConversions(forceReinit) {
                        const $form = purchaseProductUomForm();
                        if (!$form.length || typeof window.initPurchaseProductUnitConversions !== 'function') {
                            return;
                        }
                        if (forceReinit) {
                            $form.find('#product-unit-conversions-section').removeData('uomInitialized');
                        }
                        window.initPurchaseProductUnitConversions($form);
                    }

                    $(function() {
                        bootPurchaseProductUnitConversions(false);
                    });

                    // Full-page edit: layouts/app calls window.init() on window.load and re-inits selectpickers.
                    $(window).on('load.purchaseProductUom', function() {
                        bootPurchaseProductUnitConversions(true);
                    });
                })
                ();
            </script>
        @endpush
    @endonce
@endif
