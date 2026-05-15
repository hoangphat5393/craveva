@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap border-bottom-grey pb-2 mb-3">
            <div class="align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.orders.show', $order)" class="float-left" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <form method="post" action="{{ route('production.orders.update', $order) }}" class="bg-white rounded p-4">
                    @csrf
                    @method('put')
                    <x-forms.select fieldId="output_product_id" :fieldLabel="__('production::app.fgProduct')" fieldName="output_product_id" fieldRequired="true">
                        @foreach ($finishedGoods as $p)
                            <option value="{{ $p->id }}" @selected(old('output_product_id', $order->output_product_id) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </x-forms.select>

                    <x-forms.select fieldId="production_bom_id" :fieldLabel="__('production::app.bom') . ' (' . __('app.optional') . ')'" fieldName="production_bom_id" :fieldRequired="false">
                        <option value="">—</option>
                        @foreach ($boms as $bom)
                            <option value="{{ $bom->id }}" data-output-product-id="{{ $bom->output_product_id }}" @selected(old('production_bom_id', $order->production_bom_id) == $bom->id)>
                                {{ $bom->labelForSelect() }}
                            </option>
                        @endforeach
                    </x-forms.select>
                    <p class="f-12 text-muted mt-12 mb-0">
                        @lang('production::app.bomManageFromSettingsHint')
                        <a href="{{ route('production.boms.index') }}">@lang('production::app.menuBillOfMaterials')</a>
                    </p>

                    <x-forms.select fieldId="rm_warehouse_id" :fieldLabel="__('production::app.rawMaterialWarehouse')" fieldName="rm_warehouse_id" fieldRequired="true">
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('rm_warehouse_id', $order->rm_warehouse_id) == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </x-forms.select>

                    <x-forms.select fieldId="fg_warehouse_id" :fieldLabel="__('production::app.finishedGoodsWarehouse')" fieldName="fg_warehouse_id" fieldRequired="true">
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('fg_warehouse_id', $order->fg_warehouse_id) == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </x-forms.select>

                    <div class="form-group my-3">
                        <x-forms.label fieldId="planned_quantity" :fieldLabel="__('production::app.plannedQty')" fieldRequired="true" />
                        <input type="number" step="0.0001" min="0.0001" name="planned_quantity" id="planned_quantity" class="form-control height-35 f-14" value="{{ old('planned_quantity', $order->planned_quantity) }}" required>
                    </div>

                    <x-forms.select fieldId="sales_order_id" :search="true" :fieldLabel="__('production::app.linkedSalesOrder')" fieldName="sales_order_id" :fieldRequired="false">
                        <option value="">—</option>
                        @foreach ($recentSalesOrders as $so)
                            <option value="{{ $so->id }}" @selected(old('sales_order_id', $order->sales_order_id) == $so->id)>
                                #{{ $so->id }} — {{ $so->order_number }} — {{ __('modules.invoices.' . $so->status) }}
                            </option>
                        @endforeach
                    </x-forms.select>
                    <p class="f-12 text-muted mt-12 mb-0">@lang('production::app.linkedSalesOrderHint')</p>

                    @if (config('production.ui.show_linked_project_on_order_form'))
                        <x-forms.select fieldId="project_id" :fieldLabel="__('production::app.linkedProject')" fieldName="project_id" :fieldRequired="false">
                            <option value="">—</option>
                            @foreach ($projects as $proj)
                                <option value="{{ $proj->id }}" @selected(old('project_id', $order->project_id) == $proj->id)>
                                    #{{ $proj->id }} — {{ $proj->project_name }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    @endif

                    <div class="w-100 border-top-grey pt-3 mt-2 d-flex flex-wrap">
                        <button type="submit" class="btn btn-primary rounded f-14 p-2 mr-3">
                            <i class="fa fa-check mr-1"></i>@lang('app.save')
                        </button>
                        <x-forms.button-cancel :link="route('production.orders.show', $order)" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('production::orders.partials.bom-fg-sync-script')
@endpush
