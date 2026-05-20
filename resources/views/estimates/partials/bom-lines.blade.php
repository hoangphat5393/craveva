@php
    use App\Enums\ProductType;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Product>|null $bomComponentProducts */
    /** @var \App\Models\Estimate|null $estimate */
    /** @var bool $readOnly */

    $readOnly = $readOnly ?? false;
    $bomComponentProducts = $bomComponentProducts ?? collect();
    $units = $units ?? \App\Models\UnitType::all();

    $bomUnitByProductId = $bomComponentProducts->keyBy('id')->map(static fn($product) => optional($product->unit)->unit_type ?? '');
    $unitLabelById = $units->keyBy('id')->map(static fn($unit) => $unit->unit_type ?? '');

    $lines = [];
    if (isset($estimate) && $estimate->relationLoaded('bomLines') && $estimate->bomLines->isNotEmpty()) {
        $lines = $estimate->bomLines
            ->map(
                static fn($line) => [
                    'id' => $line->id,
                    'product_id' => $line->product_id,
                    'material_name' => $line->material_name,
                    'quantity' => $line->quantity,
                    'unit_id' => $line->unit_id,
                    'unit_cost' => $line->unit_cost,
                    'line_total' => $line->line_total,
                    'notes' => $line->notes,
                ],
            )
            ->all();
    }

    if (!$readOnly) {
        $oldLines = old('bom_material_name');
        if (is_array($oldLines) && $oldLines !== []) {
            $lines = [];
            $productIds = (array) old('bom_product_id', []);
            $quantities = (array) old('bom_quantity', []);
            $unitIds = (array) old('bom_unit_id', []);
            $unitCosts = (array) old('bom_unit_cost', []);
            $lineIds = (array) old('bom_line_id', []);
            $notesList = (array) old('bom_notes', []);
            foreach ($oldLines as $index => $materialName) {
                $lines[] = [
                    'id' => $lineIds[$index] ?? null,
                    'product_id' => $productIds[$index] ?? '',
                    'material_name' => $materialName,
                    'quantity' => $quantities[$index] ?? '',
                    'unit_id' => $unitIds[$index] ?? '',
                    'unit_cost' => $unitCosts[$index] ?? '',
                    'line_total' => '',
                    'notes' => $notesList[$index] ?? '',
                ];
            }
        }
    }

    $rowCount = max(count($lines), $readOnly ? 0 : 1);
    $bomMaterialTotal = collect($lines)->sum(static fn($line) => (float) ($line['line_total'] ?? (float) ($line['quantity'] ?? 0) * (float) ($line['unit_cost'] ?? 0)));
@endphp

<div class="col-md-12 estimate-bom-lines-section my-3">
    <h5 class="f-16 font-weight-bold text-dark-grey mb-1">@lang('modules.estimates.bomLinesHeading')</h5>
    <p class="f-12 text-dark-grey mb-3">@lang('modules.estimates.bomLinesHelp')</p>

    @if ($readOnly)
        @if ($rowCount === 0)
            <p class="f-13 text-dark-grey mb-0">@lang('modules.estimates.bomLinesEmpty')</p>
        @else
            <div class="table-responsive bg-white rounded border">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr class="f-14 text-dark-grey">
                            <th>@lang('modules.estimates.bomMaterial')</th>
                            <th class="text-right" style="width: 140px;">@lang('modules.estimates.bomQuantity')</th>
                            <th class="text-right" style="width: 120px;">@lang('modules.estimates.bomUnitCost')</th>
                            <th class="text-right" style="width: 120px;">@lang('modules.estimates.bomLineTotal')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            @php
                                $lineTotal = (float) ($line['line_total'] ?? (float) ($line['quantity'] ?? 0) * (float) ($line['unit_cost'] ?? 0));
                                $unitLabel = '';
                                if (isset($estimate)) {
                                    $bomLineModel = $estimate->bomLines->firstWhere('id', $line['id'] ?? null);
                                    $unitLabel = (string) ($bomLineModel?->unit?->unit_type ?? ($unitLabelById->get((int) ($line['unit_id'] ?? 0)) ?? ''));
                                }
                                $qtyDisplay = trim($line['quantity'] . ' ' . ($unitLabel !== '' ? $unitLabel : ''));
                            @endphp
                            <tr class="f-13">
                                <td>{{ $line['material_name'] }}</td>
                                <td class="text-right">{{ $qtyDisplay }}</td>
                                <td class="text-right">{{ currency_format($line['unit_cost'], $estimate->currency_id ?? company()->currency_id, false) }}</td>
                                <td class="text-right">{{ currency_format($lineTotal, $estimate->currency_id ?? company()->currency_id, false) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold f-13 text-dark-grey">
                            <td colspan="3" class="text-right">@lang('modules.estimates.bomMaterialTotal')</td>
                            <td class="text-right">{{ currency_format($bomMaterialTotal, $estimate->currency_id ?? company()->currency_id, false) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @else
        @include('estimates.partials.copy-production-bom', [
            'productionBoms' => $productionBoms ?? collect(),
            'estimate' => $estimate ?? null,
        ])
        @if ($bomComponentProducts->isEmpty())
            <div class="alert alert-warning f-13 mb-2">@lang('modules.estimates.bomNoComponents')</div>
        @endif
        <div class="table-responsive bg-white rounded border">
            <table class="table table-sm mb-0">
                <thead>
                    <tr class="f-14 text-dark-grey">
                        <th>@lang('modules.estimates.bomMaterial')</th>
                        <th style="width: 160px;">@lang('modules.estimates.bomQuantity')</th>
                        <th style="width: 130px;">@lang('modules.estimates.bomUnitCost')</th>
                        <th style="width: 130px;">@lang('modules.estimates.bomLineTotal')</th>
                        <th style="width: 70px;">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody id="estimate-bom-lines-body">
                    @for ($i = 0; $i < $rowCount; $i++)
                        @php
                            $line = $lines[$i] ?? [];
                            $lineId = $line['id'] ?? '';
                            $productId = old("bom_product_id.$i", $line['product_id'] ?? '');
                            $materialName = old("bom_material_name.$i", $line['material_name'] ?? '');
                            $quantity = old("bom_quantity.$i", $line['quantity'] ?? '');
                            $unitId = old("bom_unit_id.$i", $line['unit_id'] ?? '');
                            $unitCost = old("bom_unit_cost.$i", $line['unit_cost'] ?? '');
                            $lineNote = old("bom_notes.$i", $line['notes'] ?? '');
                            $lineUom = $productId !== '' && $productId !== null ? (string) ($bomUnitByProductId->get((string) $productId) ?? ($bomUnitByProductId->get((int) $productId) ?? '')) : (string) ($unitLabelById->get((int) $unitId) ?? '');
                            $computedLineTotal = is_numeric($quantity) && is_numeric($unitCost) ? round((float) $quantity * (float) $unitCost, 4) : '';
                        @endphp
                        <tr class="estimate-bom-line-row" data-row-index="{{ $i }}">
                            <td>
                                <input type="hidden" name="bom_line_id[]" value="{{ $lineId }}">
                                <select name="bom_product_id[]" class="form-control select-picker f-14 estimate-bom-product-select" data-container="body" data-size="8" data-live-search="true" data-unit-map='@json($bomUnitByProductId->all())'>
                                    <option value="">@lang('modules.estimates.bomSelectProduct')</option>
                                    @foreach ($bomComponentProducts as $product)
                                        <option value="{{ $product->id }}" data-unit-id="{{ $product->unit_id }}" @selected((string) $productId === (string) $product->id)>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="bom_material_name[]" class="form-control f-14 mt-2 estimate-bom-material-name" placeholder="@lang('modules.estimates.bomMaterialNamePlaceholder')" value="{{ $materialName }}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" step="0.0001" min="0.0001" name="bom_quantity[]" class="form-control height-35 f-14 estimate-bom-quantity" value="{{ $quantity }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text height-35 f-13 text-dark-grey estimate-bom-unit-suffix">{{ $lineUom !== '' ? $lineUom : '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0" name="bom_unit_cost[]" class="form-control height-35 f-14 estimate-bom-unit-cost" value="{{ $unitCost }}">
                            </td>
                            <td class="estimate-bom-line-total f-14 text-dark-grey align-middle text-right">{{ $computedLineTotal !== '' ? number_format((float) $computedLineTotal, 2, '.', '') : '—' }}</td>
                            <td class="text-right align-middle">
                                <input type="hidden" name="bom_unit_id[]" class="estimate-bom-unit-id-input" value="{{ $unitId }}">
                                @if ($i > 0)
                                    <button type="button" class="btn btn-outline-danger btn-sm estimate-bom-remove-row" title="@lang('app.delete')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold f-13 text-dark-grey">
                        <td colspan="4" class="text-right border-0">@lang('modules.estimates.bomMaterialTotal')</td>
                        <td class="text-right border-0" id="estimate-bom-material-total">{{ number_format((float) $bomMaterialTotal, 2, '.', '') }}</td>
                        <td class="border-0"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-2">
            <button type="button" id="estimate-bom-add-row" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-plus mr-1"></i>@lang('modules.estimates.bomAddRow')
            </button>
        </div>

        <template id="estimate-bom-product-options-template">
            <option value="">@lang('modules.estimates.bomSelectProduct')</option>
            @foreach ($bomComponentProducts as $product)
                <option value="{{ $product->id }}" data-unit-id="{{ $product->unit_id }}">{{ $product->name }}</option>
            @endforeach
        </template>

        <script>
            (function() {
                const body = document.getElementById('estimate-bom-lines-body');
                if (!body) {
                    return;
                }

                const optionsTemplate = document.getElementById('estimate-bom-product-options-template');
                const materialPlaceholder = @json(__('modules.estimates.bomMaterialNamePlaceholder'));

                const recalcRow = (row) => {
                    const qty = parseFloat(row.querySelector('.estimate-bom-quantity')?.value || '0');
                    const cost = parseFloat(row.querySelector('.estimate-bom-unit-cost')?.value || '0');
                    const totalCell = row.querySelector('.estimate-bom-line-total');
                    if (totalCell) {
                        if (qty > 0 && cost >= 0) {
                            totalCell.textContent = (qty * cost).toFixed(2);
                        } else {
                            totalCell.textContent = '—';
                        }
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
                    const footer = document.getElementById('estimate-bom-material-total');
                    if (footer) {
                        footer.textContent = sum.toFixed(2);
                    }
                };

                const updateBomLineUnit = (row) => {
                    const select = row.querySelector('.estimate-bom-product-select');
                    const unitSuffix = row.querySelector('.estimate-bom-unit-suffix');
                    const unitInput = row.querySelector('.estimate-bom-unit-id-input');
                    if (!select || !unitSuffix) {
                        return;
                    }
                    const unitMap = JSON.parse(select.getAttribute('data-unit-map') || '{}');
                    const productId = select.value;
                    unitSuffix.textContent = productId && unitMap[productId] ? unitMap[productId] : '—';
                    const selectedOption = select.options[select.selectedIndex];
                    if (unitInput && selectedOption) {
                        unitInput.value = selectedOption.getAttribute('data-unit-id') || '';
                    }
                };

                const reindexRows = () => {
                    body.querySelectorAll('.estimate-bom-line-row').forEach((row, idx) => {
                        row.setAttribute('data-row-index', String(idx));
                        const removeBtn = row.querySelector('.estimate-bom-remove-row');
                        if (removeBtn) {
                            removeBtn.closest('td').style.display = idx === 0 ? 'none' : '';
                        }
                    });
                };

                document.getElementById('estimate-bom-add-row')?.addEventListener('click', () => {
                    const idx = body.querySelectorAll('.estimate-bom-line-row').length;
                    const tr = document.createElement('tr');
                    tr.className = 'estimate-bom-line-row';
                    tr.setAttribute('data-row-index', String(idx));
                    tr.innerHTML =
                        '<td>' +
                        '<input type="hidden" name="bom_line_id[]" value="">' +
                        '<select name="bom_product_id[]" class="form-control select-picker f-14 estimate-bom-product-select" data-container="body" data-size="8" data-live-search="true" data-unit-map=\'' + JSON.stringify(@json($bomUnitByProductId->all())).replace(/'/g, '&#39;') + '\'>' +
                        (optionsTemplate ? optionsTemplate.innerHTML : '') +
                        '</select>' +
                        '<input type="text" name="bom_material_name[]" class="form-control f-14 mt-2 estimate-bom-material-name" placeholder="' + materialPlaceholder + '">' +
                        '</td>' +
                        '<td><div class="input-group">' +
                        '<input type="number" step="0.0001" min="0.0001" name="bom_quantity[]" class="form-control height-35 f-14 estimate-bom-quantity">' +
                        '<div class="input-group-append"><span class="input-group-text height-35 f-13 text-dark-grey estimate-bom-unit-suffix">—</span></div></div></td>' +
                        '<td><input type="number" step="0.0001" min="0" name="bom_unit_cost[]" class="form-control height-35 f-14 estimate-bom-unit-cost"></td>' +
                        '<td class="estimate-bom-line-total f-14 text-dark-grey align-middle text-right">—</td>' +
                        '<td class="text-right align-middle">' +
                        '<input type="hidden" name="bom_unit_id[]" class="estimate-bom-unit-id-input" value="">' +
                        '<button type="button" class="btn btn-outline-danger btn-sm estimate-bom-remove-row" title="@lang('app.delete')"><i class="fa fa-trash"></i></button>' +
                        '</td>';
                    body.appendChild(tr);
                    if (typeof $ !== 'undefined' && $(tr).find('.select-picker').selectpicker) {
                        $(tr).find('.select-picker').selectpicker();
                    }
                    reindexRows();
                });

                body.addEventListener('click', (event) => {
                    const btn = event.target.closest('.estimate-bom-remove-row');
                    if (!btn) {
                        return;
                    }
                    btn.closest('tr')?.remove();
                    reindexRows();
                    recalcGrandTotal();
                });

                body.addEventListener('input', (event) => {
                    if (event.target.matches('.estimate-bom-quantity, .estimate-bom-unit-cost')) {
                        const row = event.target.closest('.estimate-bom-line-row');
                        if (row) {
                            recalcRow(row);
                            recalcGrandTotal();
                        }
                    }
                });

                body.addEventListener('change', (event) => {
                    if (event.target.matches('.estimate-bom-product-select')) {
                        const row = event.target.closest('.estimate-bom-line-row');
                        if (row) {
                            updateBomLineUnit(row);
                            const nameInput = row.querySelector('.estimate-bom-material-name');
                            const selected = event.target.options[event.target.selectedIndex];
                            if (nameInput && selected && selected.value) {
                                nameInput.value = selected.text.trim();
                            }
                        }
                    }
                });

                body.querySelectorAll('.estimate-bom-line-row').forEach((row) => {
                    updateBomLineUnit(row);
                    recalcRow(row);
                });
                recalcGrandTotal();
                reindexRows();
            })();
        </script>
    @endif
</div>
