@extends('layouts.app')

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

        @if (session('success'))
            <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3 mb-0">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded p-4 mt-3 mb-4">
            <div class="row f-14">
                <div class="col-md-4 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.status') (@lang('app.order'))</span>
                    <span class="font-weight-normal">{{ ucfirst(str_replace('_', ' ', $batch->order->status)) }}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.postedAt') (RM)</span>
                    <span class="font-weight-normal">{{ $batch->posted_consumptions_at ?? '—' }}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.postedAt') (FG)</span>
                    <span class="font-weight-normal">{{ $batch->posted_receipt_at ?? '—' }}</span>
                </div>
            </div>
        </div>

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.consumptions')</h5>
        <div class="d-flex flex-column w-tables rounded mb-4 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.componentProduct')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.warehouseBatchId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.plannedConsumption')</th>
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
                            <td>{{ $line->actual_quantity ?? $line->planned_quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if (in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true) && $batch->posted_consumptions_at === null && in_array($batch->order->status, [\Modules\Production\Entities\ProductionOrder::STATUS_RELEASED, \Modules\Production\Entities\ProductionOrder::STATUS_IN_PROGRESS], true))
            <div class="bg-white rounded p-4 mb-4">
                <h6 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.addConsumptionLine')</h6>
                <form method="post" action="{{ route('production.batches.consumptions.store', $batch) }}" class="form-row align-items-end">
                    @csrf
                    <div class="form-group col-md-4">
                        <x-forms.label fieldId="component_product_id" :fieldLabel="__('production::app.componentProduct')" fieldRequired="true" />
                        <select name="component_product_id" id="component_product_id" class="form-control select-picker" data-size="8" data-container="body" required>
                            @foreach ($componentProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <x-forms.label fieldId="warehouse_product_batch_id" :fieldLabel="__('production::app.warehouseBatchId')" fieldRequired="true" />
                        <select name="warehouse_product_batch_id" id="warehouse_product_batch_id" class="form-control select-picker" data-size="8" data-container="body" required>
                            @foreach ($rmBatches as $wb)
                                <option value="{{ $wb->id }}">#{{ $wb->id }} — {{ $wb->product_id }} qty {{ $wb->quantity }} @if ($wb->batch_number)
                                        ({{ $wb->batch_number }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <x-forms.label fieldId="planned_quantity_line" :fieldLabel="__('production::app.plannedConsumption')" fieldRequired="true" />
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

            <form method="post" action="{{ route('production.batches.post-consumptions', $batch) }}" class="mb-4" onsubmit="return confirm(@json(__('app.areYouSure')));">
                @csrf
                <button type="submit" class="btn btn-warning rounded f-14 p-2 text-white border-0" @disabled($batch->consumptions->isEmpty())>
                    <i class="fa fa-share mr-1"></i>@lang('production::app.postConsumption')
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
                        <th class="f-14 text-dark-grey">@lang('production::app.postedAt')</th>
                        <th class="f-14 text-dark-grey text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batch->outputs as $out)
                        <tr>
                            <td>{{ $out->batch_number }}</td>
                            <td>{{ $out->quantity }}</td>
                            <td>{{ $out->posted_at ?? '—' }}</td>
                            <td class="text-right">
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
            <div class="bg-white rounded p-4">
                <h6 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.createOutput')</h6>
                <form method="post" action="{{ route('production.batches.outputs.store', $batch) }}" class="form-row">
                    @csrf
                    <div class="form-group col-md-3">
                        <x-forms.label fieldId="batch_number" :fieldLabel="__('production::app.fgBatchNumber')" fieldRequired="true" />
                        <input type="text" name="batch_number" id="batch_number" class="form-control height-35 f-14" required maxlength="191">
                    </div>
                    <div class="form-group col-md-2">
                        <x-forms.label fieldId="output_quantity" :fieldLabel="__('production::app.fgQty')" fieldRequired="true" />
                        <input type="number" step="0.0001" min="0.0001" name="quantity" id="output_quantity" class="form-control height-35 f-14" required>
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
                        <input type="date" name="expiration_date" id="expiration_date" class="form-control height-35 f-14">
                    </div>
                    <div class="form-group col-md-2">
                        <x-forms.label fieldId="manufacturing_date" :fieldLabel="__('production::app.mfgDate')" fieldRequired="false" />
                        <input type="date" name="manufacturing_date" id="manufacturing_date" class="form-control height-35 f-14">
                    </div>
                    <div class="form-group col-12">
                        <button type="submit" class="btn btn-primary rounded f-14 p-2">
                            <i class="fa fa-check mr-1"></i>@lang('app.save')
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection
