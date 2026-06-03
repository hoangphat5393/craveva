@php
    use Modules\Production\Support\ProductionBomLineCostCalculator;
    use Modules\Production\Support\ProductionProductSelectLabel;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Product>|array $finishedGoods */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Product>|array $componentProducts */
    /** @var \Modules\Production\Entities\ProductionBom|null $bom */

    $lines = old('items');
    if (!is_array($lines)) {
        $lines = [];
    }
    if ($lines === [] && isset($bom) && $bom->relationLoaded('items')) {
        $lines = $bom->items
            ->map(
                static fn($it) => [
                    'component_product_id' => $it->component_product_id,
                    'unit_id' => $it->unit_id,
                    'quantity' => $it->quantity,
                    'waste_percent' => $it->waste_percent ?? 0,
                ],
            )
            ->all();
    }

    $showBomWasteUi = (bool) config('production.ui.show_bom_waste_percent_ui', false);

    $bomFgUnitByProductId = $bomFgUnitByProductId ?? $finishedGoods->keyBy('id')->map(static fn($p) => optional($p->unit)->unit_type ?? '—');
    $bomComponentUnitByProductId = $bomComponentUnitByProductId ?? $componentProducts->keyBy('id')->map(static fn($p) => optional($p->unit)->unit_type ?? '—');
    $bomUnitsByProductId = $bomUnitsByProductId ?? [];
    $bomUnitCostByProductAndUnit = $bomUnitCostByProductAndUnit ?? [];
    $bomCostCalculator = $bomCostCalculator ?? app(ProductionBomLineCostCalculator::class);
    $bomCompanyId = (int) company()->id;
    $bomCurrencySetting = currency_format_setting(company()->currency_id);

    $formatBomCost = static function (?float $value): string {
        if ($value === null) {
            return '—';
        }

        return currency_format($value, company()->currency_id);
    };

    /** @param  \App\Models\Product  $product */
    $bomProductLabelWithUnit = static function ($product, \Illuminate\Support\Collection $unitByProductId): string {
        $u = (string) ($unitByProductId->get((string) $product->id) ?? ($unitByProductId->get((int) $product->id) ?? '—'));
        if ($u !== '' && $u !== '—') {
            return $product->name . ' (' . $u . ')';
        }

        return $product->name;
    };

    $componentProductById = collect($componentProducts ?? [])->keyBy('id');
    $bomComponentLabelByProductId = $componentProductById->map(fn($p) => $bomProductLabelWithUnit($p, $bomComponentUnitByProductId));

    $renderedLines = [];
    foreach ($lines as $line) {
        $cid = data_get($line, 'component_product_id');
        if ($cid === '' || $cid === null) {
            continue;
        }
        $product = $componentProductById->get((int) $cid);
        $lineUnitId = data_get($line, 'unit_id');
        $qty = data_get($line, 'quantity');
        $waste = data_get($line, 'waste_percent', 0);
        $unitsForRow = $bomUnitsByProductId[(string) $cid] ?? ($bomUnitsByProductId[(int) $cid] ?? []);
        if (($lineUnitId === '' || $lineUnitId === null) && $unitsForRow !== []) {
            $defaultUnit = collect($unitsForRow)->firstWhere('is_base', true) ?? $unitsForRow[0];
            $lineUnitId = $defaultUnit['unit_id'] ?? $lineUnitId;
        }
        $lineCosts = $bomCostCalculator->lineCostFromInput(
            [
                'component_product_id' => $cid,
                'unit_id' => $lineUnitId,
                'quantity' => $qty,
                'waste_percent' => $waste,
            ],
            $bomCompanyId,
        );
        $renderedLines[] = [
            'component_product_id' => $cid,
            'component_product_name' => $product ? $bomProductLabelWithUnit($product, $bomComponentUnitByProductId) : (string) $cid,
            'unit_id' => $lineUnitId,
            'quantity' => $qty,
            'waste_percent' => $waste,
            'units_for_row' => $unitsForRow,
            'unit_cost' => $formatBomCost($lineCosts['unit_cost']),
            'line_total' => $formatBomCost($lineCosts['line_total']),
        ];
    }
@endphp

<div class="form-row my-3">
    <div class="col-12">
        <x-forms.select class="mb-0" fieldId="output_product_id" :search="true" :fieldLabel="__('production::app.manufacturedProduct')" fieldName="output_product_id" fieldRequired="true">
            <option value="">—</option>
            @foreach ($finishedGoods as $p)
                @php
                    $fgSelectLabel = ProductionProductSelectLabel::forProduct($p);
                    $fgSku = trim((string) ($p->sku ?? ''));
                @endphp
                <option value="{{ $p->id }}" data-content="{{ $fgSelectLabel }}" @if ($fgSku !== '') data-tokens="{{ $fgSku }}" @endif @selected((int) old('output_product_id', isset($bom) ? $bom->output_product_id : 0) === (int) $p->id)>{{ $fgSelectLabel }}</option>
            @endforeach
        </x-forms.select>
        <p class="f-12 text-dark-grey mb-0 mt-2">@lang('production::app.bomManufacturedProductHelp')</p>
        @if ($finishedGoods->isEmpty())
            <div class="alert alert-warning f-13 mt-2 mb-0">@lang('production::app.bomNoManufacturedProducts')</div>
        @endif
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <x-forms.label fieldId="version" :fieldLabel="__('production::app.bomVersion')" fieldRequired="true" />
        <input type="text" name="version" id="version" class="form-control height-35 f-14" maxlength="32" required value="{{ old('version', isset($bom) ? $bom->version : '') }}">
    </div>
    <div class="form-group col-md-3">
        <x-forms.label fieldId="code" :fieldLabel="__('production::app.bomCode')" fieldRequired="false" />
        <input type="text" name="code" id="code" class="form-control height-35 f-14" maxlength="64" value="{{ old('code', isset($bom) ? $bom->code ?? '' : '') }}">
    </div>
    <div class="form-group col-md-3">
        <x-forms.label fieldId="effective_from" :fieldLabel="__('production::app.bomEffectiveFrom')" fieldRequired="false" />
        <input type="date" name="effective_from" id="effective_from" class="form-control height-35 f-14" value="{{ old('effective_from', isset($bom) && $bom->effective_from ? $bom->effective_from->format('Y-m-d') : '') }}">
    </div>
    <div class="form-group col-md-3">
        <x-forms.label fieldId="effective_to" :fieldLabel="__('production::app.bomEffectiveTo')" fieldRequired="false" />
        <input type="date" name="effective_to" id="effective_to" class="form-control height-35 f-14" value="{{ old('effective_to', isset($bom) && $bom->effective_to ? $bom->effective_to->format('Y-m-d') : '') }}">
    </div>
</div>

<div class="form-group my-2">
    <input type="hidden" name="is_default" value="0" />
    @if (config('production.ui.show_bom_default_for_manufactured_product_ui', false))
        <x-forms.checkbox :checked="(bool) old('is_default', isset($bom) ? $bom->is_default : false)" :fieldLabel="__('production::app.bomDefaultForManufacturedProduct')" fieldName="is_default" fieldId="is_default" fieldValue="1" />
    @endif
</div>

<div class="form-group my-3">
    <x-forms.label fieldId="notes" :fieldLabel="__('production::app.bomNotes')" fieldRequired="false" />
    <textarea name="notes" id="notes" class="form-control f-14 pt-2" rows="4" maxlength="2000">{{ old('notes', isset($bom) ? $bom->notes ?? '' : '') }}</textarea>
</div>

<h6 class="f-14 text-dark-grey font-weight-bold mb-1">@lang('production::app.bomLines')</h6>
<p class="f-12 text-dark-grey mb-1">@lang('production::app.bomComponentHelpForManufacturedProduct')</p>
<p class="f-12 text-muted mb-1">@lang('production::app.bomCostingHelp')</p>
<p class="f-12 text-muted mb-2">@lang('production::app.bomUomSelectHelpForManufacturedProduct')</p>
@if ($showBomWasteUi)
    <p class="f-12 text-muted mb-2">@lang('production::app.bomWastePercentHelp')</p>
@endif
@if ($componentProducts->isEmpty())
    <div class="alert alert-warning f-13">@lang('production::app.bomNoComponents')</div>
@else
    <div class="row mb-3">
        <div class="col-md-6 col-lg-5">
            <x-forms.label class="my-1" fieldId="add-bom-component" :fieldLabel="__('production::app.bomAddComponent')" />
            <x-forms.input-group>
                <select class="form-control select-picker" data-live-search="true" data-size="8" id="add-bom-component">
                    <option value="">{{ __('app.select') }} {{ __('production::app.rawMaterialProduct') }}</option>
                    @foreach ($componentProducts as $p)
                        <option value="{{ $p->id }}">{{ $bomProductLabelWithUnit($p, $bomComponentUnitByProductId) }}</option>
                    @endforeach
                </select>
            </x-forms.input-group>
        </div>
    </div>
@endif
<div class="table-responsive bg-white rounded border">
    <table class="table table-sm mb-0">
        <thead>
            <tr class="f-14 text-dark-grey">
                <th>@lang('production::app.componentProduct')</th>
                <th style="min-width: 220px;">@lang('production::app.bomComponentQtyAndUom')</th>
                @if ($showBomWasteUi)
                    <th style="width: 100px;">@lang('production::app.bomWastePercent')</th>
                @endif
                <th style="width: 110px;" class="text-right">@lang('production::app.bomComponentUnitCost')</th>
                <th style="width: 110px;" class="text-right">@lang('production::app.bomComponentLineTotal')</th>
                <th style="width: 48px;"></th>
            </tr>
        </thead>
        <tbody id="bom-lines-body">
            @foreach ($renderedLines as $rowIndex => $lineRow)
                @include('production::boms.partials.bom-line-row', [
                    'rowIndex' => $rowIndex,
                    'componentProductId' => $lineRow['component_product_id'],
                    'componentProductName' => $lineRow['component_product_name'],
                    'lineUnitId' => $lineRow['unit_id'],
                    'qty' => $lineRow['quantity'],
                    'waste' => $lineRow['waste_percent'],
                    'unitsForRow' => $lineRow['units_for_row'],
                    'lineUnitCost' => $lineRow['unit_cost'],
                    'lineExtendedCost' => $lineRow['line_total'],
                    'showBomWasteUi' => $showBomWasteUi,
                ])
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3 d-flex flex-wrap align-items-center justify-content-end">
    <p class="f-14 mb-0 text-dark-grey">
        <span class="font-weight-bold">@lang('production::app.bomTotalComponentCostPerManufacturedProduct'):</span>
        <span id="bom-total-component-cost" class="ml-1">—</span>
    </p>
</div>

@push('scripts')
    <script>
        (() => {
            const body = document.getElementById('bom-lines-body');
            const addComponentSelect = document.getElementById('add-bom-component');
            const bomForm = body ? body.closest('form') : null;
            const fgSelect = bomForm ?
                (bomForm.querySelector('#output_product_id') || bomForm.querySelector('select[name="output_product_id"]')) :
                null;
            if (!body || !bomForm) {
                return;
            }

            const bomUnitsByProductId = @json($bomUnitsByProductId);
            const bomUnitCostByProductAndUnit = @json($bomUnitCostByProductAndUnit);
            const bomComponentLabelByProductId = @json($bomComponentLabelByProductId);
            const bomCurrencySymbol = @json(company()->currency->currency_symbol ?? '');
            const bomCurrencyPosition = @json($bomCurrencySetting->currency_position ?? 'left');
            const bomCurrencyDecimals = {{ (int) ($bomCurrencySetting->no_of_decimal ?? 2) }};
            const bomCostDash = '—';
            const showBomWasteUi = @json($showBomWasteUi);
            const bomTotalCostEl = document.getElementById('bom-total-component-cost');
            const msgComponentAlreadyOnBom = @json(__('production::app.bomComponentAlreadyOnBom'));
            const msgComponentMustDifferFromOutput = @json(__('production::app.bomComponentMustDifferFromManufacturedProduct'));
            const msgAddAtLeastOneLine = @json(__('production::app.bomAddAtLeastOneLine'));
            const deleteTitle = @json(__('app.delete'));

            const formatBomMoney = (value) => {
                if (value === null || value === undefined || Number.isNaN(Number(value))) {
                    return bomCostDash;
                }

                const amount = Number(value).toLocaleString(undefined, {
                    minimumFractionDigits: bomCurrencyDecimals,
                    maximumFractionDigits: bomCurrencyDecimals,
                });

                switch (bomCurrencyPosition) {
                    case 'right':
                        return amount + bomCurrencySymbol;
                    case 'left_with_space':
                        return bomCurrencySymbol + ' ' + amount;
                    case 'right_with_space':
                        return amount + ' ' + bomCurrencySymbol;
                    default:
                        return bomCurrencySymbol + amount;
                }
            };

            const extendedCostFromInputs = (quantity, wastePercent, unitPrice) => {
                if (unitPrice === null || unitPrice === undefined || quantity <= 0) {
                    return null;
                }

                const wasteMultiplier = 1 + (Math.max(0, wastePercent) / 100);

                return Math.round(quantity * wasteMultiplier * unitPrice * 10000) / 10000;
            };

            const resolveUnitPrice = (productId, unitId) => {
                if (productId === '' || unitId === '') {
                    return null;
                }

                const productKey = String(productId);
                const unitKey = String(unitId);
                const byProduct = bomUnitCostByProductAndUnit[productKey] ??
                    bomUnitCostByProductAndUnit[Number(productId)];
                if (!byProduct || typeof byProduct !== 'object') {
                    return null;
                }

                const price = byProduct[unitKey] ?? byProduct[Number(unitId)];

                return price === null || price === undefined ? null : Number(price);
            };

            const defaultUnitIdForProduct = (productId) => {
                const productKey = String(productId);
                const units = bomUnitsByProductId[productKey] || bomUnitsByProductId[Number(productId)] || [];
                if (!units.length) {
                    return '';
                }
                const base = units.find((unit) => unit.is_base);

                return String((base || units[0]).unit_id);
            };

            const pickerSelectValue = (selectEl) => {
                if (!(selectEl instanceof HTMLSelectElement)) {
                    return '';
                }
                if (selectEl.value) {
                    return String(selectEl.value);
                }
                if (window.jQuery && typeof window.jQuery.fn.selectpicker === 'function') {
                    const $el = window.jQuery(selectEl);
                    if ($el.data('selectpicker')) {
                        const val = $el.selectpicker('val');
                        if (val !== null && val !== undefined && val !== '') {
                            return String(Array.isArray(val) ? val[0] : val);
                        }
                    }
                }

                return '';
            };

            const setRowUnitId = (row, unitId) => {
                if (!row) {
                    return;
                }
                row.dataset.unitId = unitId === null || unitId === '' ? '' : String(unitId);
            };

            const lineUnitId = (row) => {
                if (row?.dataset?.unitId) {
                    return String(row.dataset.unitId);
                }
                const unitSelect = row?.querySelector('.bom-line-unit-select');
                if (unitSelect instanceof HTMLSelectElement && unitSelect.value) {
                    return String(unitSelect.value);
                }

                return '';
            };

            const lineProductIdFromRow = (row) => {
                if (row?.dataset?.productId) {
                    return String(row.dataset.productId);
                }

                return lineProductId(row);
            };

            const effectiveLineUnitId = (row, forcedUnitId = null) => {
                if (forcedUnitId !== null && forcedUnitId !== '') {
                    return String(forcedUnitId);
                }

                return lineUnitId(row);
            };

            const scheduleBomCostRecalc = () => {
                window.setTimeout(() => recalcBomTotals(), 0);
            };

            const populateBomLineUnitSelect = (row, productId, preferredUnitId = null) => {
                const unitSelect = row.querySelector('.bom-line-unit-select');
                if (!(unitSelect instanceof HTMLSelectElement)) {
                    return '';
                }

                const productKey = String(productId);
                const units = productId !== '' ?
                    (bomUnitsByProductId[productKey] || bomUnitsByProductId[Number(productId)] || []) : [];
                unitSelect.innerHTML = '';

                if (!units.length) {
                    const emptyOpt = document.createElement('option');
                    emptyOpt.value = '';
                    emptyOpt.textContent = bomCostDash;
                    unitSelect.appendChild(emptyOpt);
                    setRowUnitId(row, '');

                    return '';
                }

                units.forEach((unit) => {
                    const opt = document.createElement('option');
                    opt.value = String(unit.unit_id);
                    opt.textContent = unit.label;
                    unitSelect.appendChild(opt);
                });

                let selected = preferredUnitId !== null && preferredUnitId !== '' ?
                    String(preferredUnitId) :
                    defaultUnitIdForProduct(productKey);
                if (selected && Array.from(unitSelect.options).some((opt) => opt.value === selected)) {
                    unitSelect.value = selected;
                } else {
                    selected = '';
                    unitSelect.value = '';
                }

                setRowUnitId(row, selected);

                return selected;
            };

            const lineProductId = (row) => {
                const hidden = row.querySelector('.bom-line-component-id');

                return hidden instanceof HTMLInputElement ? String(hidden.value || '') : '';
            };

            const recalcBomLineCost = (row, forcedUnitId = null) => {
                if (!row) {
                    return;
                }
                const unitCostCell = row.querySelector('.bom-line-unit-cost');
                const lineTotalCell = row.querySelector('.bom-line-extended-cost');
                const qtyInput = row.querySelector('.bom-line-quantity');
                const wasteInput = row.querySelector('.bom-line-waste');
                if (!unitCostCell || !lineTotalCell) {
                    return;
                }

                const productId = lineProductIdFromRow(row);
                const unitId = effectiveLineUnitId(row, forcedUnitId);
                const unitPrice = resolveUnitPrice(productId, unitId);
                const quantity = qtyInput ? parseFloat(qtyInput.value || '0') : 0;
                const wastePercent = wasteInput ? parseFloat(wasteInput.value || '0') : 0;
                const lineTotal = extendedCostFromInputs(quantity, wastePercent, unitPrice);

                unitCostCell.textContent = formatBomMoney(unitPrice);
                lineTotalCell.textContent = formatBomMoney(lineTotal);
            };

            const recalcBomTotals = () => {
                let total = 0;
                let hasAny = false;
                body.querySelectorAll('.bom-line-row').forEach((row) => {
                    recalcBomLineCost(row);
                    const qtyInput = row.querySelector('.bom-line-quantity');
                    const wasteInput = row.querySelector('.bom-line-waste');
                    const productId = lineProductIdFromRow(row);
                    const unitId = lineUnitId(row);
                    const unitPrice = resolveUnitPrice(productId, unitId);
                    const quantity = qtyInput ? parseFloat(qtyInput.value || '0') : 0;
                    const wastePercent = wasteInput ? parseFloat(wasteInput.value || '0') : 0;
                    const lineTotal = extendedCostFromInputs(quantity, wastePercent, unitPrice);
                    if (lineTotal !== null) {
                        total += lineTotal;
                        hasAny = true;
                    }
                });
                if (bomTotalCostEl) {
                    bomTotalCostEl.textContent = hasAny ? formatBomMoney(Math.round(total * 10000) / 10000) : bomCostDash;
                }
            };

            const reindexRows = () => {
                body.querySelectorAll('.bom-line-row').forEach((row, index) => {
                    row.dataset.rowIndex = String(index);
                    row.querySelectorAll('select,input').forEach((field) => {
                        const name = field.getAttribute('name');
                        if (!name) {
                            return;
                        }
                        field.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
                    });
                });
            };

            const rowHasProductId = (productId) => Array.from(body.querySelectorAll('.bom-line-row'))
                .some((row) => lineProductId(row) === String(productId));

            const showBomAlert = (text) => {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'error',
                        title: @json(__('app.error')),
                        text,
                    });

                    return;
                }
                alert(text);
            };

            const appendBomLine = (productId) => {
                const pid = String(productId || '');
                if (pid === '') {
                    return;
                }

                const fgId = fgSelect ? String(fgSelect.value || '') : '';
                if (fgId !== '' && pid === fgId) {
                    showBomAlert(msgComponentMustDifferFromOutput);

                    return;
                }

                if (rowHasProductId(pid)) {
                    showBomAlert(msgComponentAlreadyOnBom);

                    return;
                }

                const label = bomComponentLabelByProductId[pid] || bomComponentLabelByProductId[Number(pid)] || pid;
                const newIndex = body.querySelectorAll('.bom-line-row').length;
                const tr = document.createElement('tr');
                tr.className = 'bom-line-row';
                tr.dataset.rowIndex = String(newIndex);
                tr.dataset.productId = pid;
                tr.dataset.unitId = '';
                const wasteCellHtml = showBomWasteUi ? `
                    <td>
                        <input type="number" step="0.01" min="0" max="100" name="items[${newIndex}][waste_percent]" class="form-control height-35 f-14 bom-line-waste" value="0">
                    </td>
                ` : '';
                tr.innerHTML = `
                    <td class="align-middle">
                        <span class="f-14 text-dark-grey bom-line-product-name"></span>
                        <input type="hidden" name="items[${newIndex}][component_product_id]" class="bom-line-component-id" value="">
                    </td>
                    <td class="align-middle">
                        <div class="d-flex flex-nowrap align-items-center bom-line-qty-uom" style="gap: 0.35rem;">
                            <input type="number" step="0.0001" min="0.0001" name="items[${newIndex}][quantity]" class="form-control height-35 f-14 bom-line-quantity" style="min-width: 4.5rem; max-width: 7rem; flex: 1 1 auto;" value="1">
                            <select name="items[${newIndex}][unit_id]" class="form-control height-35 f-14 bom-line-unit-select" style="min-width: 5.5rem; max-width: 9rem; flex: 0 0 auto;">
                                <option value="">—</option>
                            </select>
                        </div>
                        ${showBomWasteUi ? '' : `<input type="hidden" name="items[${newIndex}][waste_percent]" class="bom-line-waste" value="0">`}
                    </td>
                    ${showBomWasteUi ? wasteCellHtml : ''}
                    <td class="bom-line-unit-cost f-14 text-dark-grey align-middle text-right">${bomCostDash}</td>
                    <td class="bom-line-extended-cost f-14 text-dark-grey align-middle text-right">${bomCostDash}</td>
                    <td class="text-right align-middle">
                        <button type="button" class="btn btn-outline-danger btn-sm bom-remove-row" title="${deleteTitle}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                `;
                body.appendChild(tr);
                const nameEl = tr.querySelector('.bom-line-product-name');
                const hiddenId = tr.querySelector('.bom-line-component-id');
                if (nameEl) {
                    nameEl.textContent = label;
                }
                if (hiddenId instanceof HTMLInputElement) {
                    hiddenId.value = pid;
                }
                const selectedUnitId = populateBomLineUnitSelect(tr, pid, null);
                recalcBomLineCost(tr, selectedUnitId);
                scheduleBomCostRecalc();
            };

            const resetAddComponentPicker = () => {
                if (!(addComponentSelect instanceof HTMLSelectElement)) {
                    return;
                }
                addComponentSelect.value = '';
                if (window.jQuery && typeof window.jQuery.fn.selectpicker === 'function') {
                    window.jQuery(addComponentSelect).selectpicker('val', '');
                    window.jQuery(addComponentSelect).selectpicker('refresh');
                }
            };

            if (addComponentSelect instanceof HTMLSelectElement) {
                const onAddComponentPick = () => {
                    const id = pickerSelectValue(addComponentSelect);
                    if (id !== '') {
                        appendBomLine(id);
                        resetAddComponentPicker();
                    }
                };

                addComponentSelect.addEventListener('change', onAddComponentPick);
                if (window.jQuery) {
                    window.jQuery(function() {
                        window.jQuery(document)
                            .off('changed.bs.select.bomAddComponent', '#add-bom-component')
                            .on('changed.bs.select.bomAddComponent', '#add-bom-component', onAddComponentPick);
                    });
                }
            }

            body.addEventListener('change', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLSelectElement) || !target.classList.contains('bom-line-unit-select')) {
                    return;
                }
                const row = target.closest('.bom-line-row');
                const unitId = String(target.value || '');
                setRowUnitId(row, unitId);
                recalcBomLineCost(row, unitId);
                recalcBomTotals();
            });

            body.addEventListener('input', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) {
                    return;
                }
                if (!target.classList.contains('bom-line-quantity') && !target.classList.contains('bom-line-waste')) {
                    return;
                }
                const row = target.closest('.bom-line-row');
                recalcBomLineCost(row);
                recalcBomTotals();
            });

            body.addEventListener('click', (event) => {
                const target = event.target instanceof Element ? event.target.closest('.bom-remove-row') : null;
                if (!target) {
                    return;
                }

                target.closest('.bom-line-row')?.remove();
                reindexRows();
                recalcBomTotals();
            });

            if (bomForm) {
                bomForm.addEventListener('submit', (event) => {
                    const rows = body.querySelectorAll('.bom-line-row');
                    if (rows.length < 1) {
                        event.preventDefault();
                        showBomAlert(msgAddAtLeastOneLine);

                        return;
                    }

                    const fgId = fgSelect ? String(fgSelect.value || '') : '';
                    if (fgId === '') {
                        return;
                    }

                    const hasInvalid = Array.from(rows)
                        .some((row) => lineProductId(row) === fgId);
                    if (hasInvalid) {
                        event.preventDefault();
                        showBomAlert(msgComponentMustDifferFromOutput);
                    }
                });
            }

            body.querySelectorAll('.bom-line-row').forEach((row) => {
                const productId = lineProductId(row);
                const keepUnitId = lineUnitId(row);
                if (productId !== '') {
                    const selectedUnitId = populateBomLineUnitSelect(row, productId, keepUnitId);
                    recalcBomLineCost(row, selectedUnitId || keepUnitId);
                }
            });

            scheduleBomCostRecalc();
        })();
    </script>
@endpush
