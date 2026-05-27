@extends('layouts.app')

@php
    $componentProductNames = $componentProducts->pluck('name', 'id');
    $formatQuantity = static fn($value): string => rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.orders.show', $batch->order)" class="mr-3 float-left" icon="arrow-left">
                    @lang('production::app.backToOrder')
                </x-forms.link-secondary>
                <x-forms.link-primary :link="route('production.batches.trace', $batch)" class="float-left" icon="project-diagram">
                    @lang('production::app.traceability')
                </x-forms.link-primary>
            </div>
        </div>

        @php
            $productionFeedbackJumpAnchor = $errors->hasAny(['quantity', 'variance_reason', 'batch_number', 'warehouse_id']) || session('error') ? 'production-create-output-form' : null;
        @endphp
        @include('production::partials.flash-and-validation-alerts')

        @include('production::batches.partials.completion-workflow')

        <div class="bg-white rounded p-4 mt-3 mb-3 border-left border-primary border-width-3" style="border-left-width: 4px !important;">
            <h5 class="f-14 text-dark-grey font-weight-bold mb-2">@lang('production::app.printLabelSlipCardHeading')</h5>
            <p class="f-20 font-weight-bold text-dark mb-2">{{ $batch->batch_code }}</p>
            <a href="{{ route('production.batches.print-label-slip', $batch) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm rounded f-13 mb-2">
                <i class="fa fa-print mr-1"></i>@lang('production::app.printLabelSlipOpen')
            </a>
            <p class="text-muted f-12 mb-0">@lang('production::app.printLabelSlipCardHelp')</p>
        </div>

        <div class="bg-white rounded p-4 mt-3 mb-4">
            <div class="row f-14">
                <div class="col-md-4 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.status') (@lang('app.order'))</span>
                    <span class="font-weight-normal">@include('production::partials.order-status-badge', ['status' => $batch->order->status])</span>
                </div>
                <div class="col-md-4 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.rawMaterialsDeductedAt')</span>
                    <span class="font-weight-normal">{{ $batch->posted_consumptions_at ?? '—' }}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.finishedGoodsPostedAt')</span>
                    <span class="font-weight-normal">{{ $batch->posted_receipt_at ?? '—' }}</span>
                </div>
            </div>
        </div>

        <h5 class="f-14 text-dark-grey font-weight-bold mb-2">@lang('production::app.rawMaterialsUsed')</h5>
        @if (\Modules\Production\Support\ProductionBatchPlannedLinesPolicy::autoApplyBomSnapshotOnBatch() && !\Modules\Production\Support\ProductionBatchPlannedLinesPolicy::showApplyPlannedFromSnapshotButton())
            <p class="f-12 text-muted mb-2">@lang('production::app.batchRmAutoAppliedNote')</p>
        @endif
        <p class="f-13 text-muted mb-3">
            @lang('production::app.batchRmConsumptionIntro')
            @if (($batchCountOnOrder ?? 1) > 1)
                <span class="d-block mt-1">@lang('production::app.batchRmMultiBatchSplitNote', [
                    'order_qty' => $formatQuantity($orderPlannedQuantity ?? (float) $batch->order->planned_quantity),
                    'batch_count' => (int) ($batchCountOnOrder ?? 1),
                ])</span>
            @endif
        </p>
        @if (!empty($canApplyBomSnapshotPlanned) && $canApplyBomSnapshotPlanned)
            <div class="bg-white rounded p-3 mb-3 f-14">
                <form method="post" action="{{ route('production.batches.apply-planned-from-bom-snapshot', $batch) }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary rounded f-14 p-2">
                        <i class="fa fa-magic mr-1"></i>@lang('production::app.applyPlannedFromBomSnapshot')
                    </button>
                </form>
                <p class="text-muted f-12 mb-0">@lang('production::app.applyPlannedFromBomSnapshotHelp')</p>
            </div>
        @endif
        <div class="d-flex flex-column w-tables rounded mb-4 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.rawMaterialProduct')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.rawMaterialBatchId')</th>
                        <th class="f-14 text-dark-grey">
                            @if ($batch->posted_consumptions_at)
                                @lang('production::app.batchRmPlannedConsumptionColumnPosted')
                            @else
                                @lang('production::app.batchRmPlannedConsumptionColumn')
                            @endif
                        </th>
                        @if ($showBatchConsumptionShadowColumn ?? (bool) config('production.phase2.yield_uom_shadow_enabled', false))
                            <th class="f-14 text-dark-grey">@lang('production::app.batchRmPlannedConsumptionShadowColumn')</th>
                        @endif
                        <th class="f-14 text-dark-grey">@lang('production::app.rawMaterialBatch')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batch->consumptions as $line)
                        <tr>
                            <td>{{ $line->componentProduct?->name ?? $line->component_product_id }}</td>
                            <td>{{ $line->warehouse_product_batch_id }} @if ($line->warehouseProductBatch)
                                    ({{ $line->warehouseProductBatch->batch_number ?? '—' }})
                                @endif
                            </td>
                            <td>
                                @php
                                    $consumptionQty = $line->actual_quantity ?? $line->planned_quantity;
                                    $consumptionUnit = $line->unit?->unit_type ?? $line->componentProduct?->unit?->unit_type;
                                @endphp
                                @if ($consumptionQty !== null && (float) $consumptionQty > 0)
                                    {{ $formatQuantity((float) $consumptionQty) }}
                                    @if ($consumptionUnit)
                                        <span class="text-muted">{{ $consumptionUnit }}</span>
                                    @endif
                                @else
                                    <span class="text-warning" title="@lang('production::app.batchRmMissingPlannedQtyHint')">—</span>
                                @endif
                            </td>
                            @if ($showBatchConsumptionShadowColumn ?? (bool) config('production.phase2.yield_uom_shadow_enabled', false))
                                <td>
                                    @if ($line->planned_quantity_shadow !== null)
                                        {{ $formatQuantity($line->planned_quantity_shadow) }}
                                        <div class="text-muted f-12 mt-1">@lang('production::app.shadowModeLabel')</div>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                            <td>
                                @if (in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true) && $batch->posted_consumptions_at === null && $line->warehouse_product_batch_id === null)
                                    @php
                                        $wbForComponent = $rmBatches->where('product_id', $line->component_product_id)->values();
                                    @endphp
                                    @if ($wbForComponent->isEmpty())
                                        <span class="text-muted f-12">@lang('production::app.noRmWarehouseBatchForComponent')</span>
                                    @else
                                        <form method="post" action="{{ route('production.batches.consumptions.assign-warehouse-batch', [$batch, $line]) }}" class="d-flex flex-wrap align-items-end">
                                            @csrf
                                            <div class="form-group mb-0 mr-2 flex-grow-1" style="min-width: 180px; max-width: 220px;">
                                                <label class="sr-only" for="assign_batch_{{ $line->id }}">@lang('production::app.rawMaterialBatch')</label>
                                                <select name="warehouse_product_batch_id" id="assign_batch_{{ $line->id }}" class="form-control form-control-sm f-14 select-picker" data-size="8" data-container="body" required>
                                                    @foreach ($wbForComponent as $wb)
                                                        <option value="{{ $wb->id }}">
                                                            #{{ $wb->id }} @if ($wb->batch_number)
                                                                ({{ $wb->batch_number }})
                                                            @endif — @lang('app.quantity'): {{ $formatQuantity($wb->quantity) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-0">
                                                <button type="submit" class="btn btn-sm btn-secondary rounded f-13 height-35">@lang('production::app.assignRmBatch')</button>
                                            </div>
                                        </form>
                                    @endif
                                @elseif ($line->warehouse_product_batch_id === null)
                                    <span class="text-muted f-12">@lang('production::app.rmBatchAssignPermission')</span>
                                @else
                                    <span class="text-muted f-12">@lang('production::app.rmBatchAlreadyAssigned', ['id' => $line->warehouse_product_batch_id])</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $canEditBatchConsumptions = in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true) && $batch->posted_consumptions_at === null && in_array($batch->order->status, [\Modules\Production\Entities\ProductionOrder::STATUS_RELEASED, \Modules\Production\Entities\ProductionOrder::STATUS_IN_PROGRESS], true);
        @endphp

        @if (\Modules\Production\Support\ProductionBomFirstPolicy::allowManualBatchConsumptionLines() && $canEditBatchConsumptions)
            <div class="bg-white rounded p-4 mb-4">
                <h6 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.addRawMaterialUsedLine')</h6>
                <form method="post" action="{{ route('production.batches.consumptions.store', $batch) }}" class="form-row align-items-end">
                    @csrf
                    <div class="form-group col-md-4">
                        <x-forms.label fieldId="component_product_id" :fieldLabel="__('production::app.rawMaterialProduct')" fieldRequired="true" />
                        <select name="component_product_id" id="component_product_id" class="form-control select-picker" data-size="8" data-container="body" required>
                            @foreach ($componentProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <x-forms.label fieldId="warehouse_product_batch_id" :fieldLabel="__('production::app.rawMaterialBatchId')" fieldRequired="true" />
                        <select name="warehouse_product_batch_id" id="warehouse_product_batch_id" class="form-control select-picker" data-size="8" data-container="body" required>
                            <option value="">@lang('app.select') @lang('production::app.rawMaterialBatchId')</option>
                            @foreach ($rmBatches as $wb)
                                <option value="{{ $wb->id }}" data-product-id="{{ $wb->product_id }}">
                                    #{{ $wb->id }} — {{ $componentProductNames[$wb->product_id] ?? $wb->product_id }} qty {{ $formatQuantity($wb->quantity) }} @if ($wb->batch_number)
                                        ({{ $wb->batch_number }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <x-forms.label fieldId="planned_quantity_line" :fieldLabel="__('production::app.plannedQuantityLine')" fieldRequired="true" />
                        <input type="number" step="0.0001" min="0.0001" name="planned_quantity" id="planned_quantity_line" class="form-control height-35 f-14" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="f-14 text-dark-grey mb-12 d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary rounded f-14 p-2 btn-block">
                            <i class="fa fa-check mr-1"></i>@lang('app.save')
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if ($canEditBatchConsumptions)
            <form method="post" action="{{ route('production.batches.post-consumptions', $batch) }}" class="mb-4" onsubmit="return confirm(@json(__('app.areYouSure')));">
                @csrf
                <button type="submit" class="btn btn-warning rounded f-14 p-2 text-white border-0" @disabled($batch->consumptions->isEmpty())>
                    <i class="fa fa-share mr-1"></i>@lang('production::app.postRawMaterialUsage')
                </button>
            </form>
        @endif

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.outputs')</h5>
        <div class="d-flex flex-column w-tables rounded mb-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgBatchNumber')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgQty')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgVarianceVsPlanned')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgVarianceReason')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgVarianceApproval')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.postedAt')</th>
                        <th class="f-14 text-dark-grey text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batch->outputs as $out)
                        <tr>
                            <td>{{ $out->batch_number }}</td>
                            <td>{{ $formatQuantity($out->quantity) }}</td>
                            <td>
                                @if ($out->variance_from_planned_total !== null)
                                    {{ $formatQuantity($out->variance_from_planned_total) }}
                                    @if ($out->variance_from_planned_percent !== null)
                                        ({{ $formatQuantity($out->variance_from_planned_percent) }}%)
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-break">{{ $out->variance_reason ? \Illuminate\Support\Str::limit($out->variance_reason, 120) : '—' }}</td>
                            <td>
                                @php
                                    $varianceApprovalUiState = $outputVarianceApprovalUiStates[$out->id] ?? 'not_required';
                                @endphp
                                @if ($varianceApprovalUiState === 'approved')
                                    <span class="badge badge-success">@lang('production::app.fgVarianceApproved')</span>
                                    <div class="text-muted f-12 mt-1">{{ $out->approved_at }}</div>
                                @elseif ($varianceApprovalUiState === 'pending')
                                    <span class="badge badge-warning">@lang('production::app.fgVariancePendingApproval')</span>
                                @else
                                    <span class="text-muted">@lang('production::app.fgVarianceNotRequired')</span>
                                @endif
                            </td>
                            <td>{{ $out->posted_at ?? '—' }}</td>
                            <td class="text-right">
                                @if ($out->posted_at === null && ($outputVarianceApprovalUiStates[$out->id] ?? '') === 'pending' && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true))
                                    <form method="post" action="{{ route('production.outputs.approve-variance', $out) }}" class="d-inline mr-1" onsubmit="return confirm(@json(__('app.areYouSure')));">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary rounded f-14 btn-sm">
                                            <i class="fa fa-check-circle mr-1"></i>@lang('production::app.approveVariance')
                                        </button>
                                    </form>
                                @endif
                                @if ($out->posted_at === null && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true) && $batch->posted_consumptions_at !== null)
                                    <form method="post" action="{{ route('production.outputs.post-fg-receipt', $out) }}" class="d-inline" onsubmit="return confirm(@json(__('app.areYouSure')));">
                                        @csrf
                                        <button type="submit" class="btn btn-success rounded f-14 btn-sm">
                                            <i class="fa fa-check mr-1"></i>@lang('production::app.postFgReceipt')
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if (in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true) && $batch->posted_consumptions_at !== null && $batch->posted_receipt_at === null)
            <div id="production-create-output-form" class="bg-white rounded p-4">
                <h6 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.createOutput')</h6>
                @include('production::partials.inline-form-validation-alert', [
                    'fields' => ['quantity', 'variance_reason', 'batch_number', 'warehouse_id', 'expiration_date', 'manufacturing_date', 'batch', 'order'],
                ])
                <form method="post" action="{{ route('production.batches.outputs.store', $batch) }}" class="form-row">
                    @csrf
                    <div class="form-group col-md-3">
                        <x-forms.label fieldId="batch_number" :fieldLabel="__('production::app.fgBatchNumber')" fieldRequired="true" />
                        <input type="text" name="batch_number" id="batch_number" class="form-control height-35 f-14" value="{{ old('batch_number') }}" required maxlength="191">
                    </div>
                    <div class="form-group col-md-2">
                        <x-forms.label fieldId="output_quantity" :fieldLabel="__('production::app.fgQty')" fieldRequired="true" />
                        <input type="number" step="0.0001" min="0.0001" name="quantity" id="output_quantity" class="form-control height-35 f-14 @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                        @error('quantity')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @if ($batch->order)
                            <small class="text-muted f-12 d-block mt-1">@lang('production::app.fgOutputOrderPlannedHint', ['planned' => $batch->order->planned_quantity])</small>
                        @endif
                    </div>
                    <div class="form-group col-md-3">
                        <x-forms.label fieldId="warehouse_id" :fieldLabel="__('warehouse::app.warehouse')" fieldRequired="true" />
                        <select name="warehouse_id" id="warehouse_id" class="form-control select-picker" data-size="8" data-container="body" required>
                            @foreach (\Modules\Warehouse\Entities\Warehouse::query()->where('company_id', company()->id)->where('status', 'active')->orderBy('name')->get() as $w)
                                <option value="{{ $w->id }}" @selected($w->id == $batch->order->fg_warehouse_id)>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <x-forms.label fieldId="expiration_date" :fieldLabel="__('production::app.expiry')" fieldRequired="false" />
                        <input type="text" name="expiration_date" id="expiration_date" class="form-control date-picker height-35 f-14" placeholder="@lang('placeholders.date')">
                    </div>
                    <div class="form-group col-md-2">
                        <x-forms.label fieldId="manufacturing_date" :fieldLabel="__('production::app.mfgDate')" fieldRequired="false" />
                        <input type="text" name="manufacturing_date" id="manufacturing_date" class="form-control date-picker height-35 f-14" placeholder="@lang('placeholders.date')">
                    </div>
                    <div class="form-group col-12">
                        <x-forms.label fieldId="variance_reason" :fieldLabel="__('production::app.fgVarianceReason')" fieldRequired="false" />
                        <textarea name="variance_reason" id="variance_reason" class="form-control f-14 pt-2 @error('variance_reason') is-invalid @enderror" rows="4" maxlength="5000" placeholder="@lang('production::app.fgVarianceReasonHelp')">{{ old('variance_reason') }}</textarea>
                        @error('variance_reason')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-12">
                        <button type="submit" class="btn btn-primary rounded f-14 p-2">
                            <i class="fa fa-check mr-1"></i>@lang('app.save')
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <h5 class="f-14 text-dark-grey font-weight-bold mt-4 mb-3">@lang('production::app.reworkOrders')</h5>
        <div class="d-flex flex-column w-tables rounded mb-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">#</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.status')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.requestedQty')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.approvedQty')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.reason')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.postedAt')</th>
                        <th class="f-14 text-dark-grey text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batch->reworkOrders as $rework)
                        <tr>
                            <td>#{{ $rework->id }}</td>
                            <td>{{ ucfirst($rework->status) }}</td>
                            <td>{{ $formatQuantity($rework->requested_quantity) }}</td>
                            <td>{{ $rework->approved_quantity !== null ? $formatQuantity($rework->approved_quantity) : '—' }}</td>
                            <td class="text-break">{{ $rework->reason ? \Illuminate\Support\Str::limit($rework->reason, 120) : '—' }}</td>
                            <td>{{ $rework->completed_at ?? ($rework->approved_at ?? '—') }}</td>
                            <td class="text-right">
                                @if (in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true))
                                    @if ($rework->status === \Modules\Production\Entities\ProductionReworkOrder::STATUS_REQUESTED)
                                        <form method="post" action="{{ route('production.batches.rework-orders.approve', [$batch, $rework]) }}" class="d-inline mr-1" onsubmit="return confirm(@json(__('app.areYouSure')));">
                                            @csrf
                                            <input type="hidden" name="approved_quantity" value="{{ $rework->requested_quantity }}">
                                            <button type="submit" class="btn btn-outline-success rounded f-13 btn-sm">
                                                @lang('production::app.approveRework')
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('production.batches.rework-orders.reject', [$batch, $rework]) }}" class="d-inline" onsubmit="return confirm(@json(__('app.areYouSure')));">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger rounded f-13 btn-sm">
                                                @lang('production::app.rejectRework')
                                            </button>
                                        </form>
                                    @elseif ($rework->status === \Modules\Production\Entities\ProductionReworkOrder::STATUS_APPROVED)
                                        <form method="post" action="{{ route('production.batches.rework-orders.complete', [$batch, $rework]) }}" class="d-inline" onsubmit="return confirm(@json(__('app.areYouSure')));">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary rounded f-13 btn-sm">
                                                @lang('production::app.completeRework')
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">@lang('messages.noRecordFound')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true))
            <div class="bg-white rounded p-4 mb-4">
                <h6 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.requestRework')</h6>
                <form method="post" action="{{ route('production.batches.rework-orders.store', $batch) }}" class="form-row align-items-end">
                    @csrf
                    <div class="form-group col-md-3">
                        <x-forms.label fieldId="requested_quantity" :fieldLabel="__('production::app.requestedQty')" fieldRequired="true" />
                        <input type="number" step="0.0001" min="0.0001" name="requested_quantity" id="requested_quantity" class="form-control height-35 f-14" required>
                    </div>
                    <div class="form-group col-md-7">
                        <x-forms.label fieldId="rework_reason" :fieldLabel="__('production::app.reason')" fieldRequired="false" />
                        <input type="text" name="reason" id="rework_reason" class="form-control height-35 f-14" maxlength="5000">
                    </div>
                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-primary rounded f-14 p-2 btn-block">
                            <i class="fa fa-plus mr-1"></i>@lang('production::app.requestRework')
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const $component = $('#component_product_id');
            const $warehouseBatch = $('#warehouse_product_batch_id');

            if ($component.length && $warehouseBatch.length) {
                const filterWarehouseBatchByComponent = () => {
                    const selectedProductId = String($component.val() || '');
                    let hasEnabledOption = false;

                    $warehouseBatch.find('option').each(function() {
                        const $option = $(this);
                        const optionValue = String($option.val() || '');
                        const optionProductId = String($option.data('product-id') || '');

                        if (optionValue === '') {
                            $option.prop('disabled', false);
                            return;
                        }

                        const shouldEnable = selectedProductId !== '' && optionProductId === selectedProductId;
                        $option.prop('disabled', !shouldEnable);
                        hasEnabledOption = hasEnabledOption || shouldEnable;
                    });

                    const currentOptionDisabled = $warehouseBatch.find(`option[value="${$warehouseBatch.val()}"]`).prop('disabled');
                    if (!hasEnabledOption || currentOptionDisabled) {
                        $warehouseBatch.val('');
                    }

                    if (typeof $.fn.selectpicker === 'function') {
                        $warehouseBatch.selectpicker('refresh');
                    }
                };

                $component.on('changed.bs.select change', filterWarehouseBatchByComponent);
                filterWarehouseBatchByComponent();
            }

            if ($('#expiration_date').length) {
                datepicker('#expiration_date', {
                    position: 'bl',
                    ...datepickerConfig
                });
            }

            if ($('#manufacturing_date').length) {
                datepicker('#manufacturing_date', {
                    position: 'bl',
                    ...datepickerConfig
                });
            }
        })();
    </script>
@endpush
