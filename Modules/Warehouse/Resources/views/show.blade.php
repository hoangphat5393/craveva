@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="bg-white rounded">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        {{ $warehouse->name }}
                    </h4>
                    <div class="p-20">
                        <div class="row">
                            <div class="col-md-6">
                                <x-cards.data-row :label="__('warehouse::app.code')" :value="$warehouse->code ?: '—'" />
                            </div>
                            <div class="col-md-6">
                                <x-cards.data-row :label="__('app.status')" :value="ucfirst($warehouse->status)" />
                            </div>
                            <div class="col-md-12">
                                <x-cards.data-row :label="__('warehouse::app.address')" :value="$warehouse->address ?: '—'" />
                            </div>
                            <div class="col-md-12">
                                <x-cards.data-row :label="__('warehouse::app.description')" :value="$warehouse->description ?: '—'" />
                            </div>
                        </div>
                        <div class="w-100 border-top-grey d-flex flex-wrap justify-content-start px-0 py-3">
                            @if (user()->permission('view_warehouse_stock') != 'none' && user()->permission('view_warehouse_stock') != '')
                                <x-forms.link-secondary :link="route('warehouse.stock.index', ['warehouse_id' => $warehouse->id])" class="mr-2 mb-2" icon="boxes">@lang('warehouse::app.viewStockForWarehouse')</x-forms.link-secondary>
                                <x-forms.link-secondary :link="route('warehouse.movements.index', ['warehouse_id' => $warehouse->id])" class="mr-2 mb-2" icon="exchange-alt">@lang('warehouse::app.viewMovementsForWarehouse')</x-forms.link-secondary>
                            @endif
                            <x-forms.link-primary :link="route('warehouse.edit', $warehouse->id)" class="mr-2 mb-2" icon="pencil">@lang('app.edit')</x-forms.link-primary>
                            <x-forms.link-secondary :link="route('warehouse.index')" class="mb-2">@lang('app.back')</x-forms.link-secondary>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
