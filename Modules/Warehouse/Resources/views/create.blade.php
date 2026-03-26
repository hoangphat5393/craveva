@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="bg-white rounded">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang('warehouse::app.createTitle')
                    </h4>
                    <div class="p-20">
                        <form action="{{ route('warehouse.store') }}" id="createWarehouse" method="POST">
                            @include('sections.password-autocomplete-hide')
                            @csrf
                            <div class="row">
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.text fieldId="name" :fieldLabel="__('warehouse::app.name')" fieldName="name" fieldRequired="true" :fieldValue="old('name')" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.text fieldId="code" :fieldLabel="__('warehouse::app.code')" fieldName="code" :fieldValue="old('code')" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.textarea fieldId="address" :fieldLabel="__('warehouse::app.address')" fieldName="address" :fieldValue="old('address')" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.textarea fieldId="description" :fieldLabel="__('warehouse::app.description')" fieldName="description" :fieldValue="old('description')" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                                        <option value="active" @selected(old('status', 'active') === 'active')>@lang('app.active')</option>
                                        <option value="inactive" @selected(old('status') === 'inactive')>@lang('app.inactive')</option>
                                    </x-forms.select>
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <x-forms.checkbox :fieldLabel="__('warehouse::app.isDefault')" fieldName="is_default" fieldId="is_default" fieldValue="1" :checked="(bool) old('is_default')" />
                                </div>
                            </div>
                            <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                                <button type="submit" id="save-form" class="btn btn-primary rounded f-14 p-2">
                                    <i class="fa fa-check mr-1"></i> @lang('app.save')
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

@push('scripts')
    <script>
        $(function() {
            if (typeof $.fn.selectpicker === 'function') {
                $('.select-picker').selectpicker('refresh');
            }
        });
    </script>
@endpush
