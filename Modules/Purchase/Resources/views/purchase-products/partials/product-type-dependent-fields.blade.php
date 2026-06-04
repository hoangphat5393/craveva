@php
    use App\Enums\ProductType;
@endphp

<script>
    window.purchaseProductTypesWithAlternateUom = @json(ProductType::alternateUnitConversionValues());
    window.purchaseProductTypesCostOnlyUom = @json(ProductType::costOnlyPurchasePricingValues());
    window.purchaseProductTypesSellOnlyPricing = @json(ProductType::sellOnlyPurchasePricingValues());
    window.purchaseProductTypesForcePurchaseInfo = @json(ProductType::costOnlyPurchasePricingValues());
    window.purchaseProductTypesHideB2bExtraPricing = @json(array_values(array_unique(array_merge(ProductType::costOnlyPurchasePricingValues(), [ProductType::Service->value]))));
    window.purchaseProductTypesCollapseB2bExtraPricing = @json([ProductType::Goods->value]);
    window.purchaseProductTypesTaxAccordion = @json(ProductType::values());
    window.purchaseProductTypesHideClientPurchase = @json(ProductType::costOnlyPurchasePricingValues());
    window.purchaseProductTypesHideInventoryMetadata = @json(ProductType::costOnlyPurchasePricingValues());
    window.purchaseProductTypesHideInventorySection = @json([ProductType::Service->value]);
    window.purchaseProductTypesHideUnitType = @json([ProductType::Service->value]);
    window.purchaseProductTypesHideProductMedia = @json(array_values(array_unique(array_merge(ProductType::costOnlyPurchasePricingValues(), [ProductType::Service->value]))));
    window.purchaseProductTypesHideDescriptionAttributes = @json(array_values(array_unique(array_merge(ProductType::costOnlyPurchasePricingValues(), [ProductType::Service->value]))));

    window.purchaseProductTypeInList = function(type, list) {
        return (list || []).indexOf((type || '').toString()) !== -1;
    };

    window.purchaseProductTypeSupportsAlternateUom = function(type) {
        return window.purchaseProductTypeInList(type, window.purchaseProductTypesWithAlternateUom);
    };

    window.purchaseProductTypeUsesCostUom = function(type) {
        return window.purchaseProductTypeInList(type, window.purchaseProductTypesCostOnlyUom);
    };

    window.purchaseProductTypeHidesCostPrice = function(type) {
        return window.purchaseProductTypeInList(type, window.purchaseProductTypesSellOnlyPricing);
    };

    window.purchaseProductTypeForcesPurchaseInfo = function(type) {
        return window.purchaseProductTypeInList(type, window.purchaseProductTypesForcePurchaseInfo);
    };

    window.togglePurchaseProductAlternateUomSection = function(type) {
        const $section = $('#product-unit-conversions-section');
        if (!$section.length) {
            return;
        }

        if (window.purchaseProductTypeSupportsAlternateUom(type)) {
            $section.removeClass('d-none');
        } else {
            $section.addClass('d-none');
        }
    };

    window.syncPurchaseProductForcePurchaseInfo = function(type) {
        const forces = window.purchaseProductTypeForcesPurchaseInfo(type);
        const $form = $('#save-product-form');
        $form.find('input[type="hidden"][name="purchase_information"]').remove();

        if (forces) {
            $('#purchase_information').prop('checked', true);
            $('<input>', {
                type: 'hidden',
                name: 'purchase_information',
                value: '1'
            }).prependTo($form);
            $('.product-cost-price-column').removeClass('d-none');
        }
    };

    window.togglePurchaseProductTypeFields = function(type) {
        const hideSellingPrice = window.purchaseProductTypeUsesCostUom(type);
        const hideCostPrice = typeof window.purchaseProductTypeHidesCostPrice === 'function' &&
            window.purchaseProductTypeHidesCostPrice(type);
        const forcesPurchaseInfo = window.purchaseProductTypeForcesPurchaseInfo(type);
        const hideB2bExtra = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideB2bExtraPricing);
        const collapseB2bExtra = window.purchaseProductTypeInList(type, window.purchaseProductTypesCollapseB2bExtraPricing);
        const useTaxAccordion = window.purchaseProductTypeInList(type, window.purchaseProductTypesTaxAccordion);
        const hideClientPurchase = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideClientPurchase);
        const hideInventoryMetadata = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideInventoryMetadata);
        const hideInventorySection = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideInventorySection);
        const hideUnitType = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideUnitType);
        const hideMedia = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideProductMedia);
        const hideDescriptionAttributes = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideDescriptionAttributes);

        if (type === 'service') {
            $('#sku_id').addClass('d-none');
            $('#track_inventory').prop('checked', false);
            $('#opening_stock, #rate_per_unit').val('');
        } else {
            $('#sku_id').removeClass('d-none');
            $('.track_inventory_div').removeClass('d-none');
            if ($('#track_inventory').prop('checked') === true) {
                $('.track_inventory').removeClass('d-none');
            } else {
                $('.track_inventory').addClass('d-none');
            }
        }

        $('.product-selling-price-column').toggleClass('d-none', hideSellingPrice);
        $('.product-purchase-information-toggle').toggleClass('d-none', hideCostPrice || forcesPurchaseInfo);
        $('.product-cost-price-column').toggleClass(
            'd-none',
            hideCostPrice || (!forcesPurchaseInfo && !$('#purchase_information').prop('checked')),
        );
        $('.product-b2b-extra-pricing-block').toggleClass('d-none', hideB2bExtra);
        $('.product-b2b-collapse-toggle').toggleClass('d-none', !collapseB2bExtra || hideB2bExtra);
        const $b2bWrap = $('#productB2bPricingCollapse');
        $b2bWrap.toggleClass('collapse', collapseB2bExtra && !hideB2bExtra);
        $b2bWrap.toggleClass('show', !collapseB2bExtra && !hideB2bExtra);
        $('.product-form-section-tax').toggleClass('product-form-section-tax--accordion', useTaxAccordion);
        $('.product-tax-section-heading').toggleClass('d-none', useTaxAccordion);
        $('.product-tax-accordion-toggle').toggleClass('d-none', !useTaxAccordion);
        const $taxWrap = $('#productTaxCollapse');
        $taxWrap.toggleClass('collapse', useTaxAccordion);
        $taxWrap.toggleClass('show', !useTaxAccordion);
        $('.product-client-purchase-toggle').toggleClass('d-none', hideClientPurchase);
        $('.product-inventory-section').toggleClass('d-none', hideInventorySection);
        $('.product-unit-type-field').toggleClass('d-none', hideUnitType);
        $('.product-inventory-metadata').toggleClass('d-none', hideInventoryMetadata);
        $('.product-media-section').toggleClass('d-none', hideMedia);
        $('.product-description-attributes').toggleClass('d-none', hideDescriptionAttributes);

        if (hideCostPrice) {
            $('#purchase_information').prop('checked', false);
            $('#purchase_price').val('');
        }

        window.syncPurchaseProductForcePurchaseInfo(type);
        window.togglePurchaseProductAlternateUomSection(type);

        if (typeof window.refreshPurchaseProductUomPricingMode === 'function') {
            window.refreshPurchaseProductUomPricingMode();
        }
    };
</script>
