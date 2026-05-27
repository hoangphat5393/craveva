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

        const bomFirst = form.dataset.bomFirst === '1';
        const bomDisableFg = form.dataset.bomDisableFg === '1';

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

        function triggerPreviewRefresh() {
            $(document).trigger('production:bom-preview-refresh');
        }

        function syncFgByBom() {
            const selectedOption = $bomSelect.find('option:selected');
            const bomOutputProductId = selectedOption.data('output-product-id');
            const hasBom = Boolean($bomSelect.val());

            if (bomOutputProductId) {
                $fgSelect.val(String(bomOutputProductId));
                if (bomFirst && bomDisableFg) {
                    $fgSelect.prop('disabled', true);
                    upsertHiddenOutputField();
                }
                refreshPicker($fgSelect);
                triggerPreviewRefresh();

                return;
            }

            if (bomFirst && bomDisableFg) {
                $fgSelect.val('');
                $fgSelect.prop('disabled', true);
                removeHiddenOutputField();
                refreshPicker($fgSelect);
                triggerPreviewRefresh();

                return;
            }

            $fgSelect.prop('disabled', false);
            removeHiddenOutputField();
            refreshPicker($fgSelect);
            triggerPreviewRefresh();
        }

        $bomSelect.on('changed.bs.select change', syncFgByBom);
        syncFgByBom();
    })(jQuery);
</script>
