@php
    use App\Enums\ProductType;
@endphp

<script>
    window.purchaseProductTypesWithAlternateUom = @json(ProductType::alternateUnitConversionValues());

    window.purchaseProductTypeSupportsAlternateUom = function(type) {
        return window.purchaseProductTypesWithAlternateUom.indexOf((type || '').toString()) !== -1;
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

        window.togglePurchaseProductAlternateUomSection(type);
    };
</script>
