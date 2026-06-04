@php
    use App\Enums\ProductType;
@endphp

<script>
    window.purchaseProductTypesWithAlternateUom = @json(ProductType::alternateUnitConversionValues());
    window.purchaseProductTypesCostOnlyUom = @json(ProductType::costOnlyPurchasePricingValues());
    window.purchaseProductTypesSellOnlyPricing = @json(ProductType::sellOnlyPurchasePricingValues());
    window.purchaseProductTypesSupportsCostFromBom = @json([ProductType::Goods->value]);
    window.purchaseProductTypesHideB2bExtraPricing = @json(array_values(array_unique(array_merge(ProductType::costOnlyPurchasePricingValues(), [ProductType::Service->value]))));
    window.purchaseProductTypesOptionalPricingTaxAccordion = @json(ProductType::values());
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

    window.purchaseProductTypeSupportsCostFromBom = function(type) {
        return window.purchaseProductTypeInList(type, window.purchaseProductTypesSupportsCostFromBom);
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

    window.syncPurchaseProductCostFromBomField = function(type) {
        const supports = window.purchaseProductTypeSupportsCostFromBom(type);
        const $toggle = $('.product-cost-from-bom-toggle');
        const $costField = $('#purchase_price');
        const $requiredStar = $('.product-cost-price-required');
        const $pendingHint = $('.product-cost-from-bom-pending-hint');

        if (!supports) {
            $toggle.addClass('d-none');
            $('#cost_from_bom').prop('checked', false);
            $costField.prop('disabled', false);
            $requiredStar.removeClass('d-none');
            $pendingHint.addClass('d-none');

            return;
        }

        $toggle.removeClass('d-none');
        const customOn = $('#cost_from_bom').prop('checked');

        if (customOn) {
            $costField.prop('disabled', true);
            $requiredStar.addClass('d-none');
            $pendingHint.removeClass('d-none');
        } else {
            $costField.prop('disabled', false);
            $requiredStar.removeClass('d-none');
            $pendingHint.addClass('d-none');
        }
    };

    window.togglePurchaseProductTypeFields = function(type) {
        const hideSellingPrice = window.purchaseProductTypeUsesCostUom(type);
        const hideCostPrice = window.purchaseProductTypeHidesCostPrice(type);
        const hideB2bExtra = window.purchaseProductTypeInList(type, window.purchaseProductTypesHideB2bExtraPricing);
        const useOptionalPricingTaxAccordion = window.purchaseProductTypeInList(type, window.purchaseProductTypesOptionalPricingTaxAccordion);
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
        $('.product-cost-price-column').toggleClass('d-none', hideCostPrice);
        $('.product-optional-pricing-tax-block').toggleClass('d-none', !useOptionalPricingTaxAccordion);
        $('.product-b2b-extra-pricing-fields').toggleClass('d-none', hideB2bExtra);
        $('.product-optional-pricing-tax-divider').toggleClass('d-none', hideB2bExtra);
        $('.product-client-purchase-toggle').toggleClass('d-none', hideClientPurchase);
        $('.product-inventory-section').toggleClass('d-none', hideInventorySection);
        $('.product-unit-type-field').toggleClass('d-none', hideUnitType);
        $('.product-inventory-metadata').toggleClass('d-none', hideInventoryMetadata);
        $('.product-media-section').toggleClass('d-none', hideMedia);
        $('.product-description-attributes').toggleClass('d-none', hideDescriptionAttributes);

        if (hideCostPrice) {
            $('#cost_from_bom').prop('checked', false);
            $('#purchase_price').val('').prop('disabled', false);
        }

        window.syncPurchaseProductCostFromBomField(type);
        window.togglePurchaseProductAlternateUomSection(type);

        if (typeof window.refreshPurchaseProductUomPricingMode === 'function') {
            window.refreshPurchaseProductUomPricingMode();
        }
    };

    $(document).on('change', '#cost_from_bom', function() {
        window.syncPurchaseProductCostFromBomField($('#type').val());
    });
</script>
