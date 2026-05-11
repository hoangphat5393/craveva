@php
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
                    'quantity' => $it->quantity,
                ],
            )
            ->all();
    }
    $rowCount = max(count($lines), 1);

    $bomFgUnitByProductId = $bomFgUnitByProductId ?? $finishedGoods->keyBy('id')->map(static fn($p) => optional($p->unit)->unit_type ?? '—');
    $bomComponentUnitByProductId = $bomComponentUnitByProductId ?? $componentProducts->keyBy('id')->map(static fn($p) => optional($p->unit)->unit_type ?? '—');

    /** @param  \App\Models\Product  $product */
    $bomProductLabelWithUnit = static function ($product, \Illuminate\Support\Collection $unitByProductId): string {
        $u = (string) ($unitByProductId->get((string) $product->id) ?? ($unitByProductId->get((int) $product->id) ?? '—'));
        if ($u !== '' && $u !== '—') {
            return $product->name . ' (' . $u . ')';
        }

        return $product->name;
    };
@endphp

<div class="form-row my-3">
    <div class="col-12">
        <x-forms.select class="mb-0" fieldId="output_product_id" :fieldLabel="__('production::app.fgProduct')" fieldName="output_product_id" fieldRequired="true">
            <option value="">—</option>
            @foreach ($finishedGoods as $p)
                <option value="{{ $p->id }}" @selected((int) old('output_product_id', isset($bom) ? $bom->output_product_id : 0) === (int) $p->id)>{{ $bomProductLabelWithUnit($p, $bomFgUnitByProductId) }}</option>
            @endforeach
        </x-forms.select>
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
    <x-forms.checkbox :checked="(bool) old('is_default', isset($bom) ? $bom->is_default : false)" :fieldLabel="__('production::app.bomDefault')" fieldName="is_default" fieldId="is_default" fieldValue="1" />
</div>

<div class="form-group my-3">
    <x-forms.label fieldId="notes" :fieldLabel="__('production::app.bomNotes')" fieldRequired="false" />
    <textarea name="notes" id="notes" class="form-control f-14" rows="2" maxlength="2000">{{ old('notes', isset($bom) ? $bom->notes ?? '' : '') }}</textarea>
</div>

<h6 class="f-14 text-dark-grey font-weight-bold mb-2">@lang('production::app.bomLines')</h6>
<div class="table-responsive bg-white rounded border">
    <table class="table table-sm mb-0">
        <thead>
            <tr class="f-14 text-dark-grey">
                <th>@lang('production::app.componentProduct')</th>
                <th style="width: 160px;">@lang('production::app.bomComponentQty')</th>
                <th style="width: 80px;">@lang('app.action')</th>
            </tr>
        </thead>
        <tbody id="bom-lines-body">
            @for ($i = 0; $i < $rowCount; $i++)
                @php
                    $line = $lines[$i] ?? [];
                    $cid = old("items.$i.component_product_id", $line['component_product_id'] ?? '');
                    $qty = old("items.$i.quantity", $line['quantity'] ?? '');
                @endphp
                <tr class="bom-line-row" data-row-index="{{ $i }}">
                    <td>
                        <select name="items[{{ $i }}][component_product_id]" class="form-control select-picker f-14 bom-component-select" data-container="body" data-size="8">
                            <option value="">—</option>
                            @foreach ($componentProducts as $p)
                                <option value="{{ $p->id }}" @selected((string) $cid === (string) $p->id)>{{ $bomProductLabelWithUnit($p, $bomComponentUnitByProductId) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.0001" min="0.0001" name="items[{{ $i }}][quantity]" class="form-control height-35 f-14" value="{{ $qty }}">
                    </td>
                    <td class="text-right">
                        @if ($i > 0)
                            <button type="button" class="btn btn-outline-danger btn-sm bom-remove-row" title="@lang('app.delete')">
                                <i class="fa fa-trash"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
<div class="mt-3">
    <button type="button" id="bom-add-row" class="btn btn-outline-primary btn-sm">
        <i class="fa fa-plus mr-1"></i>@lang('app.add')
    </button>
</div>

@push('scripts')
    <script>
        (() => {
            const body = document.getElementById('bom-lines-body');
            const addBtn = document.getElementById('bom-add-row');
            const bomForm = body ? body.closest('form') : null;
            const fgSelect = bomForm ?
                (bomForm.querySelector('#output_product_id') || bomForm.querySelector('select[name="output_product_id"]')) :
                null;
            if (!body || !addBtn || !bomForm) {
                return;
            }

            const componentOptionsHtml = `
                <option value="">—</option>
                @foreach ($componentProducts as $p)
                    <option value="{{ $p->id }}">{{ $bomProductLabelWithUnit($p, $bomComponentUnitByProductId) }}</option>
                @endforeach
            `;

            const collectSelectOptionElements = (selectEl) => {
                if (!selectEl || typeof selectEl.querySelectorAll !== 'function') {
                    return [];
                }

                return Array.from(selectEl.querySelectorAll('option'));
            };

            const syncRemoveRowButtons = () => {
                body.querySelectorAll('.bom-line-row').forEach((row, idx) => {
                    const btn = row.querySelector('.bom-remove-row');
                    if (!btn) {
                        return;
                    }
                    btn.classList.toggle('d-none', idx === 0);
                });
            };

            const refreshPicker = (selectEl) => {
                if (!window.jQuery || typeof window.jQuery.fn.selectpicker !== 'function') {
                    return;
                }
                try {
                    const ret = window.jQuery(selectEl).selectpicker('refresh');
                    if (ret != null && typeof ret.then === 'function') {
                        ret.catch(() => {});
                    }
                } catch (e) {
                    /* bootstrap-select refresh can throw or reject */
                }
            };

            const applyFgRestrictionForRow = (row) => {
                if (!row || typeof row.querySelector !== 'function') {
                    return;
                }
                const fgId = fgSelect ? String(fgSelect.value || '') : '';
                const componentSelect = row.querySelector('.bom-component-select');
                if (!(componentSelect instanceof HTMLSelectElement)) {
                    return;
                }

                const optionNodes = collectSelectOptionElements(componentSelect);

                optionNodes.forEach((opt) => {
                    if (!opt.value) {
                        opt.disabled = false;
                        opt.hidden = false;
                        return;
                    }
                    const isFgOption = fgId !== '' && String(opt.value) === fgId;
                    opt.disabled = isFgOption;
                    opt.hidden = isFgOption;
                });

                if (fgId !== '' && String(componentSelect.value) === fgId) {
                    componentSelect.value = '';
                }

                refreshPicker(componentSelect);
            };

            const applyFgRestrictionAllRows = () => {
                body.querySelectorAll('.bom-line-row').forEach((row) => applyFgRestrictionForRow(row));
            };

            const enforceComponentNotEqualFg = (componentSelect) => {
                if (!componentSelect || !fgSelect) {
                    return;
                }
                const fgId = String(fgSelect.value || '');
                if (fgId !== '' && String(componentSelect.value || '') === fgId) {
                    componentSelect.value = '';
                    refreshPicker(componentSelect);
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
                syncRemoveRowButtons();
            };

            addBtn.addEventListener('click', () => {
                const newIndex = body.querySelectorAll('.bom-line-row').length;
                const tr = document.createElement('tr');
                tr.className = 'bom-line-row';
                tr.dataset.rowIndex = String(newIndex);
                tr.innerHTML = `
                    <td>
                        <select name="items[${newIndex}][component_product_id]" class="form-control select-picker f-14 bom-component-select" data-container="body" data-size="8">
                            ${componentOptionsHtml}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.0001" min="0.0001" name="items[${newIndex}][quantity]" class="form-control height-35 f-14" value="">
                    </td>
                    <td class="text-right">
                        <button type="button" class="btn btn-outline-danger btn-sm bom-remove-row" title="@lang('app.delete')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                `;
                body.appendChild(tr);
                const newComponentSelect = tr.querySelector('.bom-component-select');
                if (window.jQuery && typeof window.jQuery.fn.selectpicker === 'function') {
                    window.jQuery(tr).find('.select-picker').selectpicker();
                }
                window.setTimeout(() => {
                    applyFgRestrictionForRow(tr);
                    syncRemoveRowButtons();
                    if (newComponentSelect instanceof HTMLSelectElement) {
                        bindBomUnitPickerListeners();
                    }
                }, 0);

                if (newComponentSelect) {
                    if (window.jQuery && typeof window.jQuery.fn.selectpicker === 'function') {
                        const pickerButton = window.jQuery(newComponentSelect).siblings('button.dropdown-toggle');
                        if (pickerButton.length > 0) {
                            pickerButton.trigger('focus');
                        }
                    } else {
                        newComponentSelect.focus();
                    }
                }
            });

            const onComponentSelectInteraction = (selectEl) => {
                if (!(selectEl instanceof HTMLSelectElement) || !selectEl.classList.contains('bom-component-select')) {
                    return;
                }
                window.setTimeout(() => {
                    enforceComponentNotEqualFg(selectEl);
                }, 0);
            };

            let bomFgSyncQueued = false;
            const onFgSelectInteraction = () => {
                if (bomFgSyncQueued) {
                    return;
                }
                bomFgSyncQueued = true;
                window.setTimeout(() => {
                    bomFgSyncQueued = false;
                    applyFgRestrictionAllRows();
                }, 0);
            };

            /**
             * Bootstrap-select: use document delegation so listeners survive re-init / `data-container="body"`.
             */
            const bomLineComponentSelectHandler = function() {
                onComponentSelectInteraction(this);
            };
            const bomFgSelectHandler = function() {
                onFgSelectInteraction();
            };

            /**
             * Capture-phase `change` + jQuery delegation: enforce RM ≠ FG when picks change.
             */
            const bomDocumentChangeCapture = (ev) => {
                if (!bomForm) {
                    return;
                }
                const t = ev.target;
                if (!(t instanceof HTMLSelectElement) || !bomForm.contains(t)) {
                    return;
                }
                if (t.classList.contains('bom-component-select') && body.contains(t)) {
                    onComponentSelectInteraction(t);

                    return;
                }
                if (fgSelect && t === fgSelect) {
                    onFgSelectInteraction();
                }
            };

            const attachBomUnitDocumentListeners = () => {
                document.removeEventListener('change', bomDocumentChangeCapture, true);
                document.addEventListener('change', bomDocumentChangeCapture, true);
                if (!window.jQuery) {
                    return;
                }
                const $ = window.jQuery;
                $(document)
                    .off('changed.bs.select.bomUnitForm', '.bom-component-select')
                    .on('changed.bs.select.bomUnitForm', '.bom-component-select', bomLineComponentSelectHandler);
                $(document)
                    .off('hidden.bs.select.bomUnitForm', '.bom-component-select')
                    .on('hidden.bs.select.bomUnitForm', '.bom-component-select', bomLineComponentSelectHandler);
                if (fgSelect) {
                    $(document)
                        .off('changed.bs.select.bomUnitFormFg', '#output_product_id')
                        .on('changed.bs.select.bomUnitFormFg', '#output_product_id', bomFgSelectHandler);
                    $(document)
                        .off('hidden.bs.select.bomUnitFormFg', '#output_product_id')
                        .on('hidden.bs.select.bomUnitFormFg', '#output_product_id', bomFgSelectHandler);
                }
            };

            const bindBomUnitPickerListeners = () => {
                attachBomUnitDocumentListeners();
            };

            if (window.jQuery) {
                window.jQuery(function() {
                    window.setTimeout(() => attachBomUnitDocumentListeners(), 0);
                });
                window.jQuery(window).on('load', () => {
                    window.setTimeout(() => attachBomUnitDocumentListeners(), 0);
                    window.setTimeout(() => attachBomUnitDocumentListeners(), 250);
                });
            }

            body.addEventListener('click', (event) => {
                const target = event.target instanceof Element ? event.target.closest('.bom-remove-row') : null;
                if (!target) {
                    return;
                }

                const rows = body.querySelectorAll('.bom-line-row');
                if (rows.length <= 1) {
                    const onlyRow = rows[0];
                    onlyRow.querySelectorAll('input').forEach((input) => {
                        input.value = '';
                    });
                    onlyRow.querySelectorAll('select').forEach((select) => {
                        select.value = '';
                        refreshPicker(select);
                    });
                    applyFgRestrictionAllRows();
                    return;
                }

                target.closest('.bom-line-row')?.remove();
                reindexRows();
                applyFgRestrictionAllRows();
            });

            bindBomUnitPickerListeners();

            if (bomForm) {
                bomForm.addEventListener('submit', (event) => {
                    const fgId = fgSelect ? String(fgSelect.value || '') : '';
                    if (fgId === '') {
                        return;
                    }
                    const hasInvalid = Array.from(body.querySelectorAll('.bom-component-select'))
                        .some((select) => String(select.value || '') === fgId);
                    if (hasInvalid) {
                        event.preventDefault();
                        applyFgRestrictionAllRows();
                        if (window.Swal && typeof window.Swal.fire === 'function') {
                            window.Swal.fire({
                                icon: 'error',
                                title: @json(__('app.error')),
                                text: @json(__('production::app.bomComponentMustDifferFromOutput')),
                            });
                        } else {
                            alert(@json(__('production::app.bomComponentMustDifferFromOutput')));
                        }
                    }
                });
            }

            applyFgRestrictionAllRows();
            syncRemoveRowButtons();
            window.setTimeout(() => {
                bindBomUnitPickerListeners();
                applyFgRestrictionAllRows();
                syncRemoveRowButtons();
            }, 150);
            window.setTimeout(() => {
                bindBomUnitPickerListeners();
                applyFgRestrictionAllRows();
                syncRemoveRowButtons();
            }, 500);
        })();
    </script>
@endpush
