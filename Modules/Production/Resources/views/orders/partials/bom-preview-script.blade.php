@php
    use Modules\Production\Support\ProductionBomFirstPolicy;
@endphp

@if (ProductionBomFirstPolicy::showBomPreviewOnOrderForm())
    <script>
        (function($) {
            'use strict';

            const $panel = $('#production-order-bom-preview');
            if (!$panel.length) {
                return;
            }

            const previewUrl = $panel.data('preview-url');
            const placeholder = $panel.data('placeholder') || '';
            const $form = $panel.closest('form');
            let debounceTimer = null;

            function showPlaceholder() {
                $panel.html('<p class="f-13 text-muted mb-0">' + $('<div>').text(placeholder).html() + '</p>');
            }

            function refreshBomPreview() {
                const bomId = $('#production_bom_id').val();
                const plannedQty = $('#planned_quantity').val();
                const rmWarehouseId = $('#rm_warehouse_id').val();

                if (!bomId || !plannedQty || parseFloat(plannedQty) <= 0) {
                    showPlaceholder();

                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    $.get(previewUrl, {
                        production_bom_id: bomId,
                        planned_quantity: plannedQty,
                        rm_warehouse_id: rmWarehouseId || '',
                    }).done(function(response) {
                        if (response && response.status === 'success' && response.html) {
                            $panel.html(response.html);
                        }
                    }).fail(function() {
                        showPlaceholder();
                    });
                }, 280);
            }

            $form.on('changed.bs.select change input', '#production_bom_id, #planned_quantity, #rm_warehouse_id', refreshBomPreview);
            $(document).on('production:bom-preview-refresh', refreshBomPreview);

            refreshBomPreview();
        })(jQuery);
    </script>
@endif
