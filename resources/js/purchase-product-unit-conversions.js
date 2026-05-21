/**
 * Purchase product alternate UOM rows (create/edit).
 * Call after selectpicker init: initPurchaseProductUnitConversions($formOrModalRoot)
 */
module.exports = function initPurchaseProductUnitConversions($root) {
    const $rootEl = $root && $root.jquery ? $root : $($root || document);
    const $section = $rootEl.find('#product-unit-conversions-section').first();

    if (!$section.length) {
        return;
    }

    if ($section.data('uomInitialized')) {
        refreshAddButton();
        return;
    }

    $section.data('uomInitialized', true);

    const $form = $section.closest('form');
    const $body = $section.find('#product-unit-conversions-body');
    const $addBtn = $section.find('#add-product-unit-conversion');
    const $hint = $section.find('#product-unit-conversions-hint');
    const $templateHost = $form.find('#product-unit-conversion-row-template').first();
    const rowTemplate = (
        $templateHost.find('tbody').first().html() || $templateHost.html() || ''
    ).trim();
    let rowIndex = $body.find('tr').length;

    let unitOptions = [];
    try {
        unitOptions = JSON.parse($section.attr('data-unit-options') || '[]');
    } catch (e) {
        unitOptions = [];
    }

    const addRowBlockedMsg = $section.attr('data-blocked-msg') || '';

    function readUnitFieldValue($unitField) {
        let v = $unitField.val();
        if (Array.isArray(v)) {
            v = v[0];
        }
        if ((!v || v === '') && typeof $unitField.selectpicker === 'function') {
            v = $unitField.selectpicker('val');
            if (Array.isArray(v)) {
                v = v[0];
            }
        }

        return v;
    }

    function baseUnitId() {
        const $unitField = $form.find('#unit_type_id').first();
        if (!$unitField.length) {
            return 0;
        }

        const v = readUnitFieldValue($unitField);

        return v ? parseInt(v, 10) : 0;
    }

    function baseUnitLabel() {
        const id = baseUnitId();
        const found = unitOptions.find((u) => parseInt(u.id, 10) === id);

        return found ? found.label : '';
    }

    function baseSellingPrice() {
        const $price = $form.find('#selling_price').first();
        if (!$price.length) {
            return 0;
        }

        const v = parseFloat($price.val());

        return Number.isFinite(v) && v > 0 ? v : 0;
    }

    function canAddConversionRow() {
        return baseUnitId() > 0 && baseSellingPrice() > 0;
    }

    function refreshAddButton() {
        const ok = canAddConversionRow();
        $addBtn.prop('disabled', !ok);
        $hint.toggleClass('d-none', ok);
    }

    function derivedPrice(factor, overridePrice) {
        if (overridePrice !== null && overridePrice !== '' && !Number.isNaN(parseFloat(overridePrice))) {
            return parseFloat(overridePrice);
        }

        return Math.round(baseSellingPrice() * factor * 10000) / 10000;
    }

    function refreshFactorLabels() {
        const label = baseUnitLabel();
        $body.find('.unit-conversion-base-label').text(label || '—');
    }

    function reindexRows() {
        $body.find('tr').each(function (i) {
            $(this).attr('data-row-index', i);
            $(this)
                .find('[name^="unit_conversion_"]')
                .each(function () {
                    const name = $(this).attr('name') || '';
                    const match = name.match(/^(unit_conversion_\w+)\[\d+\]/);
                    if (match) {
                        $(this).attr('name', match[1] + '[' + i + ']');
                    }
                });
        });
        rowIndex = $body.find('tr').length;
    }

    function bindRowEvents($row) {
        const $factor = $row.find('.unit-conversion-factor');
        const $price = $row.find('.unit-conversion-selling-price');
        const $customBadge = $row.find('.unit-conversion-custom-price-badge');

        function syncPriceFromFactor() {
            if ($price.data('custom-override') === 1) {
                return;
            }
            const factor = parseFloat($factor.val()) || 0;
            if (factor > 0) {
                $price.val(derivedPrice(factor, null));
            }
        }

        $factor.on('input change', syncPriceFromFactor);

        $price.on('input', function () {
            const factor = parseFloat($factor.val()) || 0;
            const expected = derivedPrice(factor, null);
            const current = parseFloat($price.val());
            if (Math.abs(current - expected) > 0.0001) {
                $price.data('custom-override', 1);
                $customBadge.removeClass('d-none');
            } else {
                $price.data('custom-override', 0);
                $customBadge.addClass('d-none');
            }
        });

        $row.find('.remove-unit-conversion-row').on('click', function () {
            $row.remove();
            reindexRows();
            refreshAddButton();
        });

        $row.find('.unit-conversion-unit-select').on('changed.bs.select change', function () {
            const selected = parseInt($(this).val(), 10);
            if (selected === baseUnitId()) {
                $(this).val('').selectpicker('refresh');
            }
        });
    }

    $addBtn.off('click.purchaseUom').on('click.purchaseUom', function () {
        if (!canAddConversionRow()) {
            if (typeof Swal !== 'undefined' && addRowBlockedMsg) {
                Swal.fire({
                    icon: 'info',
                    text: addRowBlockedMsg,
                    toast: true,
                    position: 'top-end',
                    timer: 5000,
                    showConfirmButton: false,
                });
            }

            return;
        }

        if (!rowTemplate) {
            console.error('Product UOM row template is empty');

            return;
        }

        const html = rowTemplate.replace(/__INDEX__/g, String(rowIndex));
        const $row = $('<table><tbody>' + html + '</tbody></table>').find('tr').first();
        if (!$row.length) {
            console.error('Product UOM row template did not produce a table row');

            return;
        }

        $body.append($row);
        $row.find('.select-picker').selectpicker({ container: 'body' });
        refreshFactorLabels();
        bindRowEvents($row);
        const $factor = $row.find('.unit-conversion-factor');
        if (!$factor.val()) {
            $factor.val('1');
        }
        $row.find('.unit-conversion-selling-price').val(derivedPrice(parseFloat($factor.val()) || 1, null));
        rowIndex++;
        refreshAddButton();
    });

    $form
        .find('#unit_type_id, #selling_price')
        .off('change.purchaseUom input.purchaseUom changed.bs.select.purchaseUom')
        .on('change.purchaseUom input.purchaseUom changed.bs.select.purchaseUom', function () {
            refreshAddButton();
            refreshFactorLabels();
            $body.find('tr').each(function () {
                const $row = $(this);
                const $price = $row.find('.unit-conversion-selling-price');
                if ($price.data('custom-override') !== 1) {
                    const factor = parseFloat($row.find('.unit-conversion-factor').val()) || 0;
                    if (factor > 0) {
                        $price.val(derivedPrice(factor, null));
                    }
                }
            });
        });

    $body.find('tr').each(function () {
        const $row = $(this);
        const $unitSelect = $row.find('.unit-conversion-unit-select.select-picker');

        if ($unitSelect.length) {
            if ($unitSelect.data('selectpicker')) {
                $unitSelect.selectpicker('destroy');
            }

            $unitSelect.selectpicker({ container: 'body' });
        }

        bindRowEvents($row);
    });

    refreshFactorLabels();
    refreshAddButton();

    $form.find('#unit_type_id').one('shown.bs.select', refreshAddButton);
    setTimeout(refreshAddButton, 0);
    setTimeout(refreshAddButton, 350);
};
