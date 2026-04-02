@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="bg-white rounded">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang('warehouse::app.adjustStock')
                    </h4>
                    <div class="p-20">
                        <x-form id="createStock" method="POST" action="{{ route('warehouse.stock.store') }}">
                            <div id="alert"></div>
                            <div class="row">
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.select fieldId="warehouse_id" :fieldLabel="__('warehouse::app.warehouse')" fieldName="warehouse_id" fieldRequired="true" search="true">
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">
                                                {{ $warehouse->name }}{{ $warehouse->code ? ' (' . $warehouse->code . ')' : '' }}{{ $warehouse->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                                            </option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                <div class="col-lg-6 col-md-6">
                                    <x-forms.select fieldId="product_id" :fieldLabel="__('warehouse::app.product')" fieldName="product_id" fieldRequired="true" search="true">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                <div class="col-lg-6 col-md-6">
                                    <x-forms.label class="mt-3" fieldId="stock_action" :fieldLabel="__('warehouse::app.action')" fieldRequired="true"></x-forms.label>
                                    <div class="form-group mb-0 d-flex align-items-center">
                                        <x-forms.radio class="mr-4" fieldId="action_add" :fieldLabel="__('warehouse::app.addStock')" fieldValue="add" fieldName="action" :checked="true"></x-forms.radio>
                                        <x-forms.radio class="" fieldId="action_remove" :fieldLabel="__('warehouse::app.removeStock')" fieldValue="remove" fieldName="action"></x-forms.radio>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6">
                                    <x-forms.number fieldId="quantity" :fieldLabel="__('warehouse::app.quantity')" fieldName="quantity" fieldRequired="true" minValue="0.01" step="0.01" :fieldValue="old('quantity')" />
                                </div>

                                <div class="col-lg-12">
                                    <x-forms.textarea fieldId="reason" :fieldLabel="__('warehouse::app.reason')" fieldName="reason" :fieldValue="old('reason')" />
                                </div>
                            </div>

                            <input type="hidden" name="type" value="adjustment">

                            <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                                <button type="submit" id="save-form" class="btn btn-primary rounded f-14 p-2 mr-3"><i class="fa fa-check mr-1"></i>@lang('app.save')</button>
                                <x-forms.link-secondary :link="route('warehouse.stock.index')">@lang('app.cancel')</x-forms.link-secondary>
                            </div>
                        </x-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
