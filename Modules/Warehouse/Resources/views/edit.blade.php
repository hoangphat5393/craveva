@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="bg-white rounded">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang('warehouse::app.editTitle')
                    </h4>
                    <div class="p-20">
                        <form action="{{ route('warehouse.update', $warehouse->id) }}" id="updateWarehouse" method="POST">
                            @include('sections.password-autocomplete-hide')
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.text fieldId="name" :fieldLabel="__('warehouse::app.name')" fieldName="name" fieldRequired="true" :fieldValue="old('name', $warehouse->name)" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.text fieldId="code" :fieldLabel="__('warehouse::app.code')" fieldName="code" :fieldValue="old('code', $warehouse->code)" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.select fieldName="warehouse_type" fieldId="warehouse_type" :fieldLabel="__('warehouse::app.warehouseType')">
                                        <option value="normal" @selected(old('warehouse_type', $warehouse->warehouse_type ?? 'normal') === 'normal')>@lang('warehouse::app.warehouseTypeNormal')</option>
                                        <option value="locked" @selected(old('warehouse_type', $warehouse->warehouse_type) === 'locked')>@lang('warehouse::app.warehouseTypeLocked')</option>
                                        <option value="scrap" @selected(old('warehouse_type', $warehouse->warehouse_type) === 'scrap')>@lang('warehouse::app.warehouseTypeScrap')</option>
                                        <option value="transit" @selected(old('warehouse_type', $warehouse->warehouse_type) === 'transit')>@lang('warehouse::app.warehouseTypeTransit')</option>
                                    </x-forms.select>
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.textarea fieldId="address" :fieldLabel="__('warehouse::app.address')" fieldName="address" :fieldValue="old('address', $warehouse->address)" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.textarea fieldId="description" :fieldLabel="__('warehouse::app.description')" fieldName="description" :fieldValue="old('description', $warehouse->description)" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.select fieldName="status" fieldId="status" :fieldLabel="__('app.status')">
                                        <option value="active" @selected(old('status', $warehouse->status) === 'active')>@lang('app.active')</option>
                                        <option value="inactive" @selected(old('status', $warehouse->status) === 'inactive')>@lang('app.inactive')</option>
                                    </x-forms.select>
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.checkbox :fieldLabel="__('warehouse::app.isDefault')" fieldName="is_default" fieldId="is_default" fieldValue="1" :checked="(bool) old('is_default', $warehouse->is_default)" />
                                </div>
                            </div>
                            <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                                <button type="submit" id="save-form" class="btn btn-primary rounded f-14 p-2">
                                    <i class="fa fa-check mr-1"></i> @lang('app.update')
                                </button>
                                <x-forms.link-secondary :link="route('warehouse.index')" class="ml-2">@lang('app.cancel')</x-forms.link-secondary>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
