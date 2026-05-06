<script>
    (function($) {
        'use strict';

        const $fgSelect = $('#output_product_id');
        const $bomSelect = $('#production_bom_id');

        if (!$fgSelect.length || !$bomSelect.length) {
            return;
        }

        const form = $fgSelect.closest('form').get(0);
        if (!form) {
            return;
        }

        function upsertHiddenOutputField() {
            let hidden = document.getElementById('output_product_id_hidden');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'output_product_id';
                hidden.id = 'output_product_id_hidden';
                form.appendChild(hidden);
            }

            hidden.value = $fgSelect.val() || '';
        }

        function removeHiddenOutputField() {
            const hidden = document.getElementById('output_product_id_hidden');
            if (hidden) {
                hidden.remove();
            }
        }

        function refreshPicker($select) {
            if ($.fn.selectpicker) {
                $select.selectpicker('refresh');
            }
        }

        function syncFgByBom() {
            const selectedOption = $bomSelect.find('option:selected');
            const bomOutputProductId = selectedOption.data('output-product-id');

            if (bomOutputProductId) {
                $fgSelect.val(String(bomOutputProductId));
                $fgSelect.prop('disabled', true);
                upsertHiddenOutputField();
                refreshPicker($fgSelect);

                return;
            }

            $fgSelect.prop('disabled', false);
            removeHiddenOutputField();
            refreshPicker($fgSelect);
        }

        $bomSelect.on('changed.bs.select change', syncFgByBom);
        syncFgByBom();
    })(jQuery);
</script>
