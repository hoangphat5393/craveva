@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ __($pageTitle) }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('warehouse.index') }}">{{ __($pageTitle) }}</a></li>
                <li class="active">@lang('app.createNew')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('warehouse::app.createTitle')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <form action="{{ route('warehouse.store') }}" id="createWarehouse" class="form-horizontal" method="POST">
                            @csrf
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.name')</label>
                                            <div class="col-md-9">
                                                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.code')</label>
                                            <div class="col-md-9">
                                                <input type="text" name="code" class="form-control" value="{{ old('code') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.address')</label>
                                            <div class="col-md-9">
                                                <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.description')</label>
                                            <div class="col-md-9">
                                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('app.status')</label>
                                            <div class="col-md-9">
                                                <select class="form-control" name="status">
                                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>@lang('app.active')</option>
                                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>@lang('app.inactive')</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="checkbox checkbox-info col-md-offset-3 col-md-9">
                                                <input id="is_default" name="is_default" type="checkbox" {{ old('is_default') ? 'checked' : '' }}>
                                                <label for="is_default"> @lang('warehouse::app.isDefault') </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions">
                                <button type="submit" id="save-form" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
                                <a href="{{ route('warehouse.index') }}" class="btn btn-default">@lang('app.back')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
