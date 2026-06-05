@php
    /** @var \Illuminate\Support\Collection<int, \Modules\Production\Entities\ProductionBom> $productionBoms */
    $productionBoms = $productionBoms ?? collect();
@endphp
@if (estimates_phase1_review_enabled() && \App\Services\Estimates\EstimateProductionBomCopier::moduleAvailable() && $productionBoms->isNotEmpty())
    <div class="mb-3 estimate-copy-production-bom">
        <x-forms.label fieldId="estimate_production_bom_id" :fieldLabel="__('modules.estimates.copyProductionBomHeading')" />
        <p class="f-12 text-lightest mb-2">@lang('modules.estimates.copyProductionBomHelp')</p>
        <x-forms.input-group>
            <select id="estimate_production_bom_id" class="form-control select-picker" data-live-search="true" data-size="8">
                <option value="">@lang('modules.estimates.copyProductionBomSelect')</option>
                @foreach ($productionBoms as $bom)
                    <option value="{{ $bom->id }}">{{ $bom->labelForSelect() }}</option>
                @endforeach
            </select>
            <x-slot name="append">
                <button type="button" id="estimate-copy-production-bom-btn" class="btn btn-outline-secondary border-grey height-35 f-14">
                    <i class="fa fa-download mr-1"></i>@lang('modules.estimates.copyProductionBomButton')
                </button>
            </x-slot>
        </x-forms.input-group>
    </div>

    <script>
        (function() {
            const btn = document.getElementById('estimate-copy-production-bom-btn');
            const select = document.getElementById('estimate_production_bom_id');
            const body = document.getElementById('estimate-bom-lines-body');

            if (!btn || !select || !body) {
                return;
            }

            const optionsTemplate = document.getElementById('estimate-bom-product-options-template');
            const materialPlaceholder = @json(__('modules.estimates.bomMaterialNamePlaceholder'));
            const unitMaps = Array.from(body.querySelectorAll('.estimate-bom-product-select')).map((el) => {
                try {
                    return JSON.parse(el.getAttribute('data-unit-map') || '{}');
                } catch (e) {
                    return {};
                }
            });
            const unitMap = unitMaps[0] || {};

            const recalcRow = (row) => {
                const qty = parseFloat(row.querySelector('.estimate-bom-quantity')?.value || '0');
                const cost = parseFloat(row.querySelector('.estimate-bom-unit-cost')?.value || '0');
                const totalCell = row.querySelector('.estimate-bom-line-total');
                if (totalCell) {
                    totalCell.textContent = (qty > 0 && cost >= 0) ? (qty * cost).toFixed(2) : '—';
                }
            };

            const recalcGrandTotal = () => {
                let sum = 0;
                body.querySelectorAll('.estimate-bom-line-row').forEach((row) => {
                    const qty = parseFloat(row.querySelector('.estimate-bom-quantity')?.value || '0');
                    const cost = parseFloat(row.querySelector('.estimate-bom-unit-cost')?.value || '0');
                    if (qty > 0 && cost >= 0) {
                        sum += qty * cost;
                    }
                });
                const el = document.getElementById('estimate-bom-material-total');
                if (el) {
                    el.textContent = sum.toFixed(2);
                }
            };

            const buildRowHtml = (line, index) => {
                let optionsHtml = optionsTemplate ? optionsTemplate.innerHTML : '<option value=""></option>';
                const productId = line.product_id ? String(line.product_id) : '';
                const unitId = line.unit_id ? String(line.unit_id) : '';
                const unitLabel = productId && unitMap[productId] ? unitMap[productId] : '—';
                const lineTotal = (parseFloat(line.quantity) * parseFloat(line.unit_cost)).toFixed(2);

                return `
                    <tr class="estimate-bom-line-row" data-row-index="${index}">
                        <td>
                            <input type="hidden" name="bom_line_id[]" value="">
                            <select name="bom_product_id[]" class="form-control select-picker f-14 estimate-bom-product-select" data-container="body" data-size="8" data-live-search="true" data-unit-map='${JSON.stringify(unitMap)}'>
                                ${optionsHtml.replace('value="' + productId + '"', 'value="' + productId + '" selected')}
                            </select>
                            <input type="text" name="bom_material_name[]" class="form-control f-14 mt-2 estimate-bom-material-name" placeholder="${materialPlaceholder}" value="${(line.material_name || '').replace(/"/g, '&quot;')}">
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="number" step="0.0001" min="0.0001" name="bom_quantity[]" class="form-control height-35 f-14 estimate-bom-quantity" value="${line.quantity}">
                                <div class="input-group-append">
                                    <span class="input-group-text height-35 f-13 text-dark-grey estimate-bom-unit-suffix">${unitLabel}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="number" step="0.0001" min="0" name="bom_unit_cost[]" class="form-control height-35 f-14 estimate-bom-unit-cost" value="${line.unit_cost}">
                        </td>
                        <td class="estimate-bom-line-total f-14 text-dark-grey align-middle text-right">${lineTotal}</td>
                        <td class="text-right align-middle">
                            <input type="hidden" name="bom_unit_id[]" class="estimate-bom-unit-id-input" value="${unitId}">
                            ${index > 0 ? '<button type="button" class="btn btn-outline-danger btn-sm estimate-bom-remove-row"><i class="fa fa-trash"></i></button>' : ''}
                        </td>
                    </tr>
                `;
            };

            btn.addEventListener('click', function() {
                const bomId = select.value;
                if (!bomId) {
                    return;
                }

                const url = "{{ route('estimates.production_bom_lines', ['bom' => ':id']) }}".replace(':id', bomId);
                $.easyBlockUI('.estimate-bom-lines-section');

                window.apiHttp.get(url).then(function(response) {
                    const lines = response.lines || response.data?.lines || [];
                    if (!lines.length) {
                        return;
                    }

                    body.innerHTML = '';
                    lines.forEach((line, index) => {
                        body.insertAdjacentHTML('beforeend', buildRowHtml(line, index));
                    });

                    if (typeof $ !== 'undefined' && $.fn.selectpicker) {
                        $(body).find('.select-picker').selectpicker('refresh');
                    }

                    body.querySelectorAll('.estimate-bom-line-row').forEach(recalcRow);
                    recalcGrandTotal();
                }).catch(function(err) {
                    $.handleApiFormError(err);
                }).finally(function() {
                    $.easyUnblockUI('.estimate-bom-lines-section');
                });
            });
        })();
    </script>
@endif
