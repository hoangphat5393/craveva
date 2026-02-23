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
                <li><a href="{{ route('warehouse.stock.index') }}">{{ __($pageTitle) }}</a></li>
                <li class="active">@lang('warehouse::app.transferStock')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('warehouse::app.transferStock')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <form action="{{ route('warehouse.transfer.store') }}" id="createTransfer" class="form-horizontal" method="POST">
                            @csrf
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.fromWarehouse')</label>
                                            <div class="col-md-9">
                                                <select class="form-control select2" name="warehouse_from_id" id="warehouse_from_id">
                                                    @foreach ($warehouses as $warehouse)
                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.toWarehouse')</label>
                                            <div class="col-md-9">
                                                <select class="form-control select2" name="warehouse_to_id" id="warehouse_to_id">
                                                    @foreach ($warehouses as $warehouse)
                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.product')</label>
                                            <div class="col-md-9">
                                                <select class="form-control select2" name="product_id" id="product_id">
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">@lang('warehouse::app.quantity')</label>
                                            <div class="col-md-9">
                                                <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label col-md-1">@lang('warehouse::app.description')</label>
                                            <div class="col-md-11">
                                                <textarea name="description" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions">
                                <button type="submit" id="save-form" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
                                <a href="{{ route('warehouse.stock.index') }}" class="btn btn-default">@lang('app.back')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script>
        // Initialize Select2
        $(".select2").select2({
            formatNoMatches: function() {
                return "{{ __('messages.noRecordFound') }}";
            }
        });
    </script>
@endpush
