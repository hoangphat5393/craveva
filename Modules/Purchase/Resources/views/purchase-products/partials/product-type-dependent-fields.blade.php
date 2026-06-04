@php
    use App\Enums\ProductType;
@endphp

<script>
    window.purchaseProductTypesWithAlternateUom = @json(ProductType::alternateUnitConversionValues());
    window.purchaseProductTypesCostOnlyUom = @json(ProductType::costOnlyPurchasePricingValues());
    window.purchaseProductTypesSellOnlyPricing = @json(ProductType::sellOnlyPurchasePricingValues());

    window.purchaseProductTypeSupportsAlternateUom = function(type) {
        return window.purchaseProductTypesWithAlternateUom.indexOf((type || '').toString()) !== -1;
    };

    window.purchaseProductTypeUsesCostUom = function(type) {
        return window.purchaseProductTypesCostOnlyUom.indexOf((type || '').toString()) !== -1;
    };

    window.purchaseProductTypeHidesCostPrice = function(type) {
        return window.purchaseProductTypesSellOnlyPricing.indexOf((type || '').toString()) !== -1;
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

    window.togglePurchaseProductTypeFields = function(type) {
        const hideSellingPrice = window.purchaseProductTypeUsesCostUom(type);
        const hideCostPrice = typeof window.purchaseProductTypeHidesCostPrice === 'function'
            && window.purchaseProductTypeHidesCostPrice(type);

        if (type === 'service') {
            $('#sku_id').addClass('d-none');
            $('#track_inventory').prop('checked', false);
            $('#opening_stock, #rate_per_unit').val('');
            $('.track_inventory').addClass('d-none');
            $('.track_inventory_div').addClass('d-none');
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
        $('.product-purchase-information-toggle').toggleClass('d-none', hideCostPrice);
        $('.product-cost-price-column').toggleClass('d-none', hideCostPrice || !$('#purchase_information').prop('checked'));

        if (hideCostPrice) {
            $('#purchase_information').prop('checked', false);
            $('#purchase_price').val('');
        }

        window.togglePurchaseProductAlternateUomSection(type);

        if (typeof window.refreshPurchaseProductUomPricingMode === 'function') {
            window.refreshPurchaseProductUomPricingMode();
        }
    };
</script>
